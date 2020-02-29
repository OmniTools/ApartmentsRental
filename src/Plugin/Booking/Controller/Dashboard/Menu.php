<?php
/**
 *
 */

namespace OmniTools\ApartmentsRental\Plugin\Booking\Controller\Dashboard;

class Menu extends \OmniTools\Core\View\AbstractMenu
{
    /**
     *
     */
    public function generate(
        \OmniTools\Core\Http\Get $get,
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
