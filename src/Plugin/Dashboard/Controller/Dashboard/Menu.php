<?php
/**
 *
 */

namespace OmniTools\ApartmentsRental\Plugin\Dashboard\Controller\Dashboard;

class Menu extends \OmniTools\Core\View\AbstractMenu
{
    /**
     *
     */
    public function generate(
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
                'ApartmentsRental/Dashboard/Dashboard/index',
                'fa-th'
            ),
            new \OmniTools\Core\View\MenuItem(
                'Belegung',
                'ApartmentsRental/Booking/Dashboard/index',
                'fa-calendar-alt',
                null,
                null,
                [
                    'data-counter' => count($requests)
                ]
            ),
            new \OmniTools\Core\View\MenuItem(
                'Gäste',
                'ApartmentsRental/Customer/Dashboard/index',
                'fa-user-friends'
            ),
            new \OmniTools\Core\View\MenuItem(
                'Wohneinheiten',
                'ApartmentsRental/AccommodationUnits/Dashboard/index',
                'fa-house'
            ),
            new \OmniTools\Core\View\MenuItem(
                'Konfiguration',
                'ApartmentsRental/Configurations/General/index',
                'fa-cog'
            )
        ];
    }
}
