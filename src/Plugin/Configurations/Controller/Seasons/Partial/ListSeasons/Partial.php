<?php
/**
 *
 */

namespace OmniTools\ApartmentsRental\Plugin\Configurations\Controller\Seasons\Partial\ListSeasons;

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
        $seasonsRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Season::class);

        $result = $seasonsRepository->findBy([], ['dateFrom' => 'ASC']);

        $this->assign('seasons', $result);
    }
}
