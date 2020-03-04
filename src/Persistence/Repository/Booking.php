<?php
/**
 * $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
 */

namespace OmniTools\ApartmentsRental\Persistence\Repository;

use \Doctrine\Common\Collections\Criteria;

class Booking extends \OmniTools\Core\Persistence\AbstractRepository
{
    /**
     *
     */
    public function getBookingsIncomplete()
    {
        $criteria = new Criteria();
        $criteria->where($criteria->expr()->neq('state', 'Completed'));
        $criteria->orderBy(['dateFrom' => Criteria::ASC]);

        return $this->matching($criteria);
    }

    /**
     *
     */
    public function getBookingsComing()
    {
        $criteria = new Criteria();
        $criteria->where($criteria->expr()->gt('dateTo', new \DateTime()));
        // $criteria->andWhere($criteria->expr()->neq('state', 'Cancelled'));
        $criteria->orderBy(['dateFrom' => Criteria::ASC]);

        return $this->matching($criteria);
    }

    /**
     *
     */
    public function getBookingsForDate(\DateTime $date, \OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit $unit = null)
    {
        $criteria = new Criteria();
        $criteria->where($criteria->expr()->lte('dateFrom', $date));
        $criteria->andWhere($criteria->expr()->gte('dateTo', $date));

        if ($unit !== null) {
            $criteria->andWhere($criteria->expr()->eq('accommodationUnit', $unit));
        }


        return $this->matching($criteria);
    }

    /**
     *
     */
    public function getBookingsPast()
    {
        $criteria = new Criteria();

        $criteria->where($criteria->expr()->lt('dateFrom', new \DateTime()));

        $criteria->orderBy(['dateFrom' => Criteria::DESC]);

        return $this->matching($criteria);
    }

    /**
     *
     */
    public function hasBookingInDateRange(\DateTime $dateFrom, \DateTime $dateTo, \OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit $unit = null): bool
    {
        // Obtain entity manager
        $entityManager = $this->getEntityManager();

        $from = $dateFrom->format('Y-m-d');
        $to = $dateTo->format('Y-m-d');

        $sql = 'SELECT
            id, date_from, date_to
        FROM
            apartmentsrental_booking b
        WHERE
            b.type = "Booking" AND';

        if ($unit !== null) {
            $sql .= ' b.accommodationunit_id = ' . $unit->getId() . ' AND ';
        }

        $sql .= '
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
            )
        LIMIT 1';

        $stmt = $entityManager->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return count($result) > 0;
    }
}
