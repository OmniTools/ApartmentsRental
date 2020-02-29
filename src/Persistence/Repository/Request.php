<?php
/**
 * $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
 */

namespace OmniTools\ApartmentsRental\Persistence\Repository;

class Request extends \OmniTools\Core\Persistence\AbstractRepository
{
    /**
     *
     */
    public function isRequestConvertable(\OmniTools\ApartmentsRental\Persistence\Entity\Request $request): bool
    {
        $bookingRepository = $this->getEntityManager()->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Booking::class);

        return !$bookingRepository->hasBookingInDateRange($request->getDateFrom(), $request->getDateTo(), $request->getAccommodationUnit());
    }
}
