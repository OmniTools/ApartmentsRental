<?php
/**
 *
 */

namespace OmniTools\ApartmentsRental\Plugin\AccommodationUnits\Controller\Unit\Partial\ListCharges;

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
        // Fetch unit
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
        $unit = $accommodationUnitRepository->find($this->payload['unitId']);

        // Fetch charges
        $chargesRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnitCharge::class);
        $charges = $chargesRepository->findBy([
            'accommodationUnit' => $unit
        ]);

        $this->assign('charges', $charges);
    }
}
