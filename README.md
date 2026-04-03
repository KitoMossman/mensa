# Mensa Administration

Ein webbasiertes Mensa-Verwaltungssystem, mit dem Essenspläne (Vollkost, Leichte Vollkost, Vegetarisch) und Abstimmungen für Wunschspeisen der Teilnehmer transparent, digital und in einem modernen, responsiven Design organisiert werden.

## Highlights der Modernisierung (2026)

Das System wurde grundlegend überarbeitet, um eine zeitgemäße Benutzererfahrung (UX) und ein hochwertiges Interface (UI) zu bieten:

1. **Premium Dark-Mode Design:** Ein konsistentes, dunkles Design mit Glassmorphismus-Effekten, optimierter Typografie (Inter) und hochwertigen UI-Komponenten (`modern-card`, `modern-btn`).
2. **Dynamisches Tab-System:** Umstellung von einer langen Scroll-Seite auf eine intuitive, Tab-basierte Navigation. Die Tabs werden via JavaScript gesteuert und der Status wird in der URL (`#hash`) für die Browser-Historie gespeichert.
3. **Optimiertes Layout:**
   * **Narrow Sidebar:** Eine platzsparende (100px) Seitenleiste für Desktop-Nutzer.
   * **Responsive Mobile Navbar:** Eine kompakte Top-Navigation für Smartphones.
   * **Screen-Fit:** Kompakte Tabellen und Karten sorgen dafür, dass der wöchentliche Speiseplan auf den meisten Bildschirmen ohne Scrollen sichtbar ist.
4. **Visuelles Farbleitsystem:** Die Speiseplan-Kategorien (Vollkost, Leichte Vollkost, Vegetarisch) sind farblich dezent hinterlegt, um die Lesbarkeit zu erhöhen.
5. **Unified Admin Hub:** Alle administrativen Funktionen (Nachrichten, Umfragen, Speisen-Datenbank, Pläne) wurden in einem einzigen, mächtigen Dashboard (`login.php`) konsolidiert.
6. **Barrierefreie Typografie:** Optimierte Kontrastwerte und leuchtende Akzente sorgen für eine hervorragende Lesbarkeit auf allen Hintergrund-Typen.

---

## Setup & Installation

1. **Datenbank**: Importiere die Dateistruktur via `mesa.sql` in einen MySQL/MariaDB Server.
   *(Hinweis: Das System führt automatische Struktur-Upgrades beim ersten Start durch).*
2. **Konfiguration**: Passe die Zugangsdaten in `config/config.php` an.
3. **Webserver**: Lade das Projekt in den `htdocs` Ordner deines Webservers (z. B. XAMPP oder MAMP) und rufe `index.php` auf.
4. **Admin-Login**: Der Zugang für das Personal erfolgt über den Tab **"LOGIN"** in der Sidebar. Nach erfolgreicher Anmeldung steht das zentrale **Dashboard** zur Verfügung.

---

## Technische Architektur & Sicherheit

Das Projekt setzt auf eine strikte Trennung von Geschäftslogik und Design sowie auf moderne Sicherheitsstandards.

### 1. Modulare Struktur

* **`config/config.php`**: Zentrale Datenbank-Konfiguration.
* **`includes/Database.php`**: PDO-basierte Datenbankverbindung (Singleton-Pattern).
* **`includes/functions.php`**: Zentrale Logik & Helper (XSS-Schutz via `h()`, intelligente Speisen-Formatierung via `formatMealName()`).
* **`templates/header.php` & `templates/footer.php`**: Zentrale Verwaltung der Seitenstruktur und Assets.
* **`login.php`**: Der konsolidierte Administrations-Hub (ersetzt separate Seiten für Nachrichten und Umfragen).
* **`style.css`**: Umfassendes Design-System, das auf `w3.css` aufbaut, dieses aber für den modernen Look vollständig überschreibt.

### 2. Sicherheit & Datenschutz

* **Passwort-Hashing**: Administrator-Passwörter sind mit dem robusten `Bcrypt`-Algorithmus gesichert.
* **SQL-Injection Schutz**: Konsequente Nutzung von **Prepared Statements** für alle Datenbankinteraktionen (PDO).
* **XSS-Schutz**: Sämtliche Benutzerausgaben werden konsequent über die `h()`-Funktion maskiert.
* **Anonymität**: Das Nachrichtensystem arbeitet ohne personenbezogene Daten; die Zuordnung erfolgt ausschließlich über Ticket-IDs und Geheimwörter.

### 3. Anonymes Ticketsystem (integriert)

Ein ticket-basiertes System ermöglicht die Kommunikation mit der Küche (z. B. für Sonderkost-Anfragen):

* Nutzer generieren beim Senden einer Nachricht eine **Ticket-ID** und ein Wort-basiertes **Geheimwort**.
* Die Küche beantwortet Anfragen direkt im Admin-Dashboard (`login.php#nachrichten`).
* Nutzer können Antworten anonym über den Tab **"ANTWORT"** in der `index.php` abrufen und eine einmalige Rückantwort senden.
* Sobald die Kommunikation abgeschlossen ist, kann die Küche das Ticket im Admin-Bereich vollständig löschen.
