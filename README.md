# easyCredit Magento Extension

Das easyCredit Zahlungsmodul für Magento ermöglicht es Ihnen durch einfache Installation Ratenkauf by easyCredit in Ihrem Magento-Store anbieten zu können.
Weitere Informationen zu easyCredit finden Sie unter  [Ratenkauf by easyCredit](https://www.easycredit.de/Ratenkauf.htm)

## Installation

Die Verzeichnisstruktur entspricht der Magento-Verzeichnisstruktur. Die Installation kann durch Kopieren der Dateien in die entsprechende Struktur von Magento erfolgen. Alternativ kann die Installation über [modman](https://github.com/colinmollenhour/modman) erfolgen - ein entsprechendes modman-File liegt bei.

## Konfiguration

### Zahlarten-Einstellung

Die Zahlungsarten-Konfiguration befindet sich in unter *System -> Konfiguration -> Zahlungsarten -> easyCredit Ratenzahlung*

![Zahlarten-Konfiguration](var/images/config.png "Zahlarten-Konfiguration")

| Option                                        | Erklärung                                                                                                                                                                                                                                                                       |
|-----------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Title                                         | Unter dem Titel wird die Zahlungsmethode im Checkout angezeigt.                                                                                                                                                                                                                 |
| Status neuer Bestellungen                     | Ermöglicht es Ihnen den Status festzulegen den Bestellungen die mit Ratenkauf by easyCredit bezahlt wurden, nach dem Eingang im System aufweisen.                                                                                                                               |
| Zeige Modellrechner-Widget neben Produktpreis | Aktivieren Sie diese Option wenn Sie auf Produkt-Detail-Seiten ein monatliches Raten Angebot anzeigen möchten. Bitte beachten Sie das ein monaterlicher Ratenpreis nur angezeigt wird wenn der Preis des Produkts sich in der festgelegten Preisspanne für Ratenkäufe befindet. |
| Zahlung aus zutreffenden Ländern              | Stellen Sie diese Option bitte auf Bestimmte Länder (Specific Countries)                                                                                                                                                                                                       |
| Zahlung aus bestimmmten Ländern               | Wählen Sie hier als einziges Land Deutschland aus.                                                                                                                                                                                                                              |
| Debug Logging                                 | Erlaubt Ihnen festzulegen ob der Inhalt aller easyCredit API-Zugriffe in var/log/debug.log gespeichert werden soll. Fehlermitteillungen werden immer gespeichert.                                                                                                               |
| API Key                                       | Der API-Key wird Ihnen von der Teambank AG zur Verfügung gestellt.                                                                                                                                                                                                              |
| API Token                                     | Der nicht öffentliche API Token wird Ihnen von der Teambank AG zur Verfügung gestellt und sollte nicht mit Dritten geteilt werden.                                                                                                                                              |
| easyCredit Zugangsdaten überprüfen            | Ein Klick auf diesen Button überprüft die Kombination von API-Key und -Token auf Gültigkeit. Bitte vergessen Sie nicht nach einem erfolgreichen Test noch auf Speichern zu klicken.                                                                                             |

## Kompatibilität

Die Extension wurde unter Magento 1.8, Magento 1.9.2.2 & Magento 1.9.2.4 getestet. Weitere Systemvoraussetzungen sind mit den von Magento in der jeweiligen Vesion genannten abgedeckt.
