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

class ApiController extends Controller
{
    /**
     * @Route("/register")
     * @Method("POST")
     */
    public function register(Request $request)
    {
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

        $device->setLastRegistration(new \DateTime());
        $em->persist($device);
        $em->flush();

        return new JsonResponse([
            'result' => 'OK'
        ]);
    }
}
