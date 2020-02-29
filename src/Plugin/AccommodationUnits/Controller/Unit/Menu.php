<?php
/**
 *
 */

namespace OmniTools\ApartmentsRental\Plugin\AccommodationUnits\Controller\Unit;

class Menu extends \OmniTools\ApartmentsRental\Plugin\AccommodationUnits\Controller\Dashboard\Menu
{
    /**
     *
     */
    public function generateDetails(
        \OmniTools\Core\Http\Get $get
    ): array
    {
        return [
            new \OmniTools\Core\View\MenuItem(
                'Übersicht',
                'ApartmentsRental/AccommodationUnits/Dashboard/index',
                'fa fa-house'
            ),
            new \OmniTools\Core\View\MenuItem(
                'Buchungen',
                'ApartmentsRental/AccommodationUnits/Dashboard/index',
                'fa fa-house'
            ),
            new \OmniTools\Core\View\MenuItem(
                'Anfragen',
                'ApartmentsRental/AccommodationUnits/Dashboard/index',
                'fa fa-house'
            ),

            new \OmniTools\Core\View\MenuItem(
                'Ausstattung',
                'ApartmentsRental/AccommodationUnits/Dashboard/index',
                'fa fa-house'
            ),
            new \OmniTools\Core\View\MenuItem(
                'Preise',
                $this->controller->getActionUri('prices', [ 'unitId' => $get->get('unitId') ]),
                'fa fa-money-check-edit-alt'
            ),
            new \OmniTools\Core\View\MenuItem(
                'bearbeiten',
                $this->controller->getActionUri('edit', [ 'unitId' => $get->get('unitId') ]),
                'fa fa-edit'
            ),
            new \OmniTools\Core\View\MenuItem(
                'löschen',
                $this->controller->getActionUri('ajaxDelete', [ 'unitId' => $get->get('unitId') ]),
                'fa fa-times',
                'ajax',
                null,
                [ 'data-confirm' => 'Soll diese Wohneinheit wirklich gelöscht werden?' ]
            )
        ];
    }
}
