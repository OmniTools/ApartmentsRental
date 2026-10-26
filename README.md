# OmniTools ApartmentsRental

## PHP 8.3 / Core 0.1

- Benötigt PHP `^8.3` und `omnitools/core: ^0.1` (0.1.x).
- Composer-Zielplattform: `8.3.36`. Auch die Root-Anwendung muss diese
  Plattform konfigurieren; die Konfiguration einer Abhängigkeit wird nicht geerbt.
- DBAL-Leseabfragen nutzen `executeQuery()->fetchAllAssociative()`,
  die Umwandlung einer Anfrage in eine Buchung nutzt `executeStatement()`.
- PSR-4-Autoloading verwendet den relativen Pfad `src/`.

Die im UI bereits verwendeten Felder `maxToddlers`, `additionalCostsPerGuest`
und `additionalCostsPerGuestThreshold` sind jetzt im Wohnungsmodell vorhanden.
Vor dem Deployment prüfen, dass die bestehenden Anwendungsmigrationen
`Version20200305023923` und `Version20200305063124` ausgeführt wurden.
Die Änderung führt keine Datenbankmigration automatisch aus.

Leere optionale Preisfelder werden als null Euro behandelt. Preise werden
auf ganze Cent gerundet und als Integer gespeichert; ungültige numerische
Eingaben lösen eine verständliche Exception aus. Die Preis-API übergibt die
Hundeanzahl korrekt. Die vorhandene Tarifberechnung bleibt ansonsten erhalten:
Insbesondere werden die nun editierbaren Aufpreise pro Gast weiterhin nicht
in die Gesamtsumme eingerechnet; deren fachliche Berechnungsregel ist ein
separater Arbeitsschritt.

## Prüfen

```sh
composer install --no-scripts
composer test
composer validate --strict
composer check-platform-reqs
```

Tests benötigen `pdo_sqlite`. Sie prüfen Entity-Mapping mit Addresses,
Persistenz, Preise, Verfügbarkeit, DBAL-Lese- und Schreibzugriffe,
Dashboard-Aufrufe, Saisonzuordnung und Twig-Syntax. Daten werden ausschließlich
in SQLite im Speicher geschrieben. MySQL und SMTP werden nicht kontaktiert.

Für die lokale Prüfung wurde der aktuelle Core-Quellstand in einem separaten
Testprojekt als Version `0.1.0` eingebunden, zusammen mit Addresses `0.0.6`.
Das veröffentlicht keinen Core-Tag. Vor dem regulären Composer-Update muss
Core 0.1.x erreichbar sein und ApartmentsRental als neue Version veröffentlicht
werden. Anschließend die vollständige Anwendung mit ihrer Datenbank und dem
Mailversand testen und ihre getestete `composer.lock` deployen.
