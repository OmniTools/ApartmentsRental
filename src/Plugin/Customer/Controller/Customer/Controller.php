<?php
/**
 *
 */

namespace OmniTools\ApartmentsRental\Plugin\Customer\Controller\Customer;

use OmniTools\Core\Http\Get;
use OmniTools\Core\Http\Post;
use OmniTools\Core\View\Front;
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
        \OmniTools\Core\Http\Post $post,
        \OmniTools\Core\View\Front $front,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        // Validate required input
        $post->require([ 'customer' => [
            'lastname'
        ]]);

        // Compose new customer
        $customer = new \OmniTools\ApartmentsRental\Persistence\Entity\Customer($post->get('customer'));

        // Persist unit
        $entityManager->persist($customer);
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
    public function ajaxCreateContactAction(
        \OmniTools\Core\Http\Get $get,
        \OmniTools\Core\Http\Post $post,
        \OmniTools\Core\View\Front $front,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        // Fetch customer
        $customersRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Customer::class);
        $customer = $customersRepository->find($get->get('customerId'));

        $contactClass = '\\OmniTools\\Addresses\\Persistence\\Entity\\PersonContact' . $post->get('contactType');
        $contact = new $contactClass([
            'value' => $post->get('value')
        ]);

        $customer->addContact($contact);

        $entityManager->flush();

        return new \OmniTools\Core\View\ResponseJson([
            'modalDismiss' => true,
            'replace' => [
                'selector' => '#contactsReceiver',
                'html' => $front->renderPartial('ApartmentsRental/Plugin/Customer/Controller/Customer/Partial/ListContacts', [
                    'controller' => $this,
                    'customer' => $customer,
                    'highlight' => $contact->getId()
                ])
            ]
        ]);
    }

    /**
     *
     */
    public function ajaxInternalNoteUpdateAction(
        \OmniTools\Core\Http\Get $get,
        \OmniTools\Core\Http\Post $post,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        // Fetch customer
        $customersRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Customer::class);
        $customer = $customersRepository->find($get->get('customerId'));

        $customer->setNoteInternal($post->get('noteInternal'));

        $entityManager->flush();

        return new \OmniTools\Core\View\ResponseJson;
    }

    /**
     *
     */
    public function ajaxUpdateAction(
        \OmniTools\Core\Http\Get $get,
        \OmniTools\Core\Http\Post $post,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        // Fetch customer
        $customersRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Customer::class);
        $customer = $customersRepository->find($get->get('customerId'));

        $customer->setFromArray($post->get('customer'));

        $entityManager->flush();

        return new \OmniTools\Core\View\ResponseJson([
            'modalDismiss' => true
        ]);
    }

    /**
     *
     */
    public function ajaxModalComposeAction(
        \OmniTools\Core\View $view,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        return new \OmniTools\Core\View\ResponseJson([
            'html' => $view->render('AjaxModalCompose.html.twig')
        ]);
    }

    /**
     *
     */
    public function ajaxModalContactComposeAction(
        \OmniTools\Core\View $view,
        \OmniTools\Core\Http\Get $get,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        // Fetch customer
        $customersRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Customer::class);
        $customer = $customersRepository->find($get->get('customerId'));

        return new \OmniTools\Core\View\ResponseJson([
            'html' => $view->render('AjaxModalContactCompose.html.twig', [
                'customer' => $customer
            ])
        ]);
    }

    /**
     *
     */
    public function ajaxModalEditAction(
        \OmniTools\Core\View $view,
        \OmniTools\Core\Http\Get $get,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        // Fetch customer
        $customersRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Customer::class);
        $customer = $customersRepository->find($get->get('customerId'));

        return new \OmniTools\Core\View\ResponseJson([
            'html' => $view->render('AjaxModalEdit.html.twig', [
                'customer' => $customer
            ])
        ]);
    }

    /**
     *
     */
    public function detailsAction(
        \OmniTools\Core\Http\Get $get,
        \OmniTools\Core\View $view,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        // Fetch customer
        $customersRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Customer::class);
        $customer = $customersRepository->find($get->get('customerId'));

        $view->assign('customer', $customer);

        // Fetch booking
        $bookingRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Booking::class);
        $bookings = $bookingRepository->findBy([
            'customer' => $customer
        ]);

        return $this->render(null, [
            'bookings' => $bookings
        ]);
    }

    /**
     *
     */
    public function indexAction(
        \OmniTools\Core\View $view,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {
        // Fetch customers
        $customersRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Customer::class);
        $customers = $customersRepository->findBy([]);

        $view->assign('customers', $customers);

        return $this->render();
    }
}