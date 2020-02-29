<?php
/**
 *
 */

namespace OmniTools\ApartmentsRental\Plugin\Dashboard\Controller\Dashboard;

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
    public function indexAction(): \OmniTools\Core\View\Response
    {
        return $this->render();
    }
}