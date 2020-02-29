<?php
/**
 *
 */

namespace OmniTools\ApartmentsRental\Plugin\Configurations\Controller\General;

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
                'ApartmentsRental/Configurations/General/index',
                'fa fa-cog'
            ),
            new \OmniTools\Core\View\MenuItem(
                'Saisons',
                'ApartmentsRental/Configurations/Seasons/index',
                'fa fa-cog'
            )
        ];
    }
}
