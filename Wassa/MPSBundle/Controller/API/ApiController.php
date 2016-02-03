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
use Wassa\MPSBundle\Events\Events;
use Wassa\MPSBundle\Events\RegistrationEvent;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class ApiController extends Controller
{
    /**
     * @Route("/register")
     * @Method("POST")
     * @ApiDoc(
     *  resource=true,
     *  description="Register a new or existing device",
     *  input="Wassa\MPSBundle\API\RegistrationParameters",
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
        }
        elseif ($data->platform == 'android') {
            $class = '\Wassa\MPSBundle\Entity\AndroidDevice';
        }
        else {
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

        $customData = isset($data->customData) ? ( is_string($data->customData) ? json_decode($data->customData) : $data->customData) : null;
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
}
