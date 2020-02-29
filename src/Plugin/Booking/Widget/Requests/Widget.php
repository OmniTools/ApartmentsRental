<?php
/**
 *
 */

namespace OmniTools\ApartmentsRental\Plugin\Booking\Widget\Requests;

class Widget extends \OmniTools\Core\View\AbstractWidget
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
    ): array
    {
        // Fetch requests
        $requestRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Request::class);
        $requests = $requestRepository->findBy([
            'state' => 'Created'
        ], [], 5);

        return [
            'requests' => $requests
        ];
    }
}
