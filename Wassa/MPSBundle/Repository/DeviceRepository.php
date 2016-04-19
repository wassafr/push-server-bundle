<?php

namespace Wassa\MPSBundle\Repository;

use Doctrine\ORM\EntityRepository;

class DeviceRepository extends EntityRepository
{
    public function findOneByRegistrationToken($registrationToken)
    {
        $dql = "SELECT d from WassaMPSBundle:Device d WHERE d.registrationToken = ':registrationToken'";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('registrationToken', $registrationToken);

        return $query->getSingleResult();
    }

    public function findByCustomData($customData)
    {
        $filter = json_encode($customData);
        $dql = "SELECT d from WassaMPSBundle:Device d WHERE d.customData LIKE '%$filter%'";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}