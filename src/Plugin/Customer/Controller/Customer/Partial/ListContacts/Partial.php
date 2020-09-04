<?php
/**
 *
 */

namespace OmniTools\ApartmentsRental\Plugin\Customer\Controller\Customer\Partial\ListContacts;

class Partial extends \OmniTools\Core\View\AbstractPartial
{
    /**
     *
     */
    public function getPath(): string
    {
        return __DIR__ . '/';
    }

    /**
     *
     */
    public function onBeforeRendering(
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): void
    {

    }
}
