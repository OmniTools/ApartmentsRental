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
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        Post $post,
        \OmniTools\Core\View\Front $front,
        Get $get
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
    public function indexAction(): Response
    {
        return $this->render();
    }
}