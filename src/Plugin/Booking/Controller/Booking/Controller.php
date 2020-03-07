<?php
/**
 *
 */

namespace OmniTools\ApartmentsRental\Plugin\Booking\Controller\Booking;

use OmniTools\ApartmentsRental\Persistence\Entity\Request;
use OmniTools\Core\Http\Get;
use OmniTools\Core\Http\Post;
use \OmniTools\Core\View\Response;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class Controller extends \OmniTools\Core\AbstractController
{
    /**
     * @return string
     */
    public function getPath(): string
    {
        return __DIR__ . '/';
    }

    /**
     *
     */
    public function ajaxCancelAction(
        Get $get,
        \OmniTools\Core\LoggerService $loggerService,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        // Fetch booking
        $bookingRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Booking::class);
        $booking = $bookingRepository->find($get->get('bookingId'));

        $booking->setState('Cancelled');

        // Log customer creation
        $loggerService->log($booking, 'Cancelled');

        $entityManager->flush();

        return new \OmniTools\Core\View\ResponseJson([
            'success' => 'Die Buchung wurde storniert.'
        ]);
    }

    /**
     *
     */
    public function ajaxCreateAction(
        \OmniTools\Core\Http\Post $post,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        // Fetch unit
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
        $unit = $accommodationUnitRepository->find($post->get('unitId'));

        // Check if booking exists
        $bookingRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Booking::class);

        $from = new \DateTime($post->get('dateFrom'));
        $to = new \DateTime($post->get('dateTo'));

        if ($to < $from) {
            throw new \Exception('Das Ende der Buchung darf nicht vor dem Anfang der Buchung liegen.');
        }

        if ($to == $from) {
            throw new \Exception('Anfang und Ende der Buchung können nicht auf den gleichen Tag fallen.');
        }

        if ($bookingRepository->hasBookingInDateRange($from, $to, $unit)) {
            throw new \Exception('In diesem Datumsbereich existiert bereits eine Buchung.');
        }

        // Create new booking
        $booking = new \OmniTools\ApartmentsRental\Persistence\Entity\Booking();
        $booking->setAccommodationUnit($unit);
        $booking->setDateFrom(new \DateTime($post->get('dateFrom')));
        $booking->setDateTo(new \DateTime($post->get('dateTo')));

        $entityManager->persist($booking);
        $entityManager->flush();

        return new \OmniTools\Core\View\ResponseJson([
            'redirect' => $this->getActionUri('booking', [
                'bookingId' => $booking->getId()
            ])
        ]);
    }

    /**
     *
     */
    public function ajaxCustomerConnectAction(
        Get $get,
        \OmniTools\Core\Http\Post $post,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        // Fetch booking
        $bookingRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Booking::class);
        $booking = $bookingRepository->find($get->get('bookingId'));

        // Fetch customer
        $customerRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Customer::class);
        $customer = $customerRepository->find($post->get('customerId'));

        $booking->setCustomer($customer);
        $booking->updateState();

        $entityManager->flush();

        return new \OmniTools\Core\View\ResponseJson([]);
    }

    /**
     *
     */
    public function ajaxCustomerCreateAction(
        Get $get,
        \OmniTools\Core\Http\Post $post,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        // Fetch booking
        $bookingRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Booking::class);
        $booking = $bookingRepository->find($get->get('bookingId'));

        // Create customer
        $customer = new \OmniTools\ApartmentsRental\Persistence\Entity\Customer([
            'firstname' => $post->get('firstname'),
            'lastname' => $post->get('lastname')
        ]);

        $entityManager->persist($customer);

        // Connect customer
        $booking->setCustomer($customer);

        $entityManager->flush();

        return new \OmniTools\Core\View\ResponseJson([
            'refresh' => true
        ]);
    }

    /**
     *
     */
    public function ajaxModalBookingReviewAction(
        Get $get,
        \OmniTools\Core\View $view,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        // Fetch unit
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
        $unit = $accommodationUnitRepository->find($get->get('unitId'));

        // Fetch bookings
        $bookingRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Booking::class);
        $bookings = $bookingRepository->getBookingsForDate(new \DateTime($get->get('dateFrom')), $unit);
        $view->assign('bookings', $bookings);

        return new \OmniTools\Core\View\ResponseJson([
            'html' => $view->render('AjaxModalBookingReview.html.twig')
        ]);
    }

    /**
     *
     */
    public function ajaxModalComposeAction(
        \OmniTools\Core\View $view,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        // Fetch units
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
        $units = $accommodationUnitRepository->findBy([]);

        $view->assign('units', $units);

        return new \OmniTools\Core\View\ResponseJson([
            'html' => $view->render('AjaxModalCompose.html.twig')
        ]);
    }

    /**
     *
     */
    public function ajaxModalCustomerConnectAction(
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        Get $get,
        \OmniTools\Core\View $view
    ): Response
    {
        // Fetch booking
        $bookingRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Booking::class);
        $booking = $bookingRepository->find($get->get('bookingId'));

        $view->assign('booking', $booking);

        // Fetch customers
        $customerRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Customer::class);
        $customers = $customerRepository->findBy([]);

        $view->assign('customers', $customers);


        return new \OmniTools\Core\View\ResponseJson([
            'html' => $view->render('AjaxModalCustomerConnect.html.twig')
        ]);
    }

    /**
     *
     */
    public function ajaxModalRequestHistoryAction(
        Get $get,
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        \OmniTools\Core\View $view,
        \OmniTools\Core\LoggerService $loggerService
    ): Response
    {
        // Fetch requests
        $requestRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Request::class);
        $request = $requestRepository->find($get->get('requestId'));

        // Fetch log history
        $history = $loggerService->getHistory($request);

        return new \OmniTools\Core\View\ResponseJson([
            'html' => $view->render('AjaxModalRequestHistory.html.twig', [
                'history' => $history
            ])
        ]);
    }

    /**
     *
     */
    public function ajaxRequestCancelAction(
        \OmniTools\Core\View\Front $front,
        Get $get,
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        \OmniTools\Core\LoggerService $loggerService
    ): Response
    {
        // Fetch request
        $requestRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Request::class);
        $request = $requestRepository->find($get->get('requestId'));

        $requestId = $request->getId();

        $request->setState('Cancelled');
        $loggerService->log($request, 'Cancelled');
        $entityManager->flush();

        $front->flash('Die Anfrage wurde storniert.');

        return new \OmniTools\Core\View\ResponseJson([
            'redirect' => $this->getActionUri('requests', [
                'highlight' => $requestId
            ])
        ]);
    }

    /**
     *
     */
    public function ajaxRequestConvertAction(
        Get $get,
        \OmniTools\Core\View\Front $front,
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        \OmniTools\Core\LoggerService $loggerService
    ): Response
    {
        // Fetch request
        $requestRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Request::class);
        $request = $requestRepository->find($get->get('requestId'));

        if (empty($request)) {
            throw new \Exception('Die Anfrage konnte nicht geladen werden.');
        }

        $sql = "UPDATE apartmentsrental_booking SET type = 'Booking' WHERE id = " . $request->getId() . " LIMIT 1";
        $entityManager->getConnection()->exec($sql);

        // Log approval
        $loggerService->log($request, 'Approved');

        // Create flash message
        $front->flash('Die Anfrage wurde bestätigt.');

        return new \OmniTools\Core\View\ResponseJson([
            'redirect' => $this->getActionUri('booking', [
                'bookingId' => $request->getId()
            ])
        ]);
    }

    /**
     *
     */
    public function bookingAction(
        Get $get,
        \OmniTools\Core\View $view,
        \OmniTools\Core\LoggerService $loggerService,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        // Fetch booking
        $bookingRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Booking::class);
        $booking = $bookingRepository->find($get->get('bookingId'));

        $view->assign('booking', $booking);

        return $this->render();
    }

    /**
     *
     */
    public function indexAction(
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        // Fetch bookings
        $bookingsRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Booking::class);
        $bookings = $bookingsRepository->getBookingsComing();
        $bookingsPast = $bookingsRepository->getBookingsPast();

        return $this->render(null, [
            'bookings' => $bookings,
            'bookingsArchive' => $bookingsPast,
        ]);
    }


    /**
     *
     */
    public function requestAction(
        Get $get,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        // Fetch requests
        $requestRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Request::class);
        $request = $requestRepository->find($get->get('requestId'));

        if ($request === null) {
            throw new \OmniTools\Core\Exception\InvalidContext('Die Buchungsanfrage konnte nicht geladen werden.');
        }

        $isValid = $requestRepository->isRequestConvertable($request);

        return $this->render(null, [
            'request' => $request,
            'isValid' => $isValid
        ]);
    }

    /**
     *
     */
    public function requestsAction(
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        // Fetch requests
        $requestRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Request::class);
        $requests = $requestRepository->findBy([
            'state' => 'Created'
        ]);

        // Fetch requests
        $requestsArchive = $requestRepository->findBy([
            'state' => 'Cancelled'
        ]);


        return $this->render(null, [
            'requests' => $requests,
            'requestsArchive' => $requestsArchive
        ]);
    }

    /**
     *
     */
    public function unitAction(
        Get $get,
        \OmniTools\Core\View $view,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        // Fetch units
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
        $unit = $accommodationUnitRepository->find($get->get('unitId'));

        $view->assign('unit', $unit);

        // Fetch bookings
        $bookingRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Booking::class);
        $bookings = $bookingRepository->findBy([
            'accommodationUnit' => $unit
        ], ['dateFrom' => 'ASC']);

        $view->assign('bookings', $bookings);

        return $this->render();
    }
}
