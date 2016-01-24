<?php

namespace Wassa\MPSBundle\Repository;

use Doctrine\ORM\EntityRepository;

class DeviceRepository extends EntityRepository
{
    public function findOnByRegistrationToken($registrationToken)
    {
        $dql = "SELECT m from WassaMPSBundle:Device d WHERE d.registrationToken = ':registrationToken'";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('registrationToken', $registrationToken);

        return $query->getSingleResult();
    }
}