Changelog
=========

v1.4.3
------

* Änderungen zum Markenrelaunch von easyCredit-Ratenkauf

v1.4.2
------

* bei Verwendung von OpenMage wird nun als System "OpenMage" zusammen mit der OpenMage Version gesendet
* die Finanzierung ist nun auch ohne Zinsen möglich
* kleinere textliche Änderungen
* die PHP-SDK wurde auf v1.6.2 aktualisiert (Änderungen zu PHP 8-Kompatiblität & 0% Finanzierung)

v1.4.1
------

* Vor- und Nachname werden nun in die Adressüberprüfung einbezogen
* die Ratenanzahl wird nun nicht mehr statisch übergeben
* die API-Library wurde auf v1.6 aktualisiert

v1.4.0
------

* eine Versandart kann für "Click & Collect" definiert werden
* die Konfiguration wurde übersichtlicher strukturiert und um Verlinkungen erweitert
* die API-Library wurde aktualisiert und wird nun über Composer eingebunden

v1.3.3
------

* die Zinsen können nun nach Bestellabschluss automatisch entfernt werden, Option standardmäßig aktiv

v1.3.2
------

* Behebung eines Fehlers in Zusammenspiel mit anderen Erweiterungen ("trigger_recollect" führte zu Endlossschleife)
* die Reihenfolge der Zahlungsart ist nun einstellbar
* die Bestätigungsseite hat nun einen Seitentitel

v1.3.1
------

* der Form Key wird nun über JavaScript übergeben (Änderung in Magento 1.9.4.5)

v1.3.0
------

* Verwendung der ratenkauf API v2 (ausgenommen "restbetragankaufobergrenze")
* Entfernung von Tilgungsplan & vorvertraglichen Informationen
* Rechnungen und Gutschriften werden nun korrekt erstellt und gutgeschrieben
* Integration des neuen Merchant-Interfaces
* Integration von Backend-Prozessen (Rechnung, Lieferschein)
* die Zahlung wird nun von Magento als "Authorisiert" betrachtet, erst die Lieferung stellt das "Capture" dar.
* Kompatibilität bis Magento v1.9.4.5

v1.2.3
------

* das Widget für Ratenkauf wird bis 10.000 EUR angezeigt
* Kompatibilität mit PHP 7.3
* Behebung kleinerer NOTICE- und Strict-Fehler

v1.2.2
------

* Verbesserung der Kompatibilität mit Mirasvit RewardPoints
* JS- und CSS-Dateien werden zusammengefügt und minified für bessere Frontend-Performance
* Artikel ohne Preis werden nicht an API übergeben

v1.2.1
------

* Anpassungen zur Kompatibilität mit älteren PHP-Versionen

v1.2.0
------

* behebt einen Fehler, der dazu führte, dass PayPal Express Checkout in bestimmten Fällen nicht mehr nutzbar war
* Einstellungsoption ob Zahlungsart auch angezeigt wird, wenn nicht verfügbar (nützlich bei Firecheckout)
* Übersetzung der Einstellungsoptionen

v1.1.1
------

* behebt einen Fehler, der dazu führt, dass das Plugin nicht angezeigt wurde
* Upgrade der API-Library

v1.1.0
------

* Upgrade der API auf Version 1.0
* verbessertes Fehlerhandling für Entwickler
* verbessertes Fehlerhandling für Benutzer
* Anpassung der Betragsgrenze auf einen Maximalbetrag von 5000 EUR
* API-Integration über gemeinsame PHP Library für alle Plugins
* Verbesserung der Kompatibilität
* getestet mit Magento 1.9.3.4 (weitere Tests folgen)
* Kompatibilität mit Magento Marketplace
* bei Angabe eines Firmennamens ist eine Zahlung mit Ratenkauf nicht mehr möglich
* Zahlungsart wird ausgegraut, wenn nicht verfügbar (Adresse stimmt nicht überein, Firmenname, Kunde ungleich Rechnungsadresse)
* Ratenkauf Widget wird nun mitgeliefert und in nachgeladenem Bootstrap-Modal angezeigt
* Kompatibilität mit reinem PrototypeJS aufgegeben (jQuery ist nun notwendig)
* Gestaltung des Ratenkauf-Widgets angepasst
* das Ratenkauf-Widget passt sich bei konfigurierbaren Produkten an
* das Ratenkauf-Widget berücksichtigt Sonderpreise
* Gestaltung der Review-Seite, Darstellung Zahlungsart angepasst
* obsolete Datenbankeinträge werden automatisch entfernt (easycredit_risk)

v1.0.0
------

* erstes öffentliches Release
