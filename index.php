<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/Database.php';

initSession();
$pdo = Database::getInstance()->getConnection();

// --- AUTOMATIC DB UPGRADE (TEMPORARY) ---
try {
    $stmtUpgrade = $pdo->query("SHOW COLUMNS FROM nachrichten LIKE 'ticket_id'");
    if ($stmtUpgrade->rowCount() == 0) {
        $pdo->exec("ALTER TABLE nachrichten 
                    ADD COLUMN ticket_id VARCHAR(20) NULL, 
                    ADD COLUMN geheimwort_hash VARCHAR(255) NULL, 
                    ADD COLUMN antwort_gewuenscht TINYINT(1) DEFAULT 0, 
                    ADD COLUMN antwort TEXT NULL");
    }
    
    $stmtUpgrade2 = $pdo->query("SHOW COLUMNS FROM nachrichten LIKE 'erstellt_am'");
    if ($stmtUpgrade2->rowCount() == 0) {
        $pdo->exec("ALTER TABLE nachrichten 
                    ADD COLUMN erstellt_am DATETIME DEFAULT CURRENT_TIMESTAMP, 
                    ADD COLUMN abgerufen_am DATETIME NULL, 
                    ADD COLUMN nutzer_rueckantwort TEXT NULL");
    }

    // --- CUSTOM SURVEY MIGRATION ---
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS custom_surveys (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            is_active TINYINT(1) DEFAULT 0,
            beginn DATE,
            ende DATE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS survey_questions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            survey_id INT NOT NULL,
            question_text TEXT NOT NULL,
            type ENUM('radio', 'checkbox') DEFAULT 'radio',
            FOREIGN KEY (survey_id) REFERENCES custom_surveys(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS survey_options (
            id INT AUTO_INCREMENT PRIMARY KEY,
            question_id INT NOT NULL,
            option_text TEXT NOT NULL,
            votes INT DEFAULT 0,
            FOREIGN KEY (question_id) REFERENCES survey_questions(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
} catch (Exception $e) {}
// ----------------------------------------

// Handle Logout
if (isset($_POST['logout'])) {
    session_destroy();
    initSession();
}

// Handle Contact Form
$contactMessage = "";
$contactIsError = false;

if (isset($_POST['thema']) && isset($_POST['nachricht'])) {
    $absender = $_POST['name'] ?? '';
    // Optional Answer functionality
    $antwort_gewuenscht = isset($_POST['antwort_gewuenscht']) ? 1 : 0;
    
    $ticketId = null;
    $geheimwortHash = null;
    $geheimwortPlain = null;

    if ($antwort_gewuenscht) {
        $ticketId = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6)); // e.g. 8A2F1C
        
        $wordList = ["Apfel", "Banane", "Croissant", "Gabel", "Holunder", "Kaffee", "Limonade", "Melone", "Nudel", "Orange", "Paprika", "Quark", "Salat", "Tomate", "Vanille", "Zitrone", "Teller", "Suppe"];
        $geheimwortPlain = $wordList[array_rand($wordList)] . rand(10, 99);
        
        $geheimwortHash = password_hash($geheimwortPlain, PASSWORD_BCRYPT);
    }
    
    $stmt = $pdo->prepare("INSERT INTO nachrichten (absender, thema, nachricht, datum, ticket_id, geheimwort_hash, antwort_gewuenscht) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$absender, $_POST['thema'], $_POST['nachricht'], date('Y-m-d'), $ticketId, $geheimwortHash, $antwort_gewuenscht])) {
        if ($antwort_gewuenscht) {
            $contactMessage = "Nachricht gesendet!<br><br>Deine <b>Ticket-ID:</b> <span class='w3-xlarge'>" . h($ticketId) . "</span><br>Sicherheits-<b>Geheimwort:</b> <span class='w3-xlarge'>" . h($geheimwortPlain) . "</span><br><br>Bitte speichere Dir *beide* Werte, um die Antwort unter 'Antwort Abrufen' zu lesen.";
        } else {
            $contactMessage = "Vielen Dank für deine Nachricht!";
        }
    } else {
        $contactIsError = true;
        $contactMessage = "Es gab ein Problem beim Senden der Nachricht.";
    }
}

// --- Survey Handling (Integrated from wunschTabelle.php) ---
$surveySuccessMsg = "";
if (isset($_POST['wunschauswahl']) && !isset($_SESSION['wunschauswahl'])) {
    $stmt = $pdo->prepare("UPDATE wunschspeisen SET wunschspeise_anzahl = wunschspeise_anzahl + 1 WHERE wunschspeise_nr = ?");
    if ($stmt->execute([$_POST['wunschauswahl']])) {
        $_SESSION['wunschauswahl'] = $_POST['wunschauswahl'];
        $surveySuccessMsg = "Vielen Dank für Deine Stimme!";
    }
}
if (isset($_POST['wunschauswahl_leichte']) && !isset($_SESSION['wunschauswahl_leichte'])) {
    $stmt = $pdo->prepare("UPDATE wunschspeisen SET wunschspeise_anzahl = wunschspeise_anzahl + 1 WHERE wunschspeise_nr = ?");
    if ($stmt->execute([$_POST['wunschauswahl_leichte']])) {
        $_SESSION['wunschauswahl_leichte'] = $_POST['wunschauswahl_leichte'];
        $surveySuccessMsg = "Vielen Dank für Deine Stimme!";
    }
}
if (isset($_POST['wunschauswahl2']) && !isset($_SESSION['wunschauswahl2'])) {
    $stmt = $pdo->prepare("UPDATE wunschspeisen SET wunschspeise_anzahl = wunschspeise_anzahl + 1 WHERE wunschspeise_nr = ?");
    if ($stmt->execute([$_POST['wunschauswahl2']])) {
        $_SESSION['wunschauswahl2'] = $_POST['wunschauswahl2'];
        $surveySuccessMsg = "Vielen Dank für Deine Stimme!";
    }
}

// --- CUSTOM SURVEY LOGIC ---
$activeCustomSurvey = null;
$stmtCS = $pdo->query("SELECT * FROM custom_surveys WHERE is_active = 1 AND beginn <= CURDATE() AND ende >= CURDATE() LIMIT 1");
if ($activeCustomSurvey = $stmtCS->fetch()) {
    $csId = $activeCustomSurvey['id'];
    $sessionKey = "survey_voted_" . $csId;
}

if (isset($_POST['submit_custom_survey']) && $activeCustomSurvey) {
    if (!isset($_SESSION[$sessionKey])) {
        if (isset($_POST['q']) && is_array($_POST['q'])) {
            foreach ($_POST['q'] as $qId => $answers) {
                if (!is_array($answers)) $answers = [$answers];
                foreach ($answers as $oId) {
                    $stmtVote = $pdo->prepare("UPDATE survey_options SET votes = votes + 1 WHERE id = ? AND question_id = ?");
                    $stmtVote->execute([(int)$oId, (int)$qId]);
                }
            }
            $_SESSION[$sessionKey] = true;
            $surveySuccessMsg = "Vielen Dank für Ihre Teilnahme!";
        }
    }
}
// ---------------------------
if (isset($_POST['sm_name'], $_POST['sm_beilage'], $_POST['sm_art'], $_POST['sm_kategorie'])) {
    $neueSpeiseName = trim($_POST['sm_name']) . " mit " . trim($_POST['sm_beilage']);
    $stmt = $pdo->prepare("INSERT INTO wunschspeisen (wunschspeise_name, wunschspeise_art, wunschspeise_kategorie, wunschspeise_anzahl) VALUES (?, ?, ?, 0)");
    if ($stmt->execute([$neueSpeiseName, $_POST['sm_art'], $_POST['sm_kategorie']])) {
        $surveySuccessMsg = "Vielen Dank für Deinen Vorschlag!";
    }
}

// Handle Login Form
$loginMessage = "";
if (!isAdminLoggedIn() && isset($_POST['loginname'], $_POST['passwort'])) {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE name = ?");
    $stmt->execute([$_POST['loginname']]);
    $user = $stmt->fetch();

    if ($user !== false && password_verify($_POST['passwort'], $user['passwort'])) {
        $_SESSION['admin'] = true;
        // Optional redirect to avoid resubmission
        redirect('./login.php#auswertung');
    } else {
        $loginMessage = "Bitte Name und Passwort korrekt eingeben";
    }
}

// Handle Antwort Abrufen logic
$abrMessageRecord = null;
$abrError = "";
$abrSuccess = "";

if (isset($_POST['rueckantwort'], $_POST['ticket_nr'])) {
    $stmtUpdate = $pdo->prepare("UPDATE nachrichten SET nutzer_rueckantwort = ? WHERE nachrichten_nr = ?");
    if ($stmtUpdate->execute([$_POST['rueckantwort'], $_POST['ticket_nr']])) {
        $abrSuccess = "Deine Rückantwort wurde erfolgreich gespeichert.";
    }
}

if (isset($_POST['ticket_id'], $_POST['geheimwort'])) {
    $stmt = $pdo->prepare("SELECT * FROM nachrichten WHERE ticket_id = ? AND antwort_gewuenscht = 1");
    $stmt->execute([trim($_POST['ticket_id'])]);
    $row = $stmt->fetch();

    if ($row) {
        if (password_verify($_POST['geheimwort'], $row['geheimwort_hash'])) {
            $abrMessageRecord = $row;
            if (!empty($abrMessageRecord['antwort']) && empty($abrMessageRecord['abgerufen_am'])) {
                $pdo->prepare("UPDATE nachrichten SET abgerufen_am = NOW() WHERE nachrichten_nr = ?")
                    ->execute([$abrMessageRecord['nachrichten_nr']]);
                $abrMessageRecord['abgerufen_am'] = date('Y-m-d H:i:s');
            }
        } else {
            $abrError = "Das eingegebene Geheimwort ist falsch.";
        }
    } else {
        $abrError = "Es wurde kein Ticket mit dieser ID gefunden oder es wurde keine Antwort angefordert.";
    }
}

// Check for active survey
$isSurveyActive = false;
$stmtSurvey = $pdo->query("SELECT * FROM umfrage LIMIT 1");
if ($umRow = $stmtSurvey->fetch()) {
    $now = new DateTime();
    if ($now >= new DateTime($umRow['beginn']) && $now <= new DateTime($umRow['ende'])) {
        $isSurveyActive = true;
    }
}

// Prepare Sidebar HTML
$surveySidebar = '';
$surveyNavbar = '';

// 1. Wunschspeisen-Umfrage Items
if ($isSurveyActive) {
    $surveySidebar .= '
      <a href="javascript:void(0)" onclick="openTab(\'umfrage_eingabe\', event)" class="w3-bar-item w3-button w3-padding-large survey-btn-flashing survey-nav-item">
        <i class="fa fa-pencil w3-xxlarge"></i><p>VORSCHLAG</p>
      </a>
      <a href="javascript:void(0)" onclick="openTab(\'umfrage_vollkost\', event)" class="w3-bar-item w3-button w3-padding-large survey-nav-item">
        <i class="fa fa-cutlery w3-xlarge"></i><p style="font-size:10px; margin-top:-5px;">TOP VOLLK.</p>
      </a>
      <a href="javascript:void(0)" onclick="openTab(\'umfrage_leichte\', event)" class="w3-bar-item w3-button w3-padding-large survey-nav-item">
        <i class="fa fa-cutlery w3-xlarge"></i><p style="font-size:10px; margin-top:-5px;">TOP LEICHT</p>
      </a>
      <a href="javascript:void(0)" onclick="openTab(\'umfrage_vegetarisch\', event)" class="w3-bar-item w3-button w3-padding-large survey-nav-item">
        <i class="fa fa-leaf w3-xlarge"></i><p style="font-size:10px; margin-top:-5px;">TOP VEGI</p>
      </a>';
    $surveyNavbar .= '
      <a href="javascript:void(0)" onclick="openTab(\'umfrage_eingabe\', event)" class="w3-bar-item w3-button survey-btn-flashing survey-nav-item" style="width:20% !important; border:none; font-size:10px; padding:8px 0;">NEU</a>
      <a href="javascript:void(0)" onclick="openTab(\'umfrage_vollkost\', event)" class="w3-bar-item w3-button survey-nav-item" style="width:20% !important; font-size:10px; padding:8px 0;">VOLL</a>
      <a href="javascript:void(0)" onclick="openTab(\'umfrage_leichte\', event)" class="w3-bar-item w3-button survey-nav-item" style="width:20% !important; font-size:10px; padding:8px 0;">L-VOLL</a>
      <a href="javascript:void(0)" onclick="openTab(\'umfrage_vegetarisch\', event)" class="w3-bar-item w3-button survey-nav-item" style="width:20% !important; font-size:10px; padding:8px 0;">VEGI</a>';
}

// 2. Custom FRAGEN Items (Independent)
if ($activeCustomSurvey) {
    $btnCount = ($isSurveyActive ? 5 : 1);
    $w = round(100 / $btnCount, 1) . '%';
    
    $surveySidebar = '
      <a href="javascript:void(0)" onclick="openTab(\'umfrage_fragen\', event)" class="w3-bar-item w3-button w3-padding-large survey-btn-flashing survey-nav-item custom-survey-red">
        <i class="fa fa-question-circle w3-xxlarge"></i><p>FRAGEN</p>
      </a>' . $surveySidebar;
    $surveyNavbar = '
      <a href="javascript:void(0)" onclick="openTab(\'umfrage_fragen\', event)" class="w3-bar-item w3-button survey-btn-flashing survey-nav-item custom-survey-red" style="width:'.$w.' !important; font-size:10px; padding:8px 0;">FRAGEN</a>' . $surveyNavbar;
    
    // Adjust width of previous buttons if both active
    if ($isSurveyActive) {
        $surveyNavbar = str_replace('width:20% !important', 'width:20% !important', $surveyNavbar); 
        // 20% is correct for 5 buttons.
    } else {
        $surveyNavbar = str_replace('width:20% !important', 'width:100% !important', $surveyNavbar);
    }
}

$sidebarHtml_default = '
  <a href="javascript:void(0)" onclick="openTab(\'home\', event)" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-star w3-xxlarge"></i><p>SPEISEPLAN</p>
  </a>
  <a href="javascript:void(0)" onclick="openTab(\'abstimmung\', event)" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-pie-chart w3-xxlarge"></i><p>ABSTIMMUNG</p>
  </a>
  <a href="javascript:void(0)" onclick="openTab(\'kontakt\', event)" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-envelope w3-xxlarge"></i><p>KONTAKT</p>
  </a>
  <a href="javascript:void(0)" onclick="openTab(\'abrufen\', event)" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-comments w3-xxlarge"></i><p>ANTWORT<br>ABRUFEN</p>
  </a>
  <a href="javascript:void(0)" onclick="openTab(\'login\', event)" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-unlock w3-xxlarge"></i><p>LOGIN</p>
  </a>
';
$sidebarHtml = $surveySidebar . $sidebarHtml_default;

$navbarSmallHtml_default = '
    <a href="javascript:void(0)" onclick="openTab(\'home\', event)" class="w3-bar-item w3-button" style="width:20% !important">MENSA</a>
    <a href="javascript:void(0)" onclick="openTab(\'abstimmung\', event)" class="w3-bar-item w3-button" style="width:20% !important">WAHL</a>
    <a href="javascript:void(0)" onclick="openTab(\'kontakt\', event)" class="w3-bar-item w3-button" style="width:20% !important">KONTAKT</a>
    <a href="javascript:void(0)" onclick="openTab(\'abrufen\', event)" class="w3-bar-item w3-button" style="width:20% !important">ANTWORT</a>
    <a href="javascript:void(0)" onclick="openTab(\'login\', event)" class="w3-bar-item w3-button" style="width:20% !important">LOGIN</a>
';
$navbarSmallHtml = (($isSurveyActive || $activeCustomSurvey) ? $surveyNavbar : $navbarSmallHtml_default);

$pageTitle = 'Mensaplan';
require __DIR__ . '/templates/header.php';
?>


<div id="home" class="tab-content active">
  <!-- Header/Home -->
  <header class="hero-header w3-center">
    <?php
      $firstDayString = '';
      $stmt = $pdo->query("SELECT * FROM wochenplan order by tag_id ASC LIMIT 1");
      if ($datumRow = $stmt->fetch()) {
        $firstDay = new DateTime($datumRow['datum']);
        $firstDayString = $firstDay->format('d.m.y');
      }

      $secondDayString = '';
      $stmt = $pdo->query("SELECT * FROM wochenplan order by tag_id DESC LIMIT 1");
      if ($datumRow = $stmt->fetch()) {
        $secondDay = new DateTime($datumRow['datum']);
        $secondDayString = $secondDay->format('d.m.y');
      }

      echo "<h1>Mensa - Speiseplan</h1>";
      if (!empty($firstDayString)) {
        echo "<h2>(" . h($firstDayString) . " - " . h($secondDayString) . ")</h2>";
      }
    ?>
  </header>

  <div class="page-container">
    <div class="modern-card meal-card-container">
      <div class="meal-plan-scroll-wrapper">
        <table class="modern-table meal-plan-table">
          <thead>
            <tr>
              <th style="width:15%">Tag</th>
              <th style="width:28%">Vollkost</th>
              <th style="width:28%">Leichte Vollkost</th>
              <th style="width:28%">Vegetarisch</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $tage = ["Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag"];
            $currentDayIndexMap = ["Monday" => 0, "Tuesday" => 1, "Wednesday" => 2, "Thursday" => 3, "Friday" => 4, "Saturday" => 5, "Sunday" => 6];
            $todayName = date("l");
            $todayIndex = $currentDayIndexMap[$todayName] ?? -1;

            $stmtWochen = $pdo->prepare("SELECT speisen.speise_name, speisen.speise_art 
                                         FROM wochenplan 
                                         JOIN speisen ON wochenplan.speise_nr = speisen.speise_nr 
                                         WHERE wochenplan.tag = ?");
            
            foreach ($tage as $index => $tag) {
                $vollkost = "";
                $leichteVollkost = "";
                $vegetarisch = "";
                
                $stmtWochen->execute([$tag]);
                while ($row = $stmtWochen->fetch()) {
                    if ($row['speise_art'] == 'Vollkost') $vollkost = $row['speise_name'];
                    if ($row['speise_art'] == 'Leichte Vollkost') $leichteVollkost = $row['speise_name'];
                    if ($row['speise_art'] == 'Vegetarisch') $vegetarisch = $row['speise_name'];
                }
                
                $isTodayClass = ($index === $todayIndex) ? "is-today" : "";
                echo "<tr class='meal-day-row $isTodayClass' data-day-index='$index'>";
                echo "<td class='day-name'><b>" . h($tag) . "</b><br>" . ($index === $todayIndex ? "<span class='today-badge'>Heute</span>" : "") . "</td>";
                echo "<td class='col-vk' data-label='Vollkost'>" . formatMealName($vollkost) . "</td>";
                echo "<td class='col-lvk' data-label='Leichte Vollkost'>" . formatMealName($leichteVollkost) . "</td>";
                echo "<td class='col-veg' data-label='Vegetarisch'>" . formatMealName($vegetarisch) . "</td>";
                echo "</tr>";
            }
            ?>
          </tbody>
        </table>
      </div>

      <div class="w3-center desktop-only" style="margin-top: 30px;">
        <a href="./zusatzstoffe.php" class="modern-btn secondary" target="_blank">
          <i class="fa fa-asterisk"></i> Zusatzstoffe
        </a>
      </div>
    </div>

    <script>
    // Scroll to current day on mobile
    function scrollToToday() {
        if (window.innerWidth <= 600) {
            const scrollWrapper = document.querySelector('.meal-plan-scroll-wrapper');
            const todayRow = document.querySelector('.meal-day-row.is-today');
            
            if (scrollWrapper && todayRow) {
                // Use a small timeout to ensure the browser has finished layout calculations
                setTimeout(() => {
                    todayRow.scrollIntoView({ 
                        behavior: 'auto', 
                        block: 'start', // Start of the card aligns with start of scroll area
                        inline: 'nearest' 
                    });
                }, 150);
            }
        }
    }

    // Run on load and orientation changes
    window.addEventListener('load', scrollToToday);
    window.addEventListener('resize', scrollToToday);
    </script>
  </div>
</div>

  <!-- Abstimmung Section -->
  <div id="abstimmung" class="tab-content">
    <header class="hero-header w3-center">
      <?php
        $wFirstDayString = '';
        $stmt = $pdo->query("SELECT * FROM wunschplan order by wtag_id ASC LIMIT 1");
        if ($datumRow = $stmt->fetch()) {
          $firstDay = new DateTime($datumRow['datum']);
          $wFirstDayString = $firstDay->format('d.m.y');
        }

        $wSecondDayString = '';
        $stmt = $pdo->query("SELECT * FROM wunschplan order by wtag_id DESC LIMIT 1");
        if ($datumRow = $stmt->fetch()) {
          $secondDay = new DateTime($datumRow['datum']);
          $wSecondDayString = $secondDay->format('d.m.y');
        }

        echo "<h1>Wochen-Wahl</h1>";
        if (!empty($wFirstDayString)) {
          echo "<h2>(" . h($wFirstDayString) . " - " . h($wSecondDayString) . ")</h2>";
        }
      ?>
    </header>

    <div class="page-container">
      <div class="w3-center w3-margin-bottom">
        <h2 class="w3-text-red" style="font-size:20px; font-weight:600;">! Bitte höchstens 1 mal abstimmen !</h2>
      </div>

      <form action="./abstimmung.php#abstimmung" method="post">
        <div class="modern-card">
          <div class="w3-responsive">
            <table class="modern-table">
              <tr>
                <th style="width:10%">Tag</th>
                <th style="width:5%; text-align:center;">#</th>
                <th style="width:25%">Vollkost</th>
                <th style="width:5%; text-align:center;">#</th>
                <th style="width:25%">Leichte Vollkost</th>
                <th style="width:5%; text-align:center;">#</th>
                <th style="width:25%">Vegetarisch</th>
              </tr>

              <?php
              $stmtWunsch = $pdo->prepare("SELECT speisen.speise_name, speisen.speise_art, speisen.speise_nr 
                                           FROM wunschplan 
                                           JOIN speisen ON wunschplan.speise_nr = speisen.speise_nr 
                                           WHERE wunschplan.tag = ?");
                                           
              foreach ($tage as $tag) {
                  $vk = ""; $vkNr = 0;
                  $lvk = ""; $lvkNr = 0;
                  $veg = ""; $vegNr = 0;
                  
                  $stmtWunsch->execute([$tag]);
                  while ($row = $stmtWunsch->fetch()) {
                      if ($row['speise_art'] == 'Vollkost') { $vk = $row['speise_name']; $vkNr = $row['speise_nr']; }
                      if ($row['speise_art'] == 'Leichte Vollkost') { $lvk = $row['speise_name']; $lvkNr = $row['speise_nr']; }
                      if ($row['speise_art'] == 'Vegetarisch') { $veg = $row['speise_name']; $vegNr = $row['speise_nr']; }
                  }
                  
                  echo "<tr>";
                  echo "<td><b>" . h($tag) . "</b></td>";
                  
                  $disVk = ($vk == "") ? "disabled" : "";
                  echo "<td class='col-vk'><div class='modern-radio-container'><input type='radio' name='" . h($tag) . "' value='" . h($vkNr) . "' $disVk></div></td>";
                  echo "<td class='col-vk'>" . formatMealName($vk) . "</td>";
                  
                  $disLvk = ($lvk == "") ? "disabled" : "";
                  echo "<td class='col-lvk'><div class='modern-radio-container'><input type='radio' name='" . h($tag) . "' value='" . h($lvkNr) . "' $disLvk></div></td>";
                  echo "<td class='col-lvk'>" . formatMealName($lvk) . "</td>";
                  
                  $disVeg = ($veg == "") ? "disabled" : "";
                  echo "<td class='col-veg'><div class='modern-radio-container'><input type='radio' name='" . h($tag) . "' value='" . h($vegNr) . "' $disVeg></div></td>";
                  echo "<td class='col-veg'>" . formatMealName($veg) . "</td>";
                  
                  echo "</tr>";
              }
              ?>
            </table>
          </div>
        </div>
        
        <div class="w3-center">
          <button class="modern-btn jumbo" type="submit">
            <i class="fa fa-paper-plane"></i> Abstimmen
          </button>
        </div>
      </form>
    </div>
  </div>

<!-- Tab: UMFRAGE EINGABE -->
<div id="umfrage_eingabe" class="tab-content">
  <header class="hero-header w3-center">
    <h1>Wunschspeise einreichen</h1>
    <p class="w3-text-muted">Habt Ihr eine Idee für den Speiseplan? Schickt sie uns!</p>
  </header>
  <div class="page-container">
    <?php if ($surveySuccessMsg): ?>
        <div class="modern-card w3-center">
          <h1 class='w3-jumbo w3-text-green'><?php echo $surveySuccessMsg; ?></h1>
          <br>
          <a href="javascript:void(0)" onclick="openTab('umfrage_eingabe', event)" class="modern-btn secondary">Weiteren Vorschlag senden</a>
        </div>
    <?php else: ?>
      <div class="modern-card" style="max-width: 600px; margin: 0 auto;">
        <h2 style="margin-top:0">Neuer Vorschlag</h2>
        <form action='./index.php?#umfrage_eingabe' method='post'>
          <p><input type='text' placeholder='Speise (z.B. Schnitzel)' name='sm_name' required></p>
          <p>
            <select name='sm_beilage' required>
              <option value='' disabled selected>Beilage wählen</option>
              <?php
              $pbeilagen = $pdo->query("SELECT * FROM beilagen ORDER BY beilage_art, beilage_name");
              while ($row = $pbeilagen->fetch()) {
                  echo "<option value='" . h($row['beilage_name']) . "'>" . h($row['beilage_art']) . " - " . h($row['beilage_name']) . "</option>";
              }
              ?>
            </select>
          </p>
          <p>
            <select name='sm_art' required>
              <option value='' disabled selected>Speiseart wählen</option>
              <option value='Vollkost'>Vollkost</option>
              <option value='Leichte Vollkost'>Leichte Vollkost</option>
              <option value='Vegetarisch'>Vegetarisch</option>
            </select>
          </p>
          <p>
            <select name='sm_kategorie' required>
              <option value='' disabled selected>Kategorie wählen</option>
              <option value='-'>-</option>
              <option value='Fleisch'>Fleisch</option>
              <option value='Fisch'>Fisch</option>
              <option value='Süßes'>Süßes</option>
            </select>
          </p>
          <br>
          <button class='modern-btn jumbo' type='submit' style="width:100%">
            <i class='fa fa-paper-plane'></i> Vorschlag abschicken
          </button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Tab: UMFRAGE VOLLKOST -->
<div id="umfrage_vollkost" class="tab-content">
  <header class="hero-header w3-center">
    <h1>Top-Vorschläge: Vollkost</h1>
    <p class="w3-text-muted">Stimme für Deinen Favoriten ab.</p>
  </header>
  <div class="page-container">
    <div class="modern-card">
      <?php if ($surveySuccessMsg) echo "<div class='w3-panel w3-green w3-round w3-margin-bottom'><p>$surveySuccessMsg</p></div>"; ?>
      <div class="w3-margin-bottom">
        <input type="text" onkeyup="filterSurveyTable(this, 'table_vk')" placeholder="Suchen..." style="max-width: 400px;">
      </div>
      <form action="./index.php?#umfrage_vollkost" method="post">
        <div class="w3-responsive">
          <table class="modern-table" id="table_vk">
            <tr><th style="width:5%;">#</th><th style="width:10%; text-align:center;">Wahl</th><th style="width:10%;">Votes</th><th style="width:15%;">Kat.</th><th style="width:50%;">Speise</th></tr>
            <?php
            $p = 1;
            $stmt = $pdo->query("SELECT * FROM wunschspeisen WHERE wunschspeise_art='Vollkost' ORDER BY wunschspeise_anzahl DESC, wunschspeise_kategorie ASC");
            while ($row = $stmt->fetch()) {
                $dis = isset($_SESSION['wunschauswahl']) ? "disabled" : "";
                $chk = (isset($_SESSION['wunschauswahl']) && $_SESSION['wunschauswahl'] == $row['wunschspeise_nr']) ? "checked" : "";
                echo "<tr><td>$p</td><td class='w3-center'><div class='modern-radio-container' style='justify-content:center;'><input type='radio' name='wunschauswahl' value='".$row['wunschspeise_nr']."' $dis $chk class='modern-radio shadow'></div></td><td>".h($row['wunschspeise_anzahl'])."</td><td>".h($row['wunschspeise_kategorie'])."</td><td>".formatMealName($row['wunschspeise_name'])."</td></tr>";
                $p++;
            }
            ?>
          </table>
        </div>
        <br>
        <?php if (!isset($_SESSION['wunschauswahl'])): ?>
          <button class='modern-btn jumbo' type='submit' style="width:100%"><i class='fa fa-check'></i> Stimme abgeben</button>
        <?php else: ?>
          <div class="w3-panel w3-blue w3-round"><p><i class="fa fa-info-circle"></i> Du hast bereits abgestimmt.</p></div>
        <?php endif; ?>
      </form>
    </div>
  </div>
</div>

<!-- Tab: UMFRAGE LEICHTE VOLLKOST -->
<div id="umfrage_leichte" class="tab-content">
  <header class="hero-header w3-center">
    <h1>Top-Vorschläge: Leichte Vollkost</h1>
    <p class="w3-text-muted">Stimme für Deinen Favoriten ab.</p>
  </header>
  <div class="page-container">
    <div class="modern-card">
      <?php if ($surveySuccessMsg) echo "<div class='w3-panel w3-green w3-round w3-margin-bottom'><p>$surveySuccessMsg</p></div>"; ?>
      <div class="w3-margin-bottom">
        <input type="text" onkeyup="filterSurveyTable(this, 'table_lvk')" placeholder="Suchen..." style="max-width: 400px;">
      </div>
      <form action="./index.php?#umfrage_leichte" method="post">
        <div class="w3-responsive">
          <table class="modern-table" id="table_lvk">
            <tr><th style="width:5%;">#</th><th style="width:10%; text-align:center;">Wahl</th><th style="width:10%;">Votes</th><th style="width:15%;">Kat.</th><th style="width:50%;">Speise</th></tr>
            <?php
            $p = 1;
            $stmt = $pdo->query("SELECT * FROM wunschspeisen WHERE wunschspeise_art='Leichte Vollkost' ORDER BY wunschspeise_anzahl DESC, wunschspeise_kategorie ASC");
            while ($row = $stmt->fetch()) {
                $dis = isset($_SESSION['wunschauswahl_leichte']) ? "disabled" : "";
                $chk = (isset($_SESSION['wunschauswahl_leichte']) && $_SESSION['wunschauswahl_leichte'] == $row['wunschspeise_nr']) ? "checked" : "";
                echo "<tr><td>$p</td><td class='w3-center'><div class='modern-radio-container' style='justify-content:center;'><input type='radio' name='wunschauswahl_leichte' value='".$row['wunschspeise_nr']."' $dis $chk class='modern-radio shadow'></div></td><td>".h($row['wunschspeise_anzahl'])."</td><td>".h($row['wunschspeise_kategorie'])."</td><td>".formatMealName($row['wunschspeise_name'])."</td></tr>";
                $p++;
            }
            ?>
          </table>
        </div>
        <br>
        <?php if (!isset($_SESSION['wunschauswahl_leichte'])): ?>
          <button class='modern-btn jumbo' type='submit' style="width:100%"><i class='fa fa-check'></i> Stimme abgeben</button>
        <?php else: ?>
          <div class="w3-panel w3-blue w3-round"><p><i class="fa fa-info-circle"></i> Du hast bereits abgestimmt.</p></div>
        <?php endif; ?>
      </form>
    </div>
  </div>
</div>

<!-- Tab: UMFRAGE VEGETARISCH -->
<div id="umfrage_vegetarisch" class="tab-content">
  <header class="hero-header w3-center">
    <h1>Top-Vorschläge: Vegetarisch</h1>
    <p class="w3-text-muted">Stimme für Deinen Favoriten ab.</p>
  </header>
  <div class="page-container">
    <div class="modern-card">
      <?php if ($surveySuccessMsg) echo "<div class='w3-panel w3-green w3-round w3-margin-bottom'><p>$surveySuccessMsg</p></div>"; ?>
      <div class="w3-margin-bottom">
        <input type="text" onkeyup="filterSurveyTable(this, 'table_veg')" placeholder="Suchen..." style="max-width: 400px;">
      </div>
      <form action="./index.php?#umfrage_vegetarisch" method="post">
        <div class="w3-responsive">
          <table class="modern-table" id="table_veg">
            <tr><th style="width:5%;">#</th><th style="width:10%; text-align:center;">Wahl</th><th style="width:10%;">Votes</th><th style="width:15%;">Kat.</th><th style="width:50%;">Speise</th></tr>
            <?php
            $p = 1;
            $stmt = $pdo->query("SELECT * FROM wunschspeisen WHERE wunschspeise_art='Vegetarisch' ORDER BY wunschspeise_anzahl DESC, wunschspeise_kategorie ASC");
            while ($row = $stmt->fetch()) {
                $dis = isset($_SESSION['wunschauswahl2']) ? "disabled" : "";
                $chk = (isset($_SESSION['wunschauswahl2']) && $_SESSION['wunschauswahl2'] == $row['wunschspeise_nr']) ? "checked" : "";
                echo "<tr><td>$p</td><td class='w3-center'><div class='modern-radio-container' style='justify-content:center;'><input type='radio' name='wunschauswahl2' value='".$row['wunschspeise_nr']."' $dis $chk class='modern-radio shadow'></div></td><td>".h($row['wunschspeise_anzahl'])."</td><td>".h($row['wunschspeise_kategorie'])."</td><td>".formatMealName($row['wunschspeise_name'])."</td></tr>";
                $p++;
            }
            ?>
          </table>
        </div>
        <br>
        <?php if (!isset($_SESSION['wunschauswahl2'])): ?>
          <button class='modern-btn jumbo' type='submit' style="width:100%"><i class='fa fa-check'></i> Stimme abgeben</button>
        <?php else: ?>
          <div class="w3-panel w3-blue w3-round"><p><i class="fa fa-info-circle"></i> Du hast bereits abgestimmt.</p></div>
        <?php endif; ?>
      </form>
    </div>
  </div>
</div>

<?php if ($activeCustomSurvey): ?>
<!-- Tab: FRAGEN (Custom Survey) -->
<div id="umfrage_fragen" class="tab-content">
  <header class="hero-header w3-center">
    <h1><?php echo h($activeCustomSurvey['title']); ?></h1>
    <p class="w3-text-muted">Deine Meinung ist uns wichtig!</p>
  </header>
  <div class="page-container">
    <div class="modern-card">
      <?php if ($surveySuccessMsg) echo "<div class='w3-panel w3-green w3-round w3-margin-bottom'><p>$surveySuccessMsg</p></div>"; ?>
      
      <?php if (isset($_SESSION[$sessionKey])): ?>
          <!-- View Results -->
          <h2 class="w3-text-white">Aktuelle Ergebnisse</h2>
          <?php
          $questions = $pdo->prepare("SELECT * FROM survey_questions WHERE survey_id = ?");
          $questions->execute([$csId]);
          while ($q = $questions->fetch()):
               echo "<div class='w3-margin-bottom'>";
               echo "<h4 class='w3-text-white'><b>".h($q['question_text'])."</b></h4>";
               $options = $pdo->prepare("SELECT * FROM survey_options WHERE question_id = ?");
               $options->execute([$q['id']]);
               $opts = $options->fetchAll();
               $totalVotes = array_sum(array_column($opts, 'votes'));
               foreach ($opts as $o) {
                   $pct = ($totalVotes > 0) ? round(($o['votes'] / $totalVotes) * 100) : 0;
                   echo "<div class='w3-small w3-text-muted' style='display:flex; justify-content:space-between;'><span>".h($o['option_text'])."</span><span>$pct% (".h($o['votes']).")</span></div>";
                   echo "<div class='w3-light-grey w3-round w3-tiny' style='height:8px; background: rgba(255,255,255,0.1); margin-bottom:10px;'><div class='w3-container w3-blue w3-round' style='height:8px; width:$pct%'></div></div>";
               }
               echo "</div><hr class='w3-opacity' style='margin: 20px 0;'>";
          endwhile;
          ?>
          <p class="w3-text-muted w3-center">Vielen Dank für Deine Teilnahme!</p>
      <?php else: ?>
          <!-- Voting Form -->
          <form action="./index.php?#umfrage_fragen" method="post">
            <input type="hidden" name="submit_custom_survey" value="1">
            <?php
            $questions = $pdo->prepare("SELECT * FROM survey_questions WHERE survey_id = ?");
            $questions->execute([$csId]);
            while ($q = $questions->fetch()):
            ?>
              <div class="w3-margin-bottom">
                 <h4 class="w3-text-white"><b><?php echo h($q['question_text']); ?></b></h4>
                 <?php
                 $options = $pdo->prepare("SELECT * FROM survey_options WHERE question_id = ?");
                 $options->execute([$q['id']]);
                 while ($o = $options->fetch()):
                     $inputName = "q[".$q['id']."]" . ($q['type'] == 'checkbox' ? '[]' : '');
                     $inputId = "o_" . $o['id'];
                 ?>
                   <div style="margin: 15px 0; display:flex; align-items:center; gap:12px;">
                      <?php if ($q['type'] == 'checkbox'): ?>
                        <input type="checkbox" name="<?php echo h($inputName); ?>" value="<?php echo h($o['id']); ?>" id="<?php echo h($inputId); ?>" class="modern-checkbox">
                      <?php else: ?>
                        <input type="radio" name="<?php echo h($inputName); ?>" value="<?php echo h($o['id']); ?>" id="<?php echo h($inputId); ?>" class="modern-radio shadow">
                      <?php endif; ?>
                      <label for="<?php echo h($inputId); ?>" style="cursor:pointer; flex:1; line-height: 1.1; font-size: 15.5px;"><?php echo h($o['option_text']); ?></label>
                   </div>
                 <?php endwhile; ?>
              </div>
              <hr class="w3-opacity" style="margin: 30px 0;">
            <?php endwhile; ?>
            <br>
            <button class='modern-btn jumbo' type='submit' style="width:100%"><i class='fa fa-paper-plane'></i> Umfrage absenden</button>
          </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

  <?php
  // Check for Umfrage
  $stmt = $pdo->query("SELECT * FROM umfrage LIMIT 1");
  if ($umfrageRow = $stmt->fetch()) {
      $beginnDate = new DateTime($umfrageRow['beginn']);
      $endDate = new DateTime($umfrageRow['ende']);
      $now = new DateTime('now');
      
      if ($now >= $beginnDate && $now <= $endDate) {
          echo "<div class='modern-card w3-center w3-margin'>";
          echo "<h1 class='w3-jumbo'>Eure Wunschspeisen</h1>";
          echo "<p style='color: var(--danger-color); font-weight:600'>Aktuell: Umfrage vom ".h($beginnDate->format('d.m.y'))." bis ".h($endDate->format('d.m.y'))."</p>";
          echo "<p class='w3-text-muted'>Neben dem Wahlmenü habt Ihr ab sofort auch die Möglichkeit den Speiseplan selbst zu gestalten.<br>";
          echo "Alle drei Monate könnt Ihr eigene Wunschspeisen vorschlagen und für Vorschläge von anderen stimmen.<br>";
          echo "Die <b>Top-Sieben</b> Vorschläge werden innerhalb von 2-4 Wochen in den Speiseplan aufgenommen.<br>";
          echo "Jeder hat eine Stimme pro Kategorie.</p><br>";
          echo "<a href='javascript:void(0)' onclick=\"openTab('umfrage_eingabe', event)\" class='modern-btn'>";
          echo "<i class='fa fa-pencil'></i> Zur Umfrage / Vorschlag einreichen";
          echo "</a>";
          echo "</div>";
      }
  }
  ?>

  <!-- Kontakt Section -->
  <div id="kontakt" class="tab-content">
    <header class="hero-header w3-center">
      <h1>Kontakt & Hilfe</h1>
      <p class="w3-text-muted">Habt Ihr Fragen oder Feedback?</p>
    </header>

    <div class="page-container">
      <div class="modern-card">
        <h2 style="margin-top:0">Nachricht schreiben</h2>
        <p class="w3-text-muted">Teilnehmer mit Sonderkost können am Freitag von 10:00 bis 10:30 Uhr zur Sprechstunde zur Küche kommen.</p>
        <hr class="w3-opacity">

        <?php if (empty($contactMessage)): ?>
          <form action='./index.php?#kontakt' method='post'>
            <p><input class='w3-input w3-padding-16' type='text' placeholder='Name (optional)' name='name'></p>
            <p>
              <select class='w3-input w3-padding-16' name='thema' required>
                <option value='' disabled selected>Thema auswählen</option>
                <option value='Essens_Vorschlag'>Essens-Vorschlag</option>
                <option value='Feedback'>Feedback</option>
                <option value='Sonstiges'>Sonstiges</option>
              </select>
            </p>
            <p><textarea class='w3-input w3-padding-16' placeholder='Nachricht' required name='nachricht' rows="4"></textarea></p>
            
            <p>
              <label class="w3-text-muted" style="display:flex; align-items:center; gap:10px;">
                <input type="checkbox" name="antwort_gewuenscht" class="modern-checkbox"> Ich möchte eine Antwort der Küche erhalten <small>(Es wird ein Abruf-Code generiert)</small>
              </label>
            </p><br>

            <p>
              <button class='modern-btn' type='submit'>
                <i class='fa fa-paper-plane'></i> Abschicken
              </button>
            </p>
          </form>
        <?php else: ?>
          <div class='w3-panel <?php echo $contactIsError ? "w3-red" : "w3-green"; ?>'>
            <?php echo $contactMessage; ?>
          </div>
          <p><br><a href="./index.php#kontakt" class="modern-btn secondary"><i class="fa fa-refresh"></i> Neue Nachricht schreiben</a></p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Antwort Abrufen Section -->
  <div id="abrufen" class="tab-content">
    <header class="hero-header w3-center">
      <h1>Antwort der Küche abrufen</h1>
      <p class="w3-text-muted">Geben Sie hier Ihre Ticket-ID und Ihr Geheimwort ein.</p>
    </header>

    <div class="page-container">
      <?php if ($abrSuccess): ?>
          <div class="w3-panel w3-green w3-padding-large w3-margin-bottom">
              <p><?php echo h($abrSuccess); ?></p>
          </div>
      <?php endif; ?>

      <?php if ($abrMessageRecord): ?>
        <div class="modern-card">
          <h2 style="margin-top:0">Ticket: <?php echo h($abrMessageRecord['ticket_id']); ?></h2>
          <?php if (!empty($abrMessageRecord['erstellt_am'])): ?>
              <p class="w3-text-muted">Erstellt am: <?php echo h((new DateTime($abrMessageRecord['erstellt_am']))->format('d.m.Y H:i')); ?> Uhr</p>
          <?php else: ?>
              <p class="w3-text-muted">Datum: <?php echo h((new DateTime($abrMessageRecord['datum']))->format('d.m.Y')); ?></p>
          <?php endif; ?>
          <hr class="w3-opacity">
          
          <div class="w3-margin-bottom">
              <h4 class="w3-text-white"><b>Deine Nachricht (<?php echo h($abrMessageRecord['thema']); ?>)</b></h4>
              <div class="w3-padding w3-round" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); white-space: pre-wrap;"><?php echo h($abrMessageRecord['nachricht']); ?></div>
          </div>

          <div>
              <h4 class="w3-text-white"><b>Antwort der Küche</b></h4>
              <?php if (!empty($abrMessageRecord['antwort'])): ?>
                  <div class="w3-padding w3-round" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); white-space: pre-wrap; color: #93c5fd;"><?php echo h($abrMessageRecord['antwort']); ?></div>
                  
                  <div class="w3-margin-top">
                      <?php if (empty($abrMessageRecord['nutzer_rueckantwort'])): ?>
                          <details style="margin-top:20px;">
                            <summary class="modern-btn secondary small" style="cursor:pointer; display:inline-block;"><i class="fa fa-reply"></i> Auf Antwort reagieren</summary>
                            <form action="./index.php#abrufen" method="post" class="w3-margin-top">
                                <input type="hidden" name="ticket_id" value="<?php echo h($_POST['ticket_id'] ?? ''); ?>">
                                <input type="hidden" name="geheimwort" value="<?php echo h($_POST['geheimwort'] ?? ''); ?>">
                                <input type="hidden" name="ticket_nr" value="<?php echo h($abrMessageRecord['nachrichten_nr']); ?>">
                                <textarea name="rueckantwort" class="w3-input w3-border w3-round w3-transparent w3-text-white" rows="3" placeholder="Ihre abschließende Rückantwort..." required style="background: rgba(0,0,0,0.2) !important;"></textarea>
                                <button type="submit" class="modern-btn w3-margin-top"><i class="fa fa-paper-plane"></i> Senden</button>
                            </form>
                          </details>
                      <?php else: ?>
                          <h4 class="w3-text-white w3-margin-top"><b>Deine Rückantwort</b></h4>
                          <div class="w3-padding w3-round" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); white-space: pre-wrap;"><?php echo h($abrMessageRecord['nutzer_rueckantwort']); ?></div>
                          <p class="w3-small w3-text-muted"><i>(Notiz: Dieses Ticket hat bereits die maximale Konversationstiefe erreicht.)</i></p>
                      <?php endif; ?>
                  </div>
              <?php else: ?>
                  <div class="w3-padding w3-round" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); color: #fcd34d;"><i>Die Küche hat Deine Nachricht noch nicht beantwortet. Bitte schaue später noch einmal vorbei.</i></div>
              <?php endif; ?>
          </div>
          <br>
          <p><a href="./index.php#abrufen" class="modern-btn secondary"><i class="fa fa-refresh"></i> Weiteres Ticket prüfen</a></p>
        </div>
        
      <?php else: ?>
        
        <div class="modern-card" style="max-width: 500px; margin: 0 auto;">
          <h2 style="margin-top:0">Ticket laden</h2>
          <?php if ($abrError): ?>
            <p class="w3-text-red w3-center"><?php echo h($abrError); ?></p>
          <?php endif; ?>

          <form action='./index.php#abrufen' method='post'>
            <p><input type='text' placeholder='Ticket-ID (z.B. 8A2F1C)' name='ticket_id' required></p>
            <p><input type='password' placeholder='Geheimwort' name='geheimwort' required></p>
            <p>
              <button class='modern-btn' type='submit' style="width:100%;">
                <i class='fa fa-search'></i> Abrufen
              </button>
            </p>
          </form>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Login Section -->
  <div id="login" class="tab-content">
    <header class="hero-header w3-center">
      <h1>Küche Login</h1>
    </header>

    <div class="page-container">
      <div class="modern-card w3-center" style="max-width: 500px; margin: 0 auto;">
        <h2 style="margin-top:0">Zutritt nur für Personal</h2>
        <br>
        
        <?php if (!empty($loginMessage)): ?>
            <p class="w3-text-red"><?php echo h($loginMessage); ?></p>
        <?php endif; ?>

        <?php if (!isAdminLoggedIn()): ?>
          <form action='./index.php?#login' method='post'>
            <p><input type='text' name='loginname' placeholder='Name' required></p>
            <p><input type='password' name='passwort' placeholder='Passwort' required></p>
            <p>
              <button class='modern-btn' type='submit' style="width:100%;">
                <i class='fa fa-unlock'></i> Einloggen
              </button>
            </p>
          </form>
        <?php else: ?>
          <form action='./login.php'>
            <button class='modern-btn' type='submit' style="width:100%;">
              <i class='fa fa-unlock'></i> Zum Dashboard
            </button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>

<script>
function filterSurveyTable(input, tableId) {
    var filter = input.value.toUpperCase();
    var table = document.getElementById(tableId);
    var tr = table.getElementsByTagName("tr");
    for (var i = 1; i < tr.length; i++) {
        var display = false;
        var tdKat = tr[i].getElementsByTagName("td")[3];
        var tdName = tr[i].getElementsByTagName("td")[4];
        if (tdKat || tdName) {
            var txtKat = tdKat ? (tdKat.textContent || tdKat.innerText) : "";
            var txtName = tdName ? (tdName.textContent || tdName.innerText) : "";
            if (txtKat.toUpperCase().indexOf(filter) > -1 || txtName.toUpperCase().indexOf(filter) > -1) {
                display = true;
            }
        }
        tr[i].style.display = display ? "" : "none";
    }
}
</script>

<?php 
require __DIR__ . '/templates/footer.php'; 
?>
