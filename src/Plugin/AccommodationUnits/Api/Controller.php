<?php
/**
 *
 */

namespace OmniTools\ApartmentsRental\Plugin\AccommodationUnits\Api;

class Controller extends \OmniTools\Core\Api\AbstractController
{
    /**
     *
     */
    public function bookingsActionUsingGet(
        \Doctrine\ORM\EntityManagerInterface $entityManager
    )
    {
        // Fetch unit
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
        $unit = $accommodationUnitRepository->find($this->getPayload('unitId'));

        $dateFrom = new \DateTime($this->getPayload('dateFrom'));
        $dateTo = new \DateTime($this->getPayload('dateTo'));

        // Fetch bookings
        $sql = 'SELECT
            id,
            accommodationunit_id,
            date_from,
            date_to
        FROM
            apartmentsrental_booking b
        WHERE
            accommodationunit_id = ' . $unit->getId() . ' AND
            b.state != "Cancelled" AND 
            b.type = "Booking" AND
            (
                b.date_from >= "' . $dateFrom->format('Y-m-d') . '" AND b.date_from <= "' . $dateTo->format('Y-m-d') . '" OR
                b.date_to >= "' . $dateFrom->format('Y-m-d') . '" AND b.date_to <= "' . $dateTo->format('Y-m-d') . '"
            )';

        $stmt = $entityManager->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result;
    }

    /**
     *
     */
    public function bookableUnitsByDateActionUsingGet(
        \Doctrine\ORM\EntityManagerInterface $entityManager
    )
    {
        $dateFrom = new \DateTime($this->getPayload('dateFrom'));
        $dateTo = new \DateTime($this->getPayload('dateTo'));

        $guests = $this->getPayloadOptional('guests');
        $dogs = $this->getPayloadOptional('dogs');

        // Fetch units
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
        $units = $accommodationUnitRepository->findBy([
            'bookableOnline' => true
        ]);

        $from = $dateFrom->format('Y-m-d');
        $to = $dateTo->format('Y-m-d');

        // Fetch bookings
        $sql = 'SELECT            
            accommodationunit_id as unitId
        FROM
            apartmentsrental_booking b
        WHERE
            type = "Booking" AND
            state != "Cancelled" AND
            (
                (                
                    /* Booking matches request exactly (E) */
                    /*         <---- B ---->               */
                    /*         <---- R ---->               */
                    b.date_from = "' . $from . '" OR
                    b.date_to = "' . $to . '"
                )
                OR
                (
                    /* Bookings lays completely inside request (F) */ 
                    /*         <---- B ---->                       */
                    /*      <------- R ------->                    */
                    b.date_from > "' . $from . '" AND
                    b.date_to < "' . $to . '"
                )
                OR
                (
                    /* Bookings lays completely inside request ( ) */ 
                    /*         <---- B ---->                       */
                    /*           <-- R -->                         */
                    b.date_from < "' . $from . '" AND
                    b.date_to > "' . $to . '"
                )
                OR
                (
                    /* End of request lays inside booking (G) */
                    /*         <---- B ---->                  */
                    /*      <--- R --->                       */                    
                    b.date_from > "' . $from . '" AND
                    b.date_from < "' . $to . '"
                )
                OR
                (
                    /* Start of request lays inside booking (H) */
                    /*         <---- B ---->                    */
                    /*               <--- R --->                */                    
                    b.date_to > "' . $from . '" AND
                    b.date_to < "' . $to . '"
                )
            ) ';

        $stmt = $entityManager->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        $list = [];

        $nights = $dateTo->diff($dateFrom)->format("%a");

        foreach ($units as $index => $unit) {

            if ($guests !== null and $unit->getMaxGuests() < $guests) {
                continue;
            }

            if ($dogs !== null and $unit->getMaxDogs() < $dogs) {
                continue;
            }

            foreach ($result as $booking) {
                if ($booking['unitId'] == $unit->getId()) {
                    continue 2;
                }
            }

            $list[] = [
                'id' => $unit->getId(),
                'title' => $unit->getTitle(),
                'maxGuests' => $unit->getMaxGuests(),
                'maxDogs' => $unit->getMaxDogs(),
                'total' => $unit->getPriceForDates($dateFrom, $dateTo, $guests, $dogs),
                'nights' => $nights
            ];
        }

        return $list;
    }

    /**
     *
     */
    public function detailsActionUsingGet(
        \Doctrine\ORM\EntityManagerInterface $entityManager
    )
    {
        // Fetch unit
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
        $unit = $accommodationUnitRepository->find($this->getPayload('unitId'));

        $data = [
            'id' => $unit->getId(),
            'title' => $unit->getTitle(),
            'maxGuests' => (int) $unit->getMaxGuests(),
            'maxToddlers' => (int) $unit->getMaxToddlers(),
            'maxDogs' => (int) $unit->getMaxDogs()
        ];

        return $data;
    }

    /**
     *
     */
    public function listActionUsingGet(
        \Doctrine\ORM\EntityManagerInterface $entityManager
    )
    {
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
        $result = $accommodationUnitRepository->findBy([]);

        $list = [];

        foreach ($result as $unit) {

            $list[] = [
                'id' => $unit->getId(),
                'title' => $unit->getTitle()
            ];
        }

        return $list;
    }
}
