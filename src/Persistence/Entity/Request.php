<?php

namespace OmniTools\ApartmentsRental\Persistence\Entity;

/**
 * @Entity(repositoryClass="OmniTools\ApartmentsRental\Persistence\Repository\Request")
 * @HasLifecycleCallbacks
 */
class Request extends AbstractBooking
{
    /**
     *
     */
    protected $mailTemplate = 'mail/CustomerRequest.html';
}
