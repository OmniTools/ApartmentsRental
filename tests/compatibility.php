<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnit;
use OmniTools\ApartmentsRental\Persistence\Entity\AccommodationUnitCharge;
use OmniTools\ApartmentsRental\Persistence\Entity\Booking;
use OmniTools\ApartmentsRental\Persistence\Entity\Customer;
use OmniTools\ApartmentsRental\Persistence\Entity\Request;
use OmniTools\ApartmentsRental\Persistence\Entity\Season;

error_reporting(E_ALL);
set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});
function check(bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$config = Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration([
    __DIR__ . '/../src/Persistence/Entity',
    __DIR__ . '/../vendor/omnitools/core/src/Persistence/Entity',
    __DIR__ . '/../vendor/omnitools/addresses/src/Persistence/Entity',
], true);
$em = Doctrine\ORM\EntityManager::create(['driver' => 'pdo_sqlite', 'memory' => true], $config);
$metadata = $em->getMetadataFactory()->getAllMetadata();
(new Doctrine\ORM\Tools\SchemaTool($em))->createSchema($metadata);

$unit = new AccommodationUnit(['title' => 'Test apartment']);
$unit->setPrice('19.99');
$unit->setPricePerDog('5.25');
$unit->setAdditionalCosts('');
check($unit->getAdditionalCosts() === 0.0, 'Empty optional money input');
$unit->setAdditionalCosts('10.50');
$unit->setAdditionalCostsPerGuest('');
$unit->setAdditionalCostsPerGuestThreshold(null);
$unit->setMaxGuests(4);
$unit->setMaxDogs(2);
$unit->setMaxToddlers(1);
$unit->setBookableOnline(true);
try {
    $unit->setPrice('invalid');
    throw new LogicException('Invalid price accepted');
} catch (InvalidArgumentException $expected) {}

$season = new Season(['title' => 'Season', 'dateFrom' => new DateTime('2030-06-01'), 'dateTo' => new DateTime('2030-06-30')]);
$season->setMinNights(3);
$charge = new AccommodationUnitCharge();
$charge->setSeason($season);
$charge->setAccommodationUnit($unit);
$charge->setPrice('29.99');
$unit->getCharges()->add($charge);
check($unit->hasChargeForSeason($season), 'Season charge lookup');
check($unit->getMinNightsForDate(new DateTime('2030-06-10')) === 3, 'Season minimum nights');
check($unit->getMinNightsForDate(new DateTime('2030-07-10')) === 0, 'Outside season');
$customer = new Customer(['firstname' => 'Test', 'lastname' => 'Customer']);
$booking = new Booking(['persons' => 2]);
$booking->setAccommodationUnit($unit);
$booking->setCustomer($customer);
$booking->setDateFrom(new DateTime('2030-06-10'));
$booking->setDateTo(new DateTime('2030-06-13'));
foreach ([$unit, $season, $customer, $booking] as $entity) {
    $em->persist($entity);
}
$em->flush();
$id = $unit->getId();
$em->clear();
$unit = $em->find(AccommodationUnit::class, $id);
check($unit->getPrice() === 19.99 && $unit->getMaxToddlers() === 1, 'Persisted unit');
check($unit->getAdditionalCostsPerGuest() === 0.0, 'Persisted optional charge');
$repo = $em->getRepository(Booking::class);
check($repo->hasBookingInDateRange(new DateTime('2030-06-11'), new DateTime('2030-06-12'), $unit), 'Overlapping booking');
check(!$repo->hasBookingInDateRange(new DateTime('2030-07-11'), new DateTime('2030-07-12'), $unit), 'Available dates');
check(count($repo->getBookingsIncomplete()) === 1, 'ORM criteria');

$api = new OmniTools\ApartmentsRental\Plugin\AccommodationUnits\Api\Controller();
$api->setPayload(['unitId' => $id, 'dateFrom' => '2030-06-01', 'dateTo' => '2030-06-30']);
check(count($api->bookingsActionUsingGet($em)) === 1, 'Booking API query');
check($api->detailsActionUsingGet($em)['maxToddlers'] === 1, 'Unit detail API');
$api->setPayload(['dateFrom' => '2030-06-11', 'dateTo' => '2030-06-12']);
check($api->bookableUnitsByDateActionUsingGet($em) === [], 'Booked unit excluded');
$api->setPayload(['dateFrom' => '2030-07-01', 'dateTo' => '2030-07-03', 'guests' => 2, 'dogs' => 1]);
$available = $api->bookableUnitsByDateActionUsingGet($em);
check(count($available) === 1 && abs($available[0]['total'] - 55.73) < 0.001, 'Availability price uses dogs, not guests');
$costs = new OmniTools\ApartmentsRental\Plugin\Booking\Api\Controller();
$costs->setPayload(['unitId' => $id, 'dateFrom' => '2030-07-01', 'dateTo' => '2030-07-03', 'guests' => 2, 'dogs' => 1]);
check(abs($costs->costsActionUsingGet($em)['total'] - 55.73) < 0.001, 'Cost API agrees with availability');

// A view double avoids application filesystem/configuration dependencies.
$view = new class extends OmniTools\Core\View {
    public array $assigned = [];
    public function __construct() {}
    public function assign(string $varname, $value): void { $this->assigned[$varname] = $value; }
};
$_GET = ['date' => '2030-06-01'];
$dashboard = new OmniTools\ApartmentsRental\Plugin\Booking\Controller\Dashboard\Controller();
$dashboard->setAction('index');
for ($i = 0; $i < 2; $i++) {
    $dashboard->indexAction(new OmniTools\Core\Http\Get(), $view, $em);
}
check(count($view->assigned['grid']) === 1 && count($view->assigned['days']) === 84, 'Dashboard DBAL and repeated rendering');

$front = new class extends OmniTools\Core\View\Front {
    public function __construct() {}
    public function flash(string $message, $type = 'Success'): void {}
};
$logger = new class extends OmniTools\Core\LoggerService {
    public function __construct() {}
    public function log(OmniTools\Core\Persistence\AbstractEntity $subject, $action, array $context = null) {}
};
$request = new Request();
$request->setAccommodationUnit($unit);
$request->setDateFrom(new DateTime('2030-08-01'));
$request->setDateTo(new DateTime('2030-08-03'));
$em->persist($request);
$em->flush();
$requestId = $request->getId();
$_GET = ['requestId' => $requestId];
$bookingController = new OmniTools\ApartmentsRental\Plugin\Booking\Controller\Booking\Controller();
$bookingController->ajaxRequestConvertAction(new OmniTools\Core\Http\Get(), $front, $em, $logger);
check($em->getConnection()->fetchOne('SELECT type FROM apartmentsrental_booking WHERE id = ?', [$requestId]) === 'Booking', 'Request conversion via DBAL 3');

// All package templates must still parse with the resolved Twig version.
$twig = new Twig\Environment(new Twig\Loader\ArrayLoader());
$twig->addExtension(new Symfony\Bridge\Twig\Extension\TranslationExtension(new Symfony\Component\Translation\Translator('de')));
$twig->addFilter(new Twig\TwigFilter('truncate', static fn ($value) => $value));
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/../src')) as $file) {
    if ($file->isFile() && in_array($file->getExtension(), ['twig', 'html'], true)) {
        $twig->parse($twig->tokenize(new Twig\Source(file_get_contents($file->getPathname()), $file->getPathname())));
    }
}

$season = $em->getRepository(Season::class)->findOneBy(['title' => 'Season']);
check($unit->getChargeForSeason($season) instanceof AccommodationUnitCharge, 'Grid editor charge lookup');
$seasonsController = new OmniTools\ApartmentsRental\Plugin\Configurations\Controller\Seasons\Controller();
$seasonsController->generateAllChargesAction($front, $em);
check($em->getRepository(AccommodationUnitCharge::class)->count([]) === 1, 'Existing seasonal charge is not duplicated');

echo 'PHP ' . PHP_VERSION . ': ApartmentsRental compatibility checks passed.' . PHP_EOL;
