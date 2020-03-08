<?php

namespace OmniTools\ApartmentsRental\Persistence\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @Entity
 * @Table(name="apartmentsrental_booking", options={"collate":"utf8mb4_general_ci", "charset":"utf8mb4"})
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="type", type="string")
 * @DiscriminatorMap({
 *     "Booking" = "Booking",
 *     "Request" = "Request"
 * })
 * @HasLifecycleCallbacks
 */
abstract class AbstractBooking extends \OmniTools\Core\Persistence\AbstractEntity
{
    /**
     * @Column(type="datetime", name="date_from", nullable=true)
     */
    protected $dateFrom;

    /**
     * @Column(type="datetime", name="date_to", nullable=true)
     */
    protected $dateTo;

    /**
     * @ManyToOne(targetEntity="AccommodationUnit")
     * @JoinColumn(name="accommodationunit_id", referencedColumnName="id", nullable=false)
     */
    protected $accommodationUnit;

    /**
     * @ManyToOne(targetEntity="Customer")
     * @JoinColumn(name="customer_id", referencedColumnName="id", nullable=true)
     */
    protected $customer;

    /**
     * @Column(length=16, options={"default":"Created"})
     */
    protected $state = 'Created';

    /**
     * @Column(type="integer", nullable=false, options={"unsigned"=true, "default"=0})
     */
    protected $persons = 0;

    /**
     * @Column(type="integer", nullable=false, options={"unsigned"=true, "default"=0})
     */
    protected $children = 0;

    /**
     * @Column(type="integer", nullable=false, options={"unsigned"=true, "default"=0})
     */
    protected $toddlers = 0;

    /**
     * @Column(type="integer", nullable=false, options={"unsigned"=true, "default"=0})
     */
    protected $dogs = 0;

    /**
     * @Column(type="text", nullable=true)
     */
    protected $note;

    /**
     *
     */
    protected $mailTemplate = 'mail/CustomerBooking.html';


    /**
     *
     */
    public function getAccommodationUnit(): AccommodationUnit
    {
        return $this->accommodationUnit;
    }

    /**
     * dfgdfgdf
     */
    public function getChildren(): int
    {
        return $this->children;
    }

    /**
     *
     */
    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

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
    public function getDogs(): int
    {
        return (int) $this->dogs;
    }

    /**
     *
     */
    public function getGuestsCount(): int
    {
        return $this->children + $this->persons;
    }

    /**
     *
     */
    public function getMailTemplate(): string
    {
        return $this->mailTemplate;
    }

    /**
     *
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     *
     */
    public function getPersons(): int
    {
        return $this->persons;
    }

    /**
     * @deprecated
     */
    public function getPets(): int
    {
        return $this->dogs;
    }

    /**
     *
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     *
     */
    public function getToddlers(): string
    {
        return $this->toddlers;
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
    public function setCustomer(Customer $customer): void
    {
        $this->customer = $customer;
    }

    /**
     *
     */
    public function setDateFrom(\DateTime $date): void
    {
        $this->dateFrom = $date;
    }

    /**
     *
     */
    public function setDateTo(\DateTime $date): void
    {
        $this->dateTo = $date;
    }

    /**
     *
     */
    public function setState(string $state): void
    {
        $this->state = $state;
    }

    /**
     *
     */
    public function updateState(): void
    {
        $state = 'Complete';

    }
}
