<?php

namespace Wassa\MPSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Device
 *
 * @ORM\Entity(repositoryClass="Wassa\MPSBundle\Repository\DeviceRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="device_type", type="integer")
 * @ORM\DiscriminatorMap({1 = "IOSDevice", 2 = "AndroidDevice"})
 */
abstract class Device
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="registration_token", type="string", nullable=true)
     */
    private $registrationToken;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_registration", type="datetime", nullable=true)
     */
    protected $lastRegistration;

    /**
     * @var array
     *
     * @ORM\Column(name="custom_data", type="json_array", nullable=true)
     */
    private $customData;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get device registration token
     *
     * @return string
     */
    public function getRegistrationToken()
    {
        return $this->registrationToken;
    }

    /**
     * Set device registration token
     *
     * @param $registrationToken
     * @return Device
     */
    public function setRegistrationToken($registrationToken)
    {
        $this->registrationToken = $registrationToken;

        return $this;
    }

    /**
     * Set lastRegistration
     *
     * @param \DateTime $lastRegistration
     * @return Device
     */
    public function setLastRegistration($lastRegistration)
    {
        $this->lastRegistration = $lastRegistration;

        return $this;
    }

    /**
     * Get lastRegistration
     *
     * @return \DateTime 
     */
    public function getLastRegistration()
    {
        return $this->lastRegistration;
    }

    /**
     * Set customData
     *
     * @param array $customData
     * @return Device
     */
    public function setCustomData($customData)
    {
        $this->customData = $customData;

        return $this;
    }

    /**
     * Get customData
     *
     * @return array
     */
    public function getCustomData()
    {
        return $this->customData;
    }
}
