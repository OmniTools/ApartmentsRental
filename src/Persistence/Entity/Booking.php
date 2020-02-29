<?php

namespace OmniTools\ApartmentsRental\Persistence\Entity;

/**
 * @Entity(repositoryClass="OmniTools\ApartmentsRental\Persistence\Repository\Booking")
 * @HasLifecycleCallbacks
 */
class Booking extends AbstractBooking
{
    /**
     *
     */
    protected $mailTemplate = 'mail/CustomerRequest.html';
}
