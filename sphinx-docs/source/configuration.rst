.. _configuration:

============= 
Konfiguration 
=============

Die Konfiguration des Modules befindet sich unter :menuselection:`System --> Konfiguration --> Zahlarten --> ratenkauf by easyCredit`.

API-Zugangsdaten konfigurieren
------------------------------

.. image:: ./_static/config-open.png
           :scale: 50%

* Aktivieren Sie die Zahlungsart.
* Tragen Sie die API-Zugangsdaten in die dafür vorgesehenen Felder API-Key und API-Token ein.
* Testen Sie die Zugangsdaten mit Klick auf **Zugangsdaten testen**.
* Nach dem erfolgreichen Test klicken Sie auf **Speichern**

Anrede konfigurieren
------------------------------

Zur Verarbeitung der Zahlung benötigt die Extension die korrekte Anrede des Kunden. 
Eine Anrede wird von Magento aber standardmäßig nicht erfasst.

Standardmäßig erfasst das Plugin die Anrede in der Zahlartenauswahl, falls nicht bereits vorhanden.
Diese Anrede kann zusätzlich durch Auswahl der Option **Anrede speichern** im Kunden-Account gespeichert werden.

Um die Anrede in Ihrem Shop global zu erfassen, aktivieren Sie die Anrede in Bestellvorgang und Kundenregistrierung. 
Stellen Sie hierzu unter :menuselection:`System -> Konfiguration -> Kunden Konfiguration` die Option **Prefix anzeigen** auf *Erforderlich*.
Zusätzlich stellen Sie die Option **Präfix Dropdown-Optionen** auf den Wert *Herr;Frau;*.

.. image:: ./_static/config-prefix.png
           :scale: 50%
