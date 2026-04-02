# Mensa Administration

Ein webbasiertes Mensa-Verwaltungssystem, mit dem Essenspläne (Vollkost, Leichte Vollkost, Vegetarisch) und Abstimmungen für Wunschspeisen der Teilnehmer transparent und digital organisiert werden. Inkludiert ein Dashboard zur Speisenverwaltung sowie ein anonymisiertes, ticket-basiertes Nachrichtensystem für die Kommunikation mit der Küche (z. B. für Absprachen zu Sonderkost).

## Setup & Installation

1. **Datenbank**: Importiere die Dateistruktur via `mesa.sql` in einen MySQL/MariaDB Server. 
*(Hinweis: Beim allerersten Aufruf der Applikation nach einem Deployment aktualisiert das Backend die Struktur der `nachrichten` Tabelle dynamisch, es bedarf keines händischen Eingriffs).*
2. **Konfiguration**: Öffne die Datei `config/config.php` und konfiguriere bei Bedarf den Benutzernamen, das Passwort und den Host der Datenbank.
3. **Webserver**: Lade das Projekt in den `htdocs` Ordner deines Webservers (z. B. XAMPP oder MAMP) und rufe `index.php` auf.
4. **Login**: Du kannst dich über die Sidebar per Klick auf "LOGIN" als `Administrator` oder `Kueche` in das Backend einloggen. 

---

## Aktuelle Systemarchitektur und Funktionen

Das ehemals monolithische Projekt wurde stark refactort. Der Code ist heute sicherer, modularer aufgebaut und der Fokus liegt auf strikter Trennung von Geschäftslogik und Design, ohne das bestehende *w3.css*-Design zu stören. Zudem wurde ein anonymes Ticketsystem zur Kontaktaufnahme implementiert.

### 1. Neue, modulare Ordnerstruktur
Der Code ist optimal organisiert und für zukünftige Entwicklungen (z.B. Nachrichtensysteme für Sonderkosten) vorbereitet:
- **`config/config.php`**: Enthält alle Zugangsdaten zur Datenbank (Host, Name, User, Passwort) zentral an einem Ort.
- **`includes/Database.php`**: Handhabt den Datenbankaufbau (über [PDO](https://www.php.net/manual/de/book.pdo.php)) mit Fehlerbehandlung via Expecton.
- **`includes/functions.php`**: Zentraler Ort für Helper-Funktionen. Hier befindet sich z.B. eine wichtige `h()` Funktion für Cross-Site-Scripting-Schutz (XSS) und zentrales Ausführen der PHP-Sessions.
- **`templates/header.php` & `templates/footer.php`**: Ermöglicht eine zentrale Verwaltung des HTML-Head-Bereichs sowie der Seitenstruktur (`w3-sidebar` & Header/Footer Logik) ohne Redundanzen im Code.

Altlasten wie z.B. `blockIndexBegin.php`, `blockAbstimmung.php`, `dewunschTabelle.php` und `conn.php` wurden bei der Umstrukturierung vollständig gelöscht.

> Zukünftige Module (inklusive Admin-Features) können nun einfach durch Erweitern der globalen `templates/header.php` Navigation und Einfügen der Skripte in den Hauptordner geschehen, anstatt das Grund-HTML mehrfach per Copy-Paste zu duplizieren.

### 2. Sicherheit (Passwords & Hashing)
- **`mesa.sql` Update**: Die Passwörter für `Administrator` und `Kueche` liegen nicht mehr im Klartext vor, sondern sind in `mesa.sql` via robustem `Bcrypt` Algorithmus als gesalzene Hashes hinterlegt.
- **`login.php` Update**: Das Vergleichen der Administrator-Eingabe läuft ab sofort sicher gegen den gespeicherten Hash des Benutzers (`password_verify()`). 

### 3. Sicherheit (SQL Injections blockiert)
Die wohl wichtigste Änderung: Jegliche Übernahmen von Benutzereingaben (`$_POST`-Variablen wie beim Suchen, Abstimmen oder Ändern einer Speise) in SQL-Anweisungen wurden umgearbeitet. Statt unsicheren direkten Querabfragen (`$conn->query("UPDATE ... " . $_POST['wert'])`) werden nun sogenannte **Prepared Statements** verwendet. Somit unterscheidet die Datenbank strikt zwischen dem Befehl selbst und der Eingabe und Angreifer können das System nicht manipulieren oder löschen. 

Gefixt in: `login.php`, `index.php`, `wunschTabelle.php`, `umfrage.php`, `nachrichten.php` und der alten `blockAbstimmung.php`.

### 4. Anonymes Ticketsystem für Mitteilungen / Sonderkost
Das Kontaktformular wurde zu einem automatisierten Ticket-System ausgebaut! Wer eine Info benötigt, hinterlässt keine Namen oder Accounts:
- **Teilnehmer stellt Anfrage (`index.php`):** Im Kontaktformular existiert bei Abgabe die Option *"Ich möchte eine Antwort der Küche (ein Abruf-Code wird generiert)"*. Nach dem Absenden generiert die Applikation automatisch eine alphanumerische **Ticket-ID** (z.B. **4BF72A**) sowie auslesbare, anwenderfreundliche Zufallspasswörter (ein echtes Wort plus Nummern, z.B. *"Tomate82"*). Parallel wird im Hintergrund der sekundengenaue Erstellungs-Zeitstempel in der Datenbank hinterlegt.
- **Küche antwortet (`nachrichten.php`):** In der Admin-Ansicht ist nebst Erstellungsdatum der Ticket-Verlauf sichtbar. Die Küche findet ein vorgefertigtes Antwortfeld für jedes Ticket. Sieht das Küchen-Personal, dass der Nutzer die Nachricht abgerufen hat, erscheint gezielt ein *Löschen*-Button, um das spezifische Ticket sauber aus der Datenbank zu entfernen.
- **Teilnehmer liest (`abrufen.php`):** Über die Seite können Teilnehmer ihre *Ticket-ID* und ihr Geheimwort eintragen. Liegt bereits eine Küchen-Antwort vor, wird die Datenbank in dieser Sekunde ein "Gelesen am"-Datum (`abgerufen_am`) speichern.
- **Nutzer Rückantwort:** Direkt nach dem Lesen der ersten Küchenantwort hat der Anwender auf der `abrufen.php` Seite einmalig die Option "Auf Antwort reagieren", um eine abschließende Rückantwort an die Küche (z. B. "Alles klar, vielen Dank!") zu senden. Diese wird für die Küche gebündelt in der Admin-Maske dargestellt.
