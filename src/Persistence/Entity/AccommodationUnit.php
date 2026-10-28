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
     * @Column(type="string", length=16, name="price_calculation", options={"unsigned"=true, "default" : "PerUnit"})
     */
    protected $priceCalculation = 'PerUnit';

    /**
     * @Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    protected $maxGuests;

    /**
     * @Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    protected $maxDogs;

    /**
     * @Column(type="boolean", nullable=false, options={"unsigned"=true, "default": false})
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
     * @Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    protected $maxToddlers;

    /**
     * @Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    protected $additionalCostsPerGuest;

    /**
     * @Column(type="integer", nullable=true, options={"unsigned"=true})
     */
    protected $additionalCostsPerGuestThreshold;

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
        return (float) $this->additionalCosts / 100;
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
    public function getPriceForDates(\DateTime $from, \DateTime $to, $dogs = 0): float
    {
        $price = $this->getPriceForDateRange($from, $to);

        $price += $this->getAdditionalCosts();

        $price += ($dogs * $this->getPricePerDog());

        return $price;
    }

    /**
     *
     */
    public function getPriceForDateRange(\DateTime $from, \DateTime $to): float
    {
        $price = 0.0;

        $from = clone $from;

        while ($from < $to) {

            $dayFee = null;

            foreach ($this->getCharges() as $charge) {

                if ($from < $charge->getSeason()->getDateFrom() or $from > $charge->getSeason()->getDateTo()) {
                    continue;
                }

                if ($dayFee !== null && $charge->getPrice() <= $dayFee) {
                    continue;
                }

                $dayFee = $charge->getPrice();
            }

            $price += $dayFee ?? $this->getPrice();

            $from->modify('+1 day');
        }

        return $price;
    }

    /**
     *
     */
    public function getPriceSegments(\DateTime $from, \DateTime $to, $dogs = 0): array
    {
        $list = [];
        $total = 0.0;

        // Calculate booking fee
        $bookingFee = $this->getPriceForDateRange($from, $to);

        if ($bookingFee === null) {
            return [
                'error' => 'NoPriaceAvaialable'
            ];
        }

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
    public function setAdditionalCosts($costs)
    {
        $this->additionalCosts = \OmniTools\ApartmentsRental\Money::toCents($costs);
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
    public function setPrice($price): void
    {
        $this->price = \OmniTools\ApartmentsRental\Money::toCents($price);
    }

    /**
     *
     */
    public function setPricePerDog($price): void
    {
        $this->pricePerDog = \OmniTools\ApartmentsRental\Money::toCents($price);
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
    public function getMaxToddlers(): ?int
    {
        return $this->maxToddlers;
    }

    public function setMaxToddlers(int $maxToddlers): void
    {
        $this->maxToddlers = $maxToddlers;
    }

    public function getAdditionalCostsPerGuest(): float
    {
        return (float) $this->additionalCostsPerGuest / 100;
    }

    public function setAdditionalCostsPerGuest($costs): void
    {
        $this->additionalCostsPerGuest = \OmniTools\ApartmentsRental\Money::toCents($costs);
    }

    public function getAdditionalCostsPerGuestThreshold(): ?int
    {
        return $this->additionalCostsPerGuestThreshold;
    }

    public function setAdditionalCostsPerGuestThreshold(?int $threshold): void
    {
        $this->additionalCostsPerGuestThreshold = $threshold;
    }

    public function hasChargeForSeason(Season $season): bool
    {
        return $this->getChargeForSeason($season) !== null;
    }

    public function getChargeForSeason(Season $season): ?AccommodationUnitCharge
    {
        foreach ($this->charges as $charge) {
            if ($charge->getSeason() === $season) {
                return $charge;
            }
        }
        return null;
    }

    public function getMinNightsForDate(\DateTime $date): int
    {
        $minNights = 0;
        foreach ($this->charges as $charge) {
            $season = $charge->getSeason();
            if ($date >= $season->getDateFrom() && $date <= $season->getDateTo()) {
                $minNights = max($minNights, $season->getMinNights() ?? 0);
            }
        }
        return $minNights;
    }
}
