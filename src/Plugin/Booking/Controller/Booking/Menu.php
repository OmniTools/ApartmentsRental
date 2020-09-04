<?php
/**
 *
 */

namespace OmniTools\ApartmentsRental\Plugin\Booking\Controller\Booking;

class Menu extends \OmniTools\Core\View\AbstractMenu
{
    /**
     *
     */
    public function generate(): array
    {
        return [ ];
    }

    /**
     *
     */
    public function generateBooking(
        \OmniTools\Core\Http\Get $get
    ): array
    {
        return [
            new \OmniTools\Core\View\MenuItem(
                'Übersicht',
                'ApartmentsRental/Booking/Dashboard/index',
                'fa-calendar-alt'
            ),
            new \OmniTools\Core\View\MenuItem(
                'stornieren',
                'ApartmentsRental/Booking/Booking/ajaxCancel?bookingId=' . $get->get('bookingId'),
                'fa-times',
                'ajax',
                'sdfsdf',
                [ 'data-confirm' => 'Soll diese Buchung wirklich storniert werden?' ]
            )
        ];
    }

    /**
     *
     */
    public function generateOverview(
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): array
    {
        // Fetch requests
        $requestRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Request::class);
        $requests = $requestRepository->findBy([
            'state' => 'Created'
        ]);

        return [
            new \OmniTools\Core\View\MenuItem(
                'Übersicht',
                'ApartmentsRental/Booking/Dashboard/index',
                'fa-calendar-alt'
            ),
            new \OmniTools\Core\View\MenuItem(
                'Buchungen',
                'ApartmentsRental/Booking/Booking/index',
                'fa-calendar-alt'
            ),
            new \OmniTools\Core\View\MenuItem(
                'Anfragen',
                'ApartmentsRental/Booking/Booking/requests',
                'fa-calendar-alt',
                null,
                null,
                [
                    'data-counter' => count($requests)
                ]
            )
        ];
    }
}
