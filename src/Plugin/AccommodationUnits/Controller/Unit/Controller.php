<?php
/**
 *
 */

namespace OmniTools\ApartmentsRental\Plugin\AccommodationUnits\Controller\Unit;

use Doctrine\ORM\EntityManagerInterface;
use \OmniTools\Core\Http\Get;
use \OmniTools\Core\Http\Post;
use OmniTools\Core\View;

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
        EntityManagerInterface $entityManager,
        Post $post,
        \OmniTools\Core\View\Front $front
    ): \OmniTools\Core\View\Response
    {
        // Validate required input
        $post->require([ 'title' ]);

        // Compose new unit
        $unit = new \OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit([
            'title' => $post->get('title')
        ]);

        // Persist unit
        $entityManager->persist($unit);
        $entityManager->flush();

        return new \OmniTools\Core\View\ResponseJson([
            'modalDismiss' => true,
            'replace' => [
                'selector' => '#unitsReceiver',
                'html' => $front->renderPartial('ApartmentsRental/Plugin/AccommodationUnits/Controller/Unit/Partial/ListUnits', [
                    'highlight' => $unit->getId()
                ])
            ]
        ]);
    }

    /**
     *
     */
    public function ajaxDeleteAction(
        Get $get,
        \OmniTools\Core\View\Front $front,
        EntityManagerInterface $entityManager
    ): \OmniTools\Core\View\Response
    {
        // Fetch unit
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
        $unit = $accommodationUnitRepository->find($get->get('unitId'));

        // Delete unit
        $entityManager->remove($unit);
        $entityManager->flush();

        $front->flash('Die Wohneinheit wurde gelöscht.');

        return new \OmniTools\Core\View\ResponseJson([
            'redirect' => $this->getUri('index', 'Dashboard')
        ]);
    }

    /**
     *
     */
    public function ajaxSeasonPriceCreateAction(
        Get $get,
        Post $post,
        \OmniTools\Core\View $view,
        \OmniTools\Core\View\Front $front,
        EntityManagerInterface $entityManager
    ): \OmniTools\Core\View\Response
    {
        // Fetch unit
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
        $unit = $accommodationUnitRepository->find($get->get('unitId'));

        // Fetch season
        $seasonsRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Season::class);
        $season = $seasonsRepository->find($post->get('seasonId'));

        // Compose new season charge
        $charge = new \OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnitCharge;
        $charge->setAccommodationUnit($unit);
        $charge->setSeason($season);

        // Persist season charge
        $entityManager->persist($charge);
        $entityManager->flush();

        return new \OmniTools\Core\View\ResponseJson([
            'modalDismiss' => true,
            'replace' => [
                'selector' => '#chargesReceiver',
                'html' => $front->renderPartial('ApartmentsRental/Plugin/AccommodationUnits/Controller/Unit/Partial/ListCharges', [
                    'controller' => $this,
                    'unitId' => $unit->getId(),
                    'highlight' => $charge->getId()
                ])
            ]
        ]);
    }

    /**
     *
     */
    public function ajaxSeasonChargeUpdateAction(
        Get $get,
        Post $post,
        EntityManagerInterface $entityManager
    ): \OmniTools\Core\View\Response
    {
        // Fetch charge
        $chargesRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnitCharge::class);
        $charge = $chargesRepository->find($get->get('chargeId'));

        $charge->setPrice($post->get('price'));

        $entityManager->flush();

        return new \OmniTools\Core\View\ResponseJson;
    }

    /**
     *
     */
    public function ajaxSeasonChargeDeleteAction(
        Get $get,
        \OmniTools\Core\View\Front $front,
        EntityManagerInterface $entityManager
    ): \OmniTools\Core\View\Response
    {
        // Fetch charge
        $chargesRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnitCharge::class);
        $charge = $chargesRepository->find($get->get('chargeId'));

        $unit = $charge->getAccommodationUnit();

        // Remove charge
        $entityManager->remove($charge);
        $entityManager->flush();

        return new \OmniTools\Core\View\ResponseJson([
            'replace' => [
                'selector' => '#chargesReceiver',
                'html' => $front->renderPartial('ApartmentsRental/Plugin/AccommodationUnits/Controller/Unit/Partial/ListCharges', [
                    'controller' => $this,
                    'unitId' => $unit->getId()
                ])
            ]
        ]);
    }

    /**
     *
     */
    public function ajaxUpdateAction(
        Get $get,
        Post $post,
        EntityManagerInterface $entityManager
    ): \OmniTools\Core\View\Response
    {
        // Fetch unit
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
        $unit = $accommodationUnitRepository->find($get->get('unitId'));

        $unit->setTitle($post->get('title'));

        $entityManager->flush();

        return new \OmniTools\Core\View\ResponseJson;
    }

    /**
     *
     */
    public function ajaxUpdateBookableAction(
        Get $get,
        Post $post,
        EntityManagerInterface $entityManager
    ): \OmniTools\Core\View\Response
    {
        // Fetch unit
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
        $unit = $accommodationUnitRepository->find($get->get('unitId'));

        $unit->setMaxGuests((int) $post->get('maxGuests'));
        $unit->setMaxDogs((int) $post->get('maxDogs') ?? 0);
        $unit->setMaxToddlers((int) $post->get('maxToddlers') ?? 0);
        $unit->setBookableOnline(!empty($post->get('bookableOnline')));

        $entityManager->flush();

        return new \OmniTools\Core\View\ResponseJson;
    }

    /**
     *
     */
    public function ajaxUpdatePriceAction(
        Get $get,
        Post $post,
        EntityManagerInterface $entityManager
    ): \OmniTools\Core\View\Response
    {
        // Fetch unit
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
        $unit = $accommodationUnitRepository->find($get->get('unitId'));

        $unit->setPrice($post->get('price'));
        $unit->setPricePerDog($post->get('pricePerDog'));
        $unit->setPriceCalculation($post->get('priceCalculation'));
        $unit->setAdditionalCosts($post->get('additionalCosts'));

        $entityManager->flush();

        return new \OmniTools\Core\View\ResponseJson;
    }

    /**
     *
     */
    public function ajaxUpdateTextsAction(
        Get $get,
        Post $post,
        EntityManagerInterface $entityManager
    )
    {
        // Fetch unit
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
        $unit = $accommodationUnitRepository->find($get->get('unitId'));


        foreach ($post->get('texts') as $key => $value) {

            if ($text = $unit->getTextByKey($key)) {

                if (!empty($value)) {
                    $text->setText($value);
                }
                else {
                    $entityManager->remove($text);
                }
            }
            elseif (!empty($value)) {

                // Compose new text
                $text = new \OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnitText([
                    'textKey' => $key,
                    'text' => $value
                ]);

                $unit->addText($text);
            }
        }

        $entityManager->flush();

        return new \OmniTools\Core\View\ResponseJson;
    }

    /**
     *
     */
    public function ajaxModalComposeAction(
        \OmniTools\Core\View $view
    ): \OmniTools\Core\View\Response
    {
        return new \OmniTools\Core\View\ResponseJson([
            'html' => $view->render('AjaxModalCompose.html.twig')
        ]);
    }

    /**
     *
     */
    public function ajaxModalCreateSeasonPriceAction(
        Get $get,
        \OmniTools\Core\View $view,
        EntityManagerInterface $entityManager
    ): \OmniTools\Core\View\Response
    {
        // Fetch unit
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
        $unit = $accommodationUnitRepository->find($get->get('unitId'));

        $view->assign('unit', $unit);

        // Fetch seasons
        $seasonsRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Season::class);
        $seasons = $seasonsRepository->findBy([], ['dateFrom' => 'ASC']);

        $view->assign('seasons', $seasons);

        return new \OmniTools\Core\View\ResponseJson([
            'html' => $view->render('AjaxModalSeasonPriceCompose.html.twig')
        ]);
    }

    /**
     *
     */
    public function detailsAction(
        Get $get,
        \OmniTools\Core\View $view,
        \OmniTools\Core\View\Front $front,
        EntityManagerInterface $entityManager
    ): \OmniTools\Core\View\Response
    {
        // Fetch unit
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
        $unit = $accommodationUnitRepository->find($get->get('unitId'));

        $view->assign('unit', $unit);

        return $this->render();
    }

    /**
     *
     */
    public function editAction(
        Get $get,
        \OmniTools\Core\View $view,
        \OmniTools\Core\View\Front $front,
        EntityManagerInterface $entityManager
    ): \OmniTools\Core\View\Response
    {
        // Fetch unit
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
        $unit = $accommodationUnitRepository->find($get->get('unitId'));

        $view->assign('unit', $unit);

        return $this->render();
    }

    /**
     *
     */
    public function indexAction()
    {
        return $this->render();
    }

    /**
     *
     */
    public function pricesAction(
        Get $get,
        \OmniTools\Core\View $view,
        \OmniTools\Core\View\Front $front,
        EntityManagerInterface $entityManager
    ): \OmniTools\Core\View\Response
    {
        // Fetch unit
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
        $unit = $accommodationUnitRepository->find($get->get('unitId'));

        $view->assign('unit', $unit);

        return $this->render();
    }
}
