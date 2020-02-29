<?php

namespace OmniTools\ApartmentsRental\Persistence\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @Entity
 * @Table(name="apartmentsrental_season", options={"collate":"utf8mb4_general_ci", "charset":"utf8mb4"})
 * @HasLifecycleCallbacks
 */
class Season extends \OmniTools\Core\Persistence\AbstractEntity
{
    /**
     * @Column(length=255)
     */
    protected $title;

    /**
     * @Column(type="datetime", name="date_from", nullable=true)
     */
    protected $dateFrom;

    /**
     * @Column(type="datetime", name="date_to", nullable=true)
     */
    protected $dateTo;

    /**
     *
     */
    public function getDateFrom(): \DateTime
    {
        return $this->dateFrom;
    }

    /**
     *
     */
    public function getDateTo(): \DateTime
    {
        return $this->dateTo;
    }

    /**
     *
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     *
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }
}
