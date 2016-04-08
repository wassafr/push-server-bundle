<?php
/*
 * ApiController.php
 *
 * Copyright (C) WASSA SAS - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * 02/02/2016
 */

namespace Wassa\MPSBundle\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Wassa\MPSBundle\Events\Events;
use Wassa\MPSBundle\Events\RegistrationEvent;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class ApiController extends Controller
{
    /**
     * @Route("/register")
     * @Method("POST")
     * @ApiDoc(
     *  resource="/api/push/",
     *  description="Register a new or existing device",
     *  parameters={
     *      {"name"="registrationToken", "dataType"="string", "required"=true, "description"="iOS device token or Android registration ID"},
     *      {"name"="platform", "dataType"="string", "required"=true, "description"="'ios' or 'android'"},
     *      {"name"="customData", "dataType"="json", "required"=false, "description"="Any custom data in JSON format"}
     *  }
     * )
     */
    public function register(Request $request)
    {
        $dispatcher = $this->get('event_dispatcher');
        $event = new RegistrationEvent();
        $dispatcher->dispatch(Events::REGISTRATION_PRECHECK, $event);

        $data = json_decode($request->getContent());

        if (!$data) {
            return new JsonResponse([
                'result' => 'KO',
                'reason' => 'INVALID_POST_DATA'
            ]);
        }

        if (!isset($data->registrationToken) || !$data->registrationToken) {
            return new JsonResponse([
                'result' => 'KO',
                'reason' => 'INVALID_REGISTRATION_TOKEN'
            ]);
        }

        if (!isset($data->platform) || !$data->platform) {
            return new JsonResponse([
                'result' => 'KO',
                'reason' => 'INVALID_PLATFORM'
            ]);
        }

        if ($data->platform == 'ios') {
            $class = '\Wassa\MPSBundle\Entity\IOSDevice';
        } elseif ($data->platform == 'android') {
            $class = '\Wassa\MPSBundle\Entity\AndroidDevice';
        } else {
            return new JsonResponse([
                'result' => 'KO',
                'reason' => 'UNSUPPORTED_PLATFORM'
            ]);
        }

        $em = $this->getDoctrine()->getManager();
        $dql = "SELECT d FROM $class d WHERE d.registrationToken = :registrationToken";
        $query = $em->createQuery($dql);
        $query->setParameter('registrationToken', $data->registrationToken);
        $query->setMaxResults(1);
        $device = $query->getOneOrNullResult();

        if (!$device) {
            $device = new $class;
            $device->setRegistrationToken($data->registrationToken);
        }

        $customData = isset($data->customData) ? (is_string($data->customData) ? json_decode($data->customData) : $data->customData) : null;
        $device->setLastRegistration(new \DateTime());
        $device->setCustomData($customData);

        $em->persist($device);
        $em->flush();

        $event = new RegistrationEvent($data->registrationToken, $data->platform, $customData);
        $dispatcher->dispatch(Events::REGISTRATION_POSTCHECK, $event);

        if ($event->getResult() && $event->getReason()) {
            return new JsonResponse([
                'result' => $event->getResult(),
                'reason' => $event->getReason()
            ]);
        }

        return new JsonResponse([
            'result' => 'OK'
        ]);
    }

    /**
     * @Route("/badge/decrease")
     * @Method("PUT")
     * @ApiDoc(
     *  resource="/api/push/",
     *  description="Decrease the badge number",
     *  parameters={
     *      {"name"="deviceToken", "dataType"="string", "required"=true, "description"="iOS device token"},
     *      {"name"="count", "dataType"="integer", "required"=false, "description"="Number to decrease. If not specified, badge will be set to 0"}
     *  }
     * )
     */
    public function badge(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $data = json_decode($request->getContent());
        $device = $em->getRepository('WassaMPSBundle:IOSDevice')->findOneByRegistrationToken($data->deviceToken);

        if (!$device) {
            throw new NotFoundHttpException('Unexisting device');
        }

        if (isset($data->count)) {
            $device->decreaseBadge($data->count);
        } else {
            $device->setBadge(0);
        }

        $em->persist($device);
        $em->flush();

        return new JsonResponse([
            'result' => 'OK'
        ]);
    }

    /**
     * @Route("/custom-data")
     * @Method("PUT")
     * @ApiDoc(
     *  resource="/api/push/",
     *  description="Update custom data",
     *  parameters={
     *      {"name"="deviceToken", "dataType"="string", "required"=true, "description"="iOS device token"},
     *      {"name"="key", "dataType"="string", "required"=false, "description"="Custom data key"},
     *      {"name"="value", "dataType"="mixed", "required"=false, "description"="Custom data value for key"}
     *  }
     * )
     */
    public function setCustomData(Request $request)
    {
        $deviceToken = $request->request->get('deviceToken');
        $em = $this->getDoctrine()->getManager();
        $device = $em->getRepository('WassaMPSBundle:IOSDevice')->findOneByRegistrationToken($deviceToken);

        if (!$device) {
            throw new NotFoundHttpException('Unexisting device');
        }

        $key = $request->request->get('key');
        $value = $request->request->get('value');

        $customData = $device->getCustomData();
        if (!is_null($value)) {
            $customData[$key] = $value;
        } else {
            unset($customData[$key]);
        }
        $device->setCustomData($customData);

        $em->persist($device);
        $em->flush();

        return new JsonResponse([
            'result' => 'OK'
        ]);
    }
}
