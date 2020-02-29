<?php
/**
 *
 */

namespace OmniTools\ApartmentsRental\Plugin\Customer\Controller\Dashboard;

class Menu extends \OmniTools\Core\View\AbstractMenu
{
    /**
     *
     */
    public function generate(
        \OmniTools\Core\Http\Get $get
    ): array
    {
        return [
            new \OmniTools\Core\View\MenuItem(
                'Übersicht',
                'ApartmentsRental/Customer/Dashboard/index',
                'fa fa-house'
            ),
            new \OmniTools\Core\View\MenuItem(
                'Verzeichnis',
                'ApartmentsRental/Customer/Customer/index',
                'fa fa-house'
            )
        ];
    }
}
