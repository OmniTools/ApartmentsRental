<?php

namespace OmniTools\ApartmentsRental\Persistence\Entity;

/**
 * @Entity
 * @Table(name="apartmentsrental_accommodationunit_charge", options={"collate":"utf8mb4_general_ci", "charset":"utf8mb4"})
 * @HasLifecycleCallbacks
 */
class AccommodationUnitCharge extends \OmniTools\Core\Persistence\AbstractEntity
{
    /**
     * @Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    protected $price;

    /**
     * @ManyToOne(targetEntity="AccommodationUnit")
     * @JoinColumn(name="accommodationunit_id", referencedColumnName="id", nullable=false)
     */
    protected $accommodationUnit;

    /**
     * @ManyToOne(targetEntity="Season")
     * @JoinColumn(name="season_id", referencedColumnName="id", nullable=false)
     */
    protected $season;

    /**
     *
     */
    public function getAccommodationUnit(): AccommodationUnit
    {
        return $this->accommodationUnit;
    }

    /**
     *
     */
    public function getPrice(): float
    {
        return (int) $this->price / 100;
    }

    /**
     *
     */
    public function getSeason(): Season
    {
        return $this->season;
    }

    /**
     *
     */
    public function setAccommodationUnit(AccommodationUnit $accommodationUnit): void
    {
        $this->accommodationUnit = $accommodationUnit;
    }

    /**
     *
     */
    public function setPrice($price): void
    {
        $this->price = (float) $price * 100;
    }

    /**
     *
     */
    public function setSeason(Season $season): void
    {
        $this->season = $season;
    }
}