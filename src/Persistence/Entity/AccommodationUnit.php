<?php

namespace OmniTools\ApartmentsRental\Persistence\Entity;

use Doctrine\Common\Collections\Collection;

/**
 * @Entity(repositoryClass="OmniTools\ApartmentsRental\Persistence\Repository\AccommodationUnit")
 * @Table(name="apartmentsrental_accommodationunit", options={"collate":"utf8mb4_general_ci", "charset":"utf8mb4"})
 * @HasLifecycleCallbacks
 */
class AccommodationUnit extends \OmniTools\Core\Persistence\AbstractEntity
{
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $title;

    /**
     * @Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    protected $price;

    /**
     * @Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    protected $pricePerDog;

    /**
     * @Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    protected $additionalCosts;

    /**
     * @Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    protected $additionalCostsPerGuest;

    /**
     * @Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    protected $additionalCostsPerGuestThreshold;

    /**
     * @Column(type="string", length=16, name="price_calculation", options={"default" : "PerUnit"})
     */
    protected $priceCalculation = 'PerUnit';

    /**
     * @Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    protected $maxGuests;

    /**
     * @Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    protected $maxToddlers;

    /**
     * @Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    protected $maxDogs;

    /**
     * @Column(type="boolean", nullable=false, options={"default": false})
     */
    protected $bookableOnline = false;

    /**
     * @OneToMany(targetEntity="AccommodationUnitCharge", mappedBy="accommodationUnit", cascade={"all"})
     */
    protected $charges;

    /**
     * @OneToMany(targetEntity="AccommodationUnitText", mappedBy="accommodationUnit", cascade={"all"})
     */
    protected $texts;

    /**
     *
     */
    public function __construct(array $record = null)
    {
        parent::__construct($record);

        $this->texts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->charges = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     *
     */
    public function addText(AccommodationUnitText $text): void
    {
        $text->setAccommodationUnit($this);

        $this->texts->add($text);
    }

    /**
     *
     */
    public function getAdditionalCosts(): float
    {
        return $this->additionalCosts / 100;
    }

    /**
     *
     */
    public function getAdditionalCostsPerGuest(): float
    {
        return $this->additionalCostsPerGuest / 100;
    }

    /**
     *
     */
    public function getAdditionalCostsPerGuestThreshold(): int
    {
        return $this->additionalCostsPerGuestThreshold;
    }

    /**
     *
     */
    public function getBookableOnline(): bool
    {
        return $this->bookableOnline;
    }

    /**
     *
     */
    public function getCharges()
    {
        return $this->charges;
    }

    /**
     *
     */
    public function getMaxDogs(): ?int
    {
        return $this->maxDogs;
    }

    /**
     *
     */
    public function getMaxGuests(): ?int
    {
        return $this->maxGuests;
    }

    /**
     *
     */
    public function getMaxToddlers(): ?int
    {
        return $this->maxToddlers;
    }

    /**
     *
     */
    public function getPrice(): float
    {
        return (float) $this->price / 100;
    }

    /**
     *
     */
    public function getPricePerDog(): float
    {
        return (float) $this->pricePerDog / 100;
    }

    /**
     *
     */
    public function getPriceCalculation(): string
    {
        return $this->priceCalculation;
    }

    /**
     *
     */
    public function getPriceForDates(\DateTime $from, \DateTime $to, $guests = 1, $dogs = 0): float
    {
        $price = 0;

        $from = clone $from;

        while ($from < $to) {

            $dayFee = $this->getPrice();

            foreach ($this->getCharges() as $charge) {

                if ($from < $charge->getSeason()->getDateFrom() or $to > $charge->getSeason()->getDateTo()) {
                    continue;
                }

                if ($charge->getPrice() <= $dayFee) {
                    continue;
                }

                $dayFee = $charge->getPrice();
            }

            $price += $dayFee;

            $from->modify('+1 day');
        }

        $price += $this->getAdditionalCosts();

        $price += ($dogs * $this->getPricePerDog());

        if (!empty($this->getAdditionalCostsPerGuest()) and $guests >= $this->getAdditionalCostsPerGuestThreshold()) {

            $additionalPayers = $guests + 1 - $this->getAdditionalCostsPerGuestThreshold();

            $price += $additionalPayers * $this->getAdditionalCostsPerGuest();
        }

        return $price;
    }

    /**
     *
     */
    public function getPriceForDateRange(\DateTime $from, \DateTime $to): float
    {
        $price = 0;

        $from = clone $from;

        while ($from < $to) {

            $dayFee = $this->getPrice();

            foreach ($this->getCharges() as $charge) {

                if ($from < $charge->getSeason()->getDateFrom() or $to > $charge->getSeason()->getDateTo()) {
                    continue;
                }

                if ($charge->getPrice() <= $dayFee) {
                    continue;
                }

                $dayFee = $charge->getPrice();
            }

            $price += $dayFee;

            $from->modify('+1 day');
        }

        return $price;
    }

    /**
     *
     */
    public function getPriceSegments(\DateTime $from, \DateTime $to, $guests = 1, $dogs = 0): array
    {
        $list = [];
        $total = 0.0;

        // Calculate booking fee
        $bookingFee = $this->getPriceForDateRange($from, $to);

        $list[] = [
            'title' => 'Buchungsgebühr',
            'total' => $bookingFee
        ];

        $total += $bookingFee;

        // Calculate fee for dogs
        if ($dogs > 0) {

            $dogsFee = $dogs * $this->getPricePerDog();

            $list[] = [
                'title' => 'Gebühr für Hunde',
                'total' => $dogsFee
            ];

            $total += $dogsFee;
        }

        if (($additionalCosts = $this->getAdditionalCosts()) > 0) {

            $list[] = [
                'title' => 'Nebenkosten',
                'total' => $additionalCosts,
                'text' => $this->getTextByKey('additionalCosts') ? $this->getTextByKey('additionalCosts')->getText() : (string) null
            ];

            $total += $additionalCosts;
        }

        if (!empty($this->getAdditionalCostsPerGuest()) and !empty($this->getAdditionalCostsPerGuestThreshold()) and $guests >= $this->getAdditionalCostsPerGuestThreshold()) {
            
            $additionalPayers = $guests + 1 - $this->getAdditionalCostsPerGuestThreshold();
            $additionalCosts = $additionalPayers * $this->getAdditionalCostsPerGuest();

            $list[] = [
                'title' => 'Zusatzkosten für ' . $additionalPayers . ' weitere Gäste',
                'total' => $additionalCosts,
                // 'text' => $this->getTextByKey('additionalCosts') ? $this->getTextByKey('additionalCosts')->getText() : (string) null
            ];

            $total += $additionalCosts;
        }


        return [
            'positions' => $list,
            'total' => $total
        ];
    }

    /**
     *
     */
    public function getPriceSegmentsForBooking(\OmniTools\ApartmentsRental\Persistence\Entity\AbstractBooking $booking): array
    {
        return $this->getPriceSegments($booking->getDateFrom(), $booking->getDateTo(), $booking->getDogs());
    }

    /**
     *
     */
    public function getTextByKey(string $key): ?AccommodationUnitText
    {
        foreach ($this->texts as $text) {

            if ($text->getTextKey() == $key) {
                return $text;
            }
        }

        return null;
    }

    /**
     *
     */
    public function getTexts(): \Doctrine\Common\Collections\Collection
    {
        return $this->texts;
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
    public function setAdditionalCosts(float $costs): void
    {
        $this->additionalCosts = $costs * 100;
    }

    /**
     *
     */
    public function setAdditionalCostsPerGuest(float $costs): void
    {
        $this->additionalCostsPerGuest = $costs * 100;
    }

    /**
     *
     */
    public function setAdditionalCostsPerGuestThreshold(int $threshold): void
    {
        $this->additionalCostsPerGuestThreshold = $threshold;
    }

    /**
     *
     */
    public function setBookableOnline(bool $bookable): void
    {
        $this->bookableOnline = $bookable;
    }

    /**
     *
     */
    public function setMaxDogs(int $maxDogs): void
    {
        $this->maxDogs = $maxDogs;
    }

    /**
     *
     */
    public function setMaxGuests(int $maxGuests): void
    {
        $this->maxGuests = $maxGuests;
    }

    /**
     *
     */
    public function setMaxToddlers(int $maxToddlers): void
    {
        $this->maxToddlers = $maxToddlers;
    }

    /**
     *
     */
    public function setPrice($price): void
    {
        $this->price = $price * 100;
    }

    /**
     *
     */
    public function setPricePerDog($price): void
    {
        $this->pricePerDog = $price * 100;
    }

    /**
     *
     */
    public function setPriceCalculation(string $priceCalculation): void
    {
        $this->priceCalculation = $priceCalculation;
    }

    /**
     *
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }
}
