<?php
/**
 *
 */

namespace OmniTools\ApartmentsRental\Plugin\Configurations\Controller\Seasons;

use OmniTools\Core\Http\Post;
use OmniTools\Core\Http\Get;
use \OmniTools\Core\View\Response;

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
    public function ajaxCreateAction(
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        Post $post,
        \OmniTools\Core\View\Front $front
    ): Response
    {
        // Validate required input
        $post->require([ 'title', 'dateFrom', 'dateTo' ]);

        // Compose new season
        $season = new \OmniTools\ApartmentsRental\Persistence\Entity\Season([
            'title' => $post->get('title'),
            'dateFrom' => \DateTime::createFromFormat('Y-m-d', $post->get('dateFrom')),
            'dateTo' => \DateTime::createFromFormat('Y-m-d', $post->get('dateTo'))
        ]);

        // Persist season
        $entityManager->persist($season);
        $entityManager->flush();

        return new \OmniTools\Core\View\ResponseJson([
            'modalDismiss' => true,
            'replace' => [
                'selector' => '#seasonsReceiver',
                'html' => $front->renderPartial('ApartmentsRental/Plugin/Configurations/Controller/Seasons/Partial/ListSeasons', [
                    'highlight' => $season->getId()
                ])
            ]
        ]);
    }

    /**
     *
     */
    public function ajaxDeleteAction(
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        Get $get
    ): Response
    {
        // Fetch season
        $seasonsRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Season::class);
        $season = $seasonsRepository->find($get->get('seasonId'));

        $seasonId = $season->getId();

        // Delete season
        $entityManager->remove($season);
        $entityManager->flush();

        return new \OmniTools\Core\View\ResponseJson([
            'success' => 'Die Saison wurde gelöscht.',
            'fadeOut' => '[data-season="' . $seasonId . '"]'
        ]);
    }

    /**
     *
     */
    public function ajaxUpdateAction(
        Get $get,
        Post $post,
        \OmniTools\Core\View\Front $front,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        // Fetch season
        $seasonsRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Season::class);
        $season = $seasonsRepository->find($get->get('seasonId'));

        // Update season
        $data = $post->get('season');
        $data['dateFrom'] = new \DateTime($data['dateFrom']);
        $data['dateTo'] = new \DateTime($data['dateTo']);

        $season->setFromArray($data);

        $entityManager->flush();

        return new \OmniTools\Core\View\ResponseJson([
            'modalDismiss' => true,
            'replace' => [
                'selector' => '#seasonsReceiver',
                'html' => $front->renderPartial('ApartmentsRental/Plugin/Configurations/Controller/Seasons/Partial/ListSeasons', [
                    'highlight' => $season->getId()
                ])
            ]
        ]);
    }

    /**
     *
     */
    public function ajaxUpdateChargesAction(
        Post $post,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        $chargesRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnitCharge::class);

        foreach ($post->get('charge') as $chargeId => $value) {

            $charge = $chargesRepository->find($chargeId);
            $charge->setPrice($value);
        }

        $entityManager->flush();

        return new \OmniTools\Core\View\ResponseJson;
    }

    /**
     *
     */
    public function ajaxModalComposeAction(
        \OmniTools\Core\View $view
    ): Response
    {
        return new \OmniTools\Core\View\ResponseJson([
            'html' => $view->render('AjaxModalCompose.html.twig')
        ]);
    }

    /**
     *
     */
    public function ajaxModalEditAction(
        Get $get,
        \OmniTools\Core\View $view,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        // Fetch season
        $seasonsRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Season::class);
        $season = $seasonsRepository->find($get->get('seasonId'));

        $view->assign('season', $season);

        return new \OmniTools\Core\View\ResponseJson([
            'html' => $view->render('AjaxModalEdit.html.twig')
        ]);
    }

    /**
     *
     */
    public function generateAllChargesAction(
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        // Fetch season
        $seasonsRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Season::class);
        $seasons = $seasonsRepository->findBy([]);

        // Fetch units
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
        $units = $accommodationUnitRepository->findBy([]);

        foreach ($units as $unit) {

            foreach ($seasons as $season) {

                if (!$unit->hasChargeForSeason($season)) {

                    // Compose new season charge
                    $charge = new \OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnitCharge;
                    $charge->setAccommodationUnit($unit);
                    $charge->setSeason($season);

                    // Persist season charge
                    $entityManager->persist($charge);
                }
            }
        }

        $entityManager->flush();

        $front->flash('Die Konfiguration wurde übertragen.');

        return new \OmniTools\Core\View\ResponseRedirect($this->getUri('gridEditor'));
    }

    /**
     *
     */
    public function gridEditorAction(
        \OmniTools\Core\View\Front $front,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        // Fetch units
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
        $units = $accommodationUnitRepository->findBy([]);

        // Fetch seasons
        $seasonsRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Season::class);
        $seasons = $seasonsRepository->findBy([], ['dateFrom' => 'ASC']);

        return $this->render(null, [
            'units' => $units,
            'seasons' => $seasons
        ]);
    }

    /**
     *
     */
    public function indexAction(): Response
    {
        return $this->render();
    }
}