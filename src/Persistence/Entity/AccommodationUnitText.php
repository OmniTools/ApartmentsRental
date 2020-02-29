<?php

namespace OmniTools\ApartmentsRental\Persistence\Entity;

/**
 * @Entity
 * @Table(name="apartmentsrental_accommodationunit_text", options={"collate":"utf8mb4_general_ci", "charset":"utf8mb4"})
 * @HasLifecycleCallbacks
 */
class AccommodationUnitText extends \OmniTools\Core\Persistence\AbstractEntity
{
    /**
     * @Column(type="string", length=255, nullable=true)
     */
    protected $textKey;

    /**
     * @Column(type="string", length=255, nullable=true)
     */
    protected $headline;

    /**
     * @Column(type="text", nullable=true)
     */
    protected $text;

    /**
     * @ManyToOne(targetEntity="AccommodationUnit")
     * @JoinColumn(name="accommodationunit_id", referencedColumnName="id", nullable=false)
     */
    protected $accommodationUnit;

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
    public function getText(): string
    {
        return $this->text;
    }

    /**
     *
     */
    public function getTextKey(): string
    {
        return $this->textKey;
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
    public function setText(string $text): void
    {
        $this->text = $text;
    }
}