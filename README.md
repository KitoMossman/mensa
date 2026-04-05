# Mensa Administration System

Ein hochmodernes, webbasiertes Mensa-Verwaltungssystem, entwickelt für die effiziente Organisation von Speiseplänen, Abstimmungen und die Kommunikation zwischen Gästen und Küche. Das System besticht durch ein premium Dark-Mode Design, intuitive Bedienung und ein anonymes Ticketsystem.

## 🌟 Highlights der Modernisierung (2026)

Das System wurde grundlegend überarbeitet, um eine erstklassige Benutzererfahrung (UX) und ein zukunftssicheres Interface (UI) zu bieten:

1. **Premium Dark-Mode Design:** Ein konsistentes, tiefdunkles Design mit Glassmorphismus-Effekten, optimierter Typografie (**Inter**) und hochwertigen UI-Komponenten (`modern-card`, `modern-btn`).
2. **Dynamisches Tab-System:** Umstellung von einer langen Scroll-Seite auf eine intuitive, Tab-basierte Navigation inkl. URL-Hash-Synchronisierung.
3. **App-Like Mobile Experience ("Perfect Fit"):** Für Smartphones optimierte Vollbild-Ansicht mit Safe-Area-Support.
4. **Unified Admin Hub (`login.php`):** Alle administrativen Aufgaben wurden in einem zentralen Dashboard konsolidiert.
5. **Flexible Custom Surveys ("FRAGEN"):** Die Küche kann nun eigenständig Umfragen (Single- & Multiple-Choice) erstellen – ideal für Feedback jeglicher Art.
6. **Echtzeit-Ergebnisse:** Teilnehmer sehen nach der Stimmabgabe sofort die aktuellen Ergebnisse in grafischen Balkendiagrammen.
7. **Anonymes Ticketsystem:** Gäste können Feedback senden und Antworten der Küche über eine Ticket-ID abrufen – ohne Registrierung.
8. **Visuelles Farbleitsystem:** Kategorien sind farblich hinterlegt. Aktive Umfragen fallen durch einen rot blinkenden Button sofort ins Auge.
9. **Responsive Design:** Das System ist vollständig adaptiv und für alle Endgeräte optimiert.
10. **Barrierefreie Typografie:** Optimierte Kontrastwerte und leuchtende Akzente sorgen für hervorragende Lesbarkeit.
11. **Sicherheit:** Konsequente Nutzung von Prepared Statements und moderner Session-Verschlüsselung.
12. **Screen-Fit Layout:** Kompakte Darstellungen sorgen dafür, dass wichtige Infos oft ohne Scrollen sichtbar sind.

---

## 🛠️ Administrations-Dashboard (Küche)

Das zentrale Dashboard (`login.php`) unterteilt sich in sieben spezialisierte Management-Bereiche:

* 📊 **Auswertung:** Echtzeit-Statistiken und grafische Aufbereitung (Fortschrittsbalken) der laufenden Abstimmungen.
* 🍴 **Speisen-Datenbank:** Zentrale Pflege aller verfügbaren Gerichte inkl. Kategorisierung.
* 📅 **Wochenplan:** Intuitive Erstellung des Speiseplans für die aktuelle/kommende Woche.
* 🗳️ **Wunsch-Wahl Prep:** Vorbereitung der Gerichte für die Nutzer-Abstimmung.
* ✉️ **Posteingang:** Verwaltung des Ticketsystems und Beantwortung von Gäste-Feedback.
* 📈 **Umfragen:** Zeitliche Steuerung der Wunschspeisen-Wahl (Vollkost/Vegi/Leicht).
* ❓ **Fragen:** Eigenständiger Editor für freie Single- und Multiple-Choice Umfragen inkl. Live-Tracking.
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
* **Modernes Session-Handling:** Sicherer Admin-Bereich und Schutz vor Mehrfach-Abstimmungen bei Umfragen.
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
