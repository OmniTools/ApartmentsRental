<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit;
use OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnitCharge;
use OmniTools\ApartmentsRental\Persistence\Entity\Season;

$cases = [
    ['Base price outside seasons', '2026-06-28', '2026-06-30', [], 200],
    ['Inside season', '2026-07-10', '2026-07-12', [['2026-07-01', '2026-07-31', 150]], 300],
    ['Cross season start', '2026-06-30', '2026-07-02', [['2026-07-01', '2026-07-31', 150]], 250],
    ['Cross season end', '2026-07-30', '2026-08-02', [['2026-07-01', '2026-07-31', 150]], 400],
    ['Last season night', '2026-07-31', '2026-08-01', [['2026-07-01', '2026-07-31', 150]], 150],
    ['Cheaper season', '2026-07-10', '2026-07-12', [['2026-07-01', '2026-07-31', 80]], 160],
    ['Zero season price', '2026-07-10', '2026-07-12', [['2026-07-01', '2026-07-31', 0]], 0],
    ['Adjacent seasons', '2026-07-31', '2026-08-02', [['2026-07-01', '2026-07-31', 150], ['2026-08-01', '2026-08-31', 120]], 270],
    ['Overlap below base', '2026-07-10', '2026-07-12', [['2026-07-01', '2026-07-31', 80], ['2026-07-10', '2026-07-20', 90]], 180],
    ['Overlap reversed', '2026-07-10', '2026-07-12', [['2026-07-10', '2026-07-20', 90], ['2026-07-01', '2026-07-31', 80]], 180],
    ['Departure excluded', '2026-06-30', '2026-07-01', [['2026-07-01', '2026-07-31', 150]], 100],
];

foreach ($cases as [$label, $from, $to, $seasons, $expected]) {
    $unit = new AccommodationUnit();
    $unit->setPrice(100);
    $unit->setAdditionalCosts(25);
    $unit->setPricePerDog(10);
    foreach ($seasons as [$start, $end, $rate]) {
        $season = new Season(['dateFrom' => new DateTime($start), 'dateTo' => new DateTime($end)]);
        $charge = new AccommodationUnitCharge();
        $charge->setSeason($season);
        $charge->setAccommodationUnit($unit);
        $charge->setPrice($rate);
        $unit->getCharges()->add($charge);
    }
    $arrival = new DateTime($from);
    $departure = new DateTime($to);
    $results = [
        'nights' => [$unit->getPriceForDateRange($arrival, $departure), $expected],
        'total' => [$unit->getPriceForDates($arrival, $departure, 2), $expected + 45],
        'segments' => [$unit->getPriceSegments($arrival, $departure, 2)['total'], $expected + 45],
    ];
    foreach ($results as $method => [$actual, $wanted]) {
        if (abs($actual - $wanted) > 0.00001) {
            throw new RuntimeException("$label ($method): expected $wanted, got $actual");
        }
    }
    if ($arrival->format('Y-m-d') !== $from || $departure->format('Y-m-d') !== $to) {
        throw new RuntimeException("$label: input dates mutated");
    }
}
echo count($cases) . " pricing cases passed.\n";
