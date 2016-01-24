<?php

namespace Wassa\MPSBundle;

use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\EntityManager;

use Wassa\MPS\MultiPushServer;
use Wassa\MPS\PushData;
use Wassa\MPSBundle\Entity\AndroidDevice;
use Wassa\MPSBundle\Entity\Device;
use Wassa\MPSBundle\Entity\IOSDevice;

/**
 * Class MPS
 * @package Wassa\MPSBundle
 */
class MPS
{
    /**
     * @var MultiPushServer
     */
    protected $_mpsServer;

    /**
     * @var RegistryInterface
     */
    protected $_registry;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var array
     */
    protected $_cache = [];

    /**
     * @param string $api_key
     * @param boolean $dry_run
     * @param string $environment
     * @param string $prod_cert
     * @param string $sand_cert
     * @param string $ca_cert
     * @param RegistryInterface $registry
     * @param LoggerInterface $logger
     */
    public function __construct($api_key,
                                $dry_run,
                                $environment,
                                $prod_cert,
                                $sand_cert,
                                $ca_cert,
                                RegistryInterface $registry,
                                LoggerInterface $logger)
    {
        $this->_mpsServer = new MultiPushServer(
            $api_key,
            $dry_run,
            $environment,
            $prod_cert,
            $sand_cert,
            $ca_cert,
            $logger
        );
        $this->_registry = $registry;
        $this->_logger = $logger;
    }

    /**
     * @param \Class $class
     *
     * @return EntityManager
     */
    public function getEntityManager($class = null)
    {
        if (!$class) {
            return $this->_registry->getEntityManager();
        }

        if (is_object($class)) {
            $class = get_class($class);
        }

        if (!isset($this->_cache[$class])) {
            $em = $this->_registry->getManagerForClass($class);

            if (!$em) {
                throw new \RuntimeException(sprintf('No entity manager defined for class %s', $class));
            }

            $this->_cache[$class] = $em;
        }

        return $this->_cache[$class];
    }

    /**
     * @param PushData $pushData
     * @param Device $device
     * @return array|bool
     */
    public function sendToDevice(PushData $pushData, Device $device)
    {
        if ($device instanceof AndroidDevice) {
            $this->_mpsServer->setMode(MultiPushServer::SEND_GCM);
            return $this->_mpsServer->send($pushData, [$device->getRegistrationToken()]);
        }
        elseif ($device instanceof IOSDevice) {
            $this->_mpsServer->setMode(MultiPushServer::SEND_APNS);
            $result = $this->_mpsServer->send($pushData, [$device->getRegistrationToken()]);

            if ($result['all_ok']) {
                $device->setBadge($device->getBadge() + $pushData->getApnsBadge());
                return $result;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    /**
     * @param PushData $pushData
     * @param mixed $devices
     * @return array
     */
    public function sendToMultipleDevices(PushData $pushData, $devices)
    {
        $this->_logger->info(sprintf('Send push to %d devices', count($devices)));
        $sortedDevices = $this->sortDevicesByPlatform($devices);
        $result = [];

        if (array_key_exists('gcm', $sortedDevices)) {
            $result['gcm'] = $this->sendMultipleGCM($pushData, $sortedDevices['gcm']);
        }

        if (array_key_exists('apns', $sortedDevices)) {
            $result['apns'] = $this->sendMultipleAPNS($pushData, $sortedDevices['apns']);
        }

        return $result;
    }

    /**
     * @param PushData $pushData
     * @param mixed $devices
     * @return array|bool
     */
    protected function sendMultipleGCM(PushData $pushData, $devices)
    {
        $registrationIds = $this->registrationTokensOfDevices($devices);
        $this->_mpsServer->setMode(MultiPushServer::SEND_GCM);

        return $this->_mpsServer->send($pushData, $registrationIds);
    }

    /**
     * @param PushData $pushData
     * @param mixed $devices
     * @return array|bool
     */
    protected function sendMultipleAPNS(PushData $pushData, $devices)
    {
        $deviceTokens = $this->registrationTokensOfDevices($devices);
        $this->_mpsServer->setMode(MultiPushServer::SEND_APNS);
        $result = $this->_mpsServer->send($pushData, $deviceTokens);

        // Increment badge count for successful devices
        if ($result['success']) {
            $em = $this->getEntityManager();
            $successDevices = $em->getRepository('WassaMPSBundle:IOSDevice')->findBy(['registrationToken' => $result['success']]);

            foreach ($successDevices as $successDevice) {
                $successDevice->setBadge($successDevice->getBadge() + $pushData->getApnsBadge());
                $em->persist($successDevice);
            }

            $em->flush();
        }

        return $result;
    }

    /**
     * @param mixed $devices
     * @return array
     */
    protected function sortDevicesByPlatform($devices)
    {
        $platforms = [
            'gmc' => [],
            'apns' => []
        ];

        foreach ($devices as $device) {
            if ($device instanceof AndroidDevice) {
                $platforms['gcm'][] = $device;
            } elseif ($device instanceof IOSDevice) {
                $platforms['apns'][] = $device;
            }
        }

        return $platforms;
    }

    /**
     * @param mixed $devices
     * @return array
     */
    protected function  registrationTokensOfDevices($devices) {
        $registrationTokens = [];

        foreach ($devices as $device) {
            $registrationTokens[] = $device->getRegistrationToken();
        }

        return $registrationTokens;
    }
} 