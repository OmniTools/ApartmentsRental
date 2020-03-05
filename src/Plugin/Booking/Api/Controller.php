<?php
/**
 *
 */

namespace OmniTools\ApartmentsRental\Plugin\Booking\Api;

class Controller extends \OmniTools\Core\Api\AbstractController
{
    /**
     *
     */
    public function costsActionUsingGet(
        \Doctrine\ORM\EntityManagerInterface $entityManager
    )
    {
        // Fetch unit
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
        $unit = $accommodationUnitRepository->find($this->getPayload('unitId'));

        $dateFrom = new \DateTime($this->getPayload('dateFrom'));
        $dateTo = new \DateTime($this->getPayload('dateTo'));

        $guests = $this->getPayloadOptional('guests');
        $dogs = $this->getPayloadOptional('dogs');

        $priceSegments = $unit->getPriceSegments($dateFrom, $dateTo, $guests, $dogs);

        return $priceSegments;
    }

    /**
     *
     */
    public function submitActionUsingPost(
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        \OmniTools\Core\Mail\Mailer $mailer,
        \OmniTools\Core\View $view,
        \OmniTools\Core\LoggerService $loggerService,
        \OmniTools\Core\Config $config
    )
    {

        $from = new \DateTime($this->getPayload('booking.dateFrom'));
        $to = new \DateTime($this->getPayload('booking.dateTo'));

        // Fetch unit
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
        $unit = $accommodationUnitRepository->find($this->getPayload('booking.unitId'));

        if ($unit === null) {
            throw new \Exception('Die Wohneinheit konnte nicht geladen werden.');
        }

        $guests = (int) $this->getPayload('booking.persons') + (int) $this->getPayloadOptional('booking.children');

        if ($unit->getMaxGuests() < $guests) {
            throw new \Exception(sprintf('Es sind maximal %s Gäste in dieser Wohnung möglich.', $unit->getMaxGuests()));
        }

        // Check if booking exists
        $bookingRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Booking::class);

        if ($bookingRepository->hasBookingInDateRange($from, $to, $unit)) {
            throw new \Exception('Das gewünschte Datum ist leider nicht mehr verfügbar.');
        }

        // Compose new customer
        $customer = new \OmniTools\ApartmentsRental\Persistence\Entity\Customer([
            'firstname' => $this->getPayloadOptional('customer.firstname'),
            'lastname' => $this->getPayload('customer.lastname'),
            'street' => $this->getPayloadOptional('customer.street'),
            'streetNumber' => $this->getPayloadOptional('customer.streetNumber'),
            'addition' => $this->getPayloadOptional('customer.addition'),
            'city' => $this->getPayloadOptional('customer.city'),
            'zipcode' => $this->getPayloadOptional('customer.zipcode'),
            'country' => $this->getPayloadOptional('customer.country'),
            'title' => $this->getPayloadOptional('customer.title'),
            'gender' => $this->getPayloadOptional('customer.gender'),
        ]);

        if (!empty($contacts = $this->getPayloadOptional('customer.contacts'))) {

            foreach ($contacts as $contactData) {

                $class = '\OmniTools\Addresses\Persistence\Entity\PersonContact' . $contactData['type'];
                $contact = new $class([
                    'scope' => $contactData['scope'] ?? null,
                    'value' => $contactData['value'] ?? null
                ]);

                $customer->addContact($contact);
            }
        }

        // Persist customer
        $entityManager->persist($customer);
        $entityManager->flush();

        // Log customer creation
        $loggerService->log($customer, 'Created');

        // Insert new booking
        $className = $this->getPayloadOptional('booking.type') ?? 'Booking';
        $className = '\OmniTools\ApartmentsRental\Persistence\Entity\\' . $className;

        $booking = new $className([
            'persons' => $this->getPayload('booking.persons'),
            'children' => $this->getPayloadOptional('booking.children'),
            'dogs' => $this->getPayloadOptional('booking.dogs'),
            'note' => $this->getPayloadOptional('booking.note'),
        ]);

        $booking->setAccommodationUnit($unit);
        $booking->setDateFrom($from);
        $booking->setDateTo($to);
        $booking->setCustomer($customer);

        // Persist booking
        $entityManager->persist($booking);
        $entityManager->flush();

        $loggerService->log($booking, 'Created');
        $loggerService->log($booking, 'CustomerCreated', [ 'id' => $customer->getId(), 'name' => $customer->getFullName() ]);

        // Create a message
        $message = new \OmniTools\Core\Mail\Message('Ihre Buchungsanfrage');
        $message->addTo($customer->getEmail());

        $cn = new \OmniTools\ApartmentsRental\Plugin\Booking\Controller\Booking\Controller;
        $viewFile = $cn->getPath() . 'resources/private/';
        $view->addPath($viewFile);

        $view->assign('unit', $unit);
        $view->assign('booking', $booking);
        $view->assign('customer', $customer);
        $html = $view->render($booking->getMailTemplate());

        $message->setBody($html);

        // Send the message
        $mailer->send($message);

        // Re-use message
        $message->clearTo();
        $message->addTo('bruening@mac.com');
        $message->addTo('bruening@pixel-fabrik.com');

        $mailer->send($message);

        $entityManager->flush();

        return [
            'bookingId' => $booking->getId()
        ];
    }
}
