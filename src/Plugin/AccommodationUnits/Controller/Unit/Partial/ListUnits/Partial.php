<?php
/**
 *
 */

namespace OmniTools\ApartmentsRental\Plugin\AccommodationUnits\Controller\Unit\Partial\ListUnits;

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
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);

        $result = $accommodationUnitRepository->findBy([], ['title' => 'ASC']);

        $this->assign('units', $result);
    }
}
