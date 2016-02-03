<?php
/*
 * Events.php
 *
 * Copyright (C) WASSA SAS - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * 02/02/2016
 */

namespace Wassa\MPSBundle\Events;

final class Events
{
    /**
     * This event will be fired before process the registration query
     */
    const REGISTRATION_PRECHECK = "wassa_mps.registration.pre_check";

    /**
     * This event will be fired after process the registration query
     */
    const REGISTRATION_POSTCHECK = "wassa_mps.registration.post_check";
}