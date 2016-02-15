<?php

namespace Wassa\MPSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * IOSDevice
 *
 * @ORM\Entity
 */
class IOSDevice extends Device
{
    /**
     * @var integer
     *
     * @ORM\Column(name="badge", type="integer", nullable=true)
     */
    private $badge;

    /**
     * Set badge
     *
     * @param integer $badge
     * @return IOSDevice
     */
    public function setBadge($badge)
    {
        $this->badge = $badge;

        return $this;
    }

    /**
     * Get badge
     *
     * @return integer 
     */
    public function getBadge()
    {
        return $this->badge;
    }

    public function increaseBadge($count = 1)
    {
        $this->badge += $count;
    }

    public function decreaseBadge($count = 1)
    {
        $this->increaseBadge($count * -1);
    }
}
