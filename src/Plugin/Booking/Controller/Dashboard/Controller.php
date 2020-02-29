<?php
/**
 *
 */

namespace OmniTools\ApartmentsRental\Plugin\Booking\Controller\Dashboard;

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
    public function indexAction(
        \OmniTools\Core\Http\Get $get,
        \OmniTools\Core\View $view,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response
    {

        // Fetch units
        $accommodationUnitRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit::class);
        $units = $accommodationUnitRepository->findBy([]);

        $view->assign('units', $units);

        // Generate days
        if ($date = $get->get('date')) {
            $date = new \DateTime($date);

            if ($dir = $get->get('dir')) {

                if ($dir == 'up') {
                    $date->modify('+12 weeks');
                }
                else {
                    $date->modify('-12 weeks');
                }
            }
        }
        else {
            $date = new \DateTime();
            $week = (int) $date->format('W');
            $offset = (int) $date->format('N') - 1;
            $date->modify('-' . $offset . ' days');
        }

        $from = clone $date;

        $view->assign('dateFrom', $date->format('Y-m-d'));

        $days = [];

        for ($i = 0; $i < (12 * 7); ++$i) {

            $days[] = $date->format('Y-m-d');

            $date->modify('+1 day');
        }



        // Fetch bookings
        $sql = 'SELECT
            id, accommodationunit_id, date_from, date_to
        FROM
            apartmentsrental_booking b
        WHERE
            type = "Booking" AND
            (
                (                
                    /* Booking matches request exactly (E) */
                    /*         <---- B ---->               */
                    /*         <---- R ---->               */
                    b.date_from = "' . $from->format('Y-m-d') . '" OR
                    b.date_to = "' . $date->format('Y-m-d') . '"
                )
                OR
                (
                    /* Bookings lays completely inside request (F) */ 
                    /*         <---- B ---->                       */
                    /*      <------- R ------->                    */
                    b.date_from > "' . $from->format('Y-m-d') . '" AND
                    b.date_to < "' . $date->format('Y-m-d') . '"
                )
                OR
                (
                    /* Bookings lays completely inside request ( ) */ 
                    /*         <---- B ---->                       */
                    /*           <-- R -->                         */
                    b.date_from < "' . $from->format('Y-m-d') . '" AND
                    b.date_to > "' . $date->format('Y-m-d') . '"
                )
                OR
                (
                    /* End of request lays inside booking (G) */
                    /*         <---- B ---->                  */
                    /*      <--- R --->                       */                    
                    b.date_from > "' . $from->format('Y-m-d') . '" AND
                    b.date_from < "' . $date->format('Y-m-d') . '"
                )
                OR
                (
                    /* Start of request lays inside booking (H) */
                    /*         <---- B ---->                    */
                    /*               <--- R --->                */                    
                    b.date_to > "' . $from->format('Y-m-d') . '" AND
                    b.date_to < "' . $date->format('Y-m-d') . '"
                )
            )
            /*
            (
                b.date_from > "' . $from->format('Y-m-d') . '" AND b.date_from < "' . $date->format('Y-m-d') . '" OR
                b.date_to > "' . $from->format('Y-m-d') . '" AND b.date_to < "' . $date->format('Y-m-d') . '"
            )
            */
            ';

        $stmt = $entityManager->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        // Set up grid
        $grid = [];

        function generateState ($date, $unitId, $result) {

            $date = new \DateTime($date);
            $state = 'free';

            foreach ($result as $record) {

                if ($record['accommodationunit_id'] != $unitId) {
                    continue;
                }

                $from = new \DateTime($record['date_from']);
                $to = new \DateTime($record['date_to']);

                if ($date == $from) {

                    if ($state == 'free') {
                        $state = 'first';
                    }
                    else {
                        $state = 'both';
                    }
                }


                if ($date == $to) {
                    if ($state == 'free') {
                        $state = 'last';
                    }
                    else {
                        $state = 'both';
                    }
                }

                if ($date > $from and $date < $to) {
                    $state = 'booked';
                }
            }

            return $state;
        }

        foreach ($units as $unit) {

            $xdays = [];

            foreach ($days as $date) {

                $xdays[] = [
                    'date' => $date,
                    'state' => generateState($date, $unit->getId(), $result)
                ];
            }

            $grid[] = [
                'unit' => $unit,
                'days' => $xdays
            ];
        }

        $view->assign('grid', $grid);
        $view->assign('days', $days);
        $view->assign('thisday', date('Y-m-d'));

        $view->assign('dateTo', $date);

        // Fetch incomplete bookings
        $bookingRepository = $entityManager->getRepository(\OmniTools\ApartmentsRental\Persistence\Entity\Booking::class);
        $result = $bookingRepository->getBookingsIncomplete();

        $view->assign('bookingsIncomplete', $result);

        return $this->render();
    }
}
