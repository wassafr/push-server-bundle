<?php
/*
 * RegistrationEvent.php
 *
 * Copyright (C) WASSA SAS - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * 02/02/2016
 */

namespace Wassa\MPSBundle\Events;


use Symfony\Component\EventDispatcher\Event;

class RegistrationEvent extends Event
{
    protected $registrationToken;
    protected $platform;
    protected $customData;
    protected $result;
    protected $reason;

    public function __construct($registrationToken = null, $platform = null, $customData = null)
    {
        $this->registrationToken = $registrationToken;
        $this->platform = $platform;
        $this->customData = $customData;
    }

    /**
     * @return mixed
     */
    public function getRegistrationToken()
    {
        return $this->registrationToken;
    }

    /**
     * @return mixed
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * @return mixed
     */
    public function getCustomData()
    {
        return $this->customData;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     * @return RegistrationEvent
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param mixed $reason
     * @return RegistrationEvent
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }
}