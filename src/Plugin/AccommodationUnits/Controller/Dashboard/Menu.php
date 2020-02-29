<?php
/**
 *
 */

namespace OmniTools\ApartmentsRental\Plugin\AccommodationUnits\Controller\Dashboard;

class Menu extends \OmniTools\Core\View\AbstractMenu
{
    /**
     *
     */
    public function generate(): array
    {
        return [
            new \OmniTools\Core\View\MenuItem(
                'Wohneinheiten',
                'ApartmentsRental/AccommodationUnits/Dashboard/index',
                'fa fa-house'
            )
        ];
    }


}
