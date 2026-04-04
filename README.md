# Mensa Administration System

Ein hochmodernes, webbasiertes Mensa-Verwaltungssystem, entwickelt für die effiziente Organisation von Speiseplänen, Abstimmungen und die Kommunikation zwischen Gästen und Küche. Das System besticht durch ein premium Dark-Mode Design, intuitive Bedienung und ein anonymes Ticketsystem.

## 🌟 Highlights der Modernisierung (2026)

Das System wurde grundlegend überarbeitet, um eine erstklassige Benutzererfahrung (UX) und ein zukunftssicheres Interface (UI) zu bieten:

1. **Premium Dark-Mode Design:** Ein konsistentes, tiefdunkles Design mit Glassmorphismus-Effekten, optimierter Typografie (**Inter**) und hochwertigen UI-Komponenten (`modern-card`, `modern-btn`).
2. **Dynamisches Tab-System:** Umstellung von einer langen Scroll-Seite auf eine intuitive, Tab-basierte Navigation. Der Status wird via JavaScript in der URL (`#hash`) synchronisiert, was die Nutzung der Browser-Historie ermöglicht.
3. **App-Like Mobile Experience ("Perfect Fit"):** Eine speziell für Smartphones (z.B. Pixel 8, iPhone 15) optimierte Vollbild-Ansicht. Edge-to-Edge Design ohne störende Ränder und Unterstützung für moderne "Notches" durch Safe-Area-Insets.
4. **Unified Admin Hub (`login.php`):** Alle administrativen Aufgaben wurden in einem zentralen Dashboard konsolidiert. Kein Hin- und Herspringen zwischen Dateien mehr nötig.
5. **Anonymes Ticketsystem:** Gäste können Feedback senden und Antworten der Küche über eine anonyme Ticket-ID und ein zufälliges Geheimwort abrufen – ohne Registrierung.
6. **Visuelles Farbleitsystem:** Die Speiseplan-Kategorien (Vollkost, Leichte Vollkost, Vegetarisch) sind farblich dezent hinterlegt, um die Orientierung auf einen Blick zu erleichtern.
7. **Responsive & Mobile First:** Das System ist vollständig adaptiv. Die kompakte Sidebar (100px) auf Desktop-Geräten verwandelt sich auf Mobilgeräten in eine platzsparende Top-Navbar.
8. **Barrierefreie Typografie:** Optimierte Kontrastwerte und leuchtende Akzente sorgen für eine hervorragende Lesbarkeit auf allen Hintergrund-Typen.
9. **Screen-Fit Design:** Kompakte Tabellen und Karten sorgen dafür, dass der wöchentliche Speiseplan auf den meisten Bildschirmen ohne Scrollen sichtbar ist.

---

## 🛠️ Administrations-Dashboard (Küche)

Das zentrale Dashboard (`login.php`) unterteilt sich in sieben spezialisierte Management-Bereiche:

* 📊 **Auswertung:** Echtzeit-Statistiken und grafische Aufbereitung (Fortschrittsbalken) der laufenden Abstimmungen.
* 🍴 **Speisen-Datenbank:** Zentrale Pflege aller verfügbaren Gerichte inkl. Kategorisierung.
* 📅 **Wochenplan:** Intuitive Erstellung des Speiseplans für die aktuelle/kommende Woche.
* 🗳️ **Wunsch-Wahl Prep:** Vorbereitung der Gerichte, die den Gästen zur Abstimmung gestellt werden.
* ✉️ **Posteingang:** Verwaltung des Ticketsystems, Beantwortung von Gästeanfragen und Feedback.
* 📈 **Umfragen:** Zeitliche Steuerung der Wunschspeisen-Wahl und Archivierung der Ergebnisse.
* ✳️ **Zusatzstoffe:** Verwaltung der Inhaltsstoffe und Allergene.

---

## 🚀 Setup & Installation

1. **Datenbank**: Importiere die `mesa.sql` in deine MySQL/MariaDB Datenbank.
2. **Konfiguration**: Hinterlege deine Zugangsdaten in `config/config.php`.
3. **Bereitstellung**: Lade das Projekt auf einen PHP-fähigen Webserver (PHP 8.0+ empfohlen).
4. **Admin-Zugang**: Der Login erfolgt über den Tab **"LOGIN"** in der Sidebar. Die Admin-Konten werden in der Tabelle `admins` verwaltet (Passwörter sind Bcrypt-gehasht).

---

## 🏗️ Technische Architektur

Das Projekt folgt modernen Web-Standards und Sicherheitsbest Practices:

### 📱 Frontend & Design

* **Vanilla CSS + Custom Components:** Ein eigens entwickeltes Design-System sorgt für maximale Performance ohne schwere Frameworks.
* **Font Awesome integration:** Klare Symbolsprache für eine intuitive Bedienung.
* **Inter Font Family:** Hochgradig lesbare Typografie für optimale UX.

### ⚙️ Backend & Sicherheit

* **PDO (PHP Data Objects):** Konsequente Nutzung von Prepared Statements zum Schutz vor SQL-Injection.
* **Modernes Session-Handling:** Sicherer Admin-Bereich mit dedizierter Authentifizierungsprüfung.
* **XSS-Prävention:** Zentralisierte Maskierung aller Benutzerausgaben via Helper-Funktion `h()`.
* **Singleton Pattern:** Effiziente Datenbankverbindung über eine zentrale Instanz (`Database.php`).

### 📂 Struktur-Übersicht

* `index.php`: Das Hauptportal für Gäste (Speiseplan, Wahl, Kontakt, Ticket-Abruf).
* `login.php`: Der konsolidierte Administrations-Hub für das Küchenpersonal.
* `includes/`: Kernlogik, Datenbank-Klasse und Hilfsfunktionen.
* `templates/`: Wiederverwendbare Header- und Footer-Komponenten.
* `style.css`: Das Herzstück des visuellen Designs und der Animationen.

---

## 🔐 Datenschutz

Das System ist "Privacy by Design" entwickelt. Das Ticketsystem arbeitet vollständig anonym. Es werden keine E-Mail-Adressen oder Klarnamen gespeichert, sofern die Nutzer diese nicht explizit in das Nachrichtenfeld schreiben. Die Zuordnung erfolgt rein über kryptografische Ticket-IDs.
