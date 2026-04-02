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

// Handle Login Form
$loginMessage = "";
if (!isAdminLoggedIn() && isset($_POST['loginname'], $_POST['passwort'])) {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE name = ?");
    $stmt->execute([$_POST['loginname']]);
    $user = $stmt->fetch();

    if ($user !== false && password_verify($_POST['passwort'], $user['passwort'])) {
        $_SESSION['admin'] = true;
        // Optional redirect to avoid resubmission
        redirect('./login.php');
    } else {
        $loginMessage = "Bitte Name und Passwort korrekt eingeben";
    }
}

// Prepare Sidebar HTML
$sidebarHtml = '
  <a href="#" class="w3-bar-item w3-button w3-padding-large w3-black">
    <i class="fa fa-star w3-xxlarge"></i><p>MENSA-ESSEN</p>
  </a>
  <a href="#abstimmung" class="w3-bar-item w3-button w3-padding-large w3-hover-black">
    <i class="fa fa-pie-chart w3-xxlarge"></i><p>ABSTIMMUNG</p>
  </a>
  <a href="#kontakt" class="w3-bar-item w3-button w3-padding-large w3-hover-black">
    <i class="fa fa-envelope w3-xxlarge"></i><p>KONTAKT</p>
  </a>
  <a href="./abrufen.php" class="w3-bar-item w3-button w3-padding-large w3-hover-black">
    <i class="fa fa-comments w3-xxlarge"></i><p>ANTWORT ABRUFEN</p>
  </a>
  <a href="#login" class="w3-bar-item w3-button w3-padding-large w3-hover-black">
    <i class="fa fa-unlock w3-xxlarge"></i><p>LOGIN</p>
  </a>
  <a href="#impressum" class="w3-bar-item w3-button w3-padding-large w3-hover-black">
    <i class="fa fa-address-card w3-xxlarge"></i><p>IMPRESSUM</p>
  </a>
';
$navbarSmallHtml = '
    <a href="#" class="w3-bar-item w3-button" style="width:20% !important">MENSA-ESSEN</a>
    <a href="#abstimmung" class="w3-bar-item w3-button" style="width:20% !important">ABSTIMMUNG</a>
    <a href="#kontakt" class="w3-bar-item w3-button" style="width:20% !important">KONTAKT</a>
    <a href="./abrufen.php" class="w3-bar-item w3-button" style="width:20% !important">ANTWORTEN</a>
    <a href="#login" class="w3-bar-item w3-button" style="width:20% !important">LOGIN</a>
';

$pageTitle = 'Mensaplan';
require __DIR__ . '/templates/header.php';
?>

  <!-- Header/Home -->
  <header class="w3-container w3-padding-32 w3-center w3-black" id="home">
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

      echo "<h1 class='w3-jumbo'>Mensa - Speiseplan</h1>";
      if (!empty($firstDayString)) {
        echo "<h2 class='w3-xxlarge'>(" . h($firstDayString) . " - " . h($secondDayString) . ")</h2>";
      }
    ?>
  </header>

  <div class="w3-responsive">
    <table class="w3-table-all w3-large w3-text-black">
      <tr class="w3-green w3-center">
        <th class="w3-center">Wochentag</th>
        <th class="w3-center">Vollkost</th>
        <th class="w3-center">Leichte Vollkost</th>
        <th class="w3-center">Vegetarisch</th>
      </tr>

      <?php
      $tage = ["Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag"];
      
      $stmtWochen = $pdo->prepare("SELECT speisen.speise_name, speisen.speise_art 
                                   FROM wochenplan 
                                   JOIN speisen ON wochenplan.speise_nr = speisen.speise_nr 
                                   WHERE wochenplan.tag = ?");
      
      foreach ($tage as $tag) {
          $vollkost = "";
          $leichteVollkost = "";
          $vegetarisch = "";
          
          $stmtWochen->execute([$tag]);
          while ($row = $stmtWochen->fetch()) {
              if ($row['speise_art'] == 'Vollkost') $vollkost = $row['speise_name'];
              if ($row['speise_art'] == 'Leichte Vollkost') $leichteVollkost = $row['speise_name'];
              if ($row['speise_art'] == 'Vegetarisch') $vegetarisch = $row['speise_name'];
          }
          
          echo "<tr>";
          echo "<td><b>" . h($tag) . "</b></td>";
          echo "<td>" . h($vollkost) . "</td>";
          echo "<td>" . h($leichteVollkost) . "</td>";
          echo "<td>" . h($vegetarisch) . "</td>";
          echo "</tr>";
      }
      ?>
    </table>

    <a href="./zusatzstoffe.php" class="w3-bar-item w3-button w3-padding-large w3-black" target="_blank">
      <i class="fa fa-asterisk w3-xxlarge"></i>
      <p>Zusatzstoffe</p>
    </a>
  </div>

  <!-- Abstimmung Section -->
  <div class="w3-container w3-padding-64 w3-center" id="abstimmung">
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

      echo "<h1 class='w3-jumbo'>Abstimmung für nächste Woche</h1>";
      if (!empty($wFirstDayString)) {
        echo "<h2 class='w3-xxlarge'>(" . h($wFirstDayString) . " - " . h($wSecondDayString) . ")</h2>";
      }
    ?>
    <h2 class="w3-xxlarge w3-text-red">! Bitte höchstens 1 mal abstimmen !</h2>

    <form action="./abstimmung.php#abstimmung" method="post">
      <div class="w3-responsive">
        <table class="w3-table-all w3-large w3-text-black">
          <tr class="w3-blue w3-center">
            <th class="w3-center">Wochentag</th>
            <th class="w3-center">Auswahl</th>
            <th class="w3-center">Vollkost</th>
            <th class="w3-center">Auswahl</th>
            <th class="w3-center">Leichte Vollkost</th>
            <th class="w3-center">Auswahl</th>
            <th class="w3-center">Vegetarisch</th>
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
              echo "<td class='w3-center'><input type='radio' name='" . h($tag) . "' value='" . h($vkNr) . "' $disVk></td>";
              echo "<td>" . h($vk) . "</td>";
              
              $disLvk = ($lvk == "") ? "disabled" : "";
              echo "<td class='w3-center'><input type='radio' name='" . h($tag) . "' value='" . h($lvkNr) . "' $disLvk></td>";
              echo "<td>" . h($lvk) . "</td>";
              
              $disVeg = ($veg == "") ? "disabled" : "";
              echo "<td class='w3-center'><input type='radio' name='" . h($tag) . "' value='" . h($vegNr) . "' $disVeg></td>";
              echo "<td>" . h($veg) . "</td>";
              
              echo "</tr>";
          }
          ?>
        </table>
      </div>
      <br><br>
      <button class="w3-button w3-jumbo w3-blue w3-padding-large" type="submit">
        <i class="fa fa-paper-plane"></i> Abstimmen
      </button>
    </form>
  </div>

  <?php
  // Check for Umfrage
  $stmt = $pdo->query("SELECT * FROM umfrage LIMIT 1");
  if ($umfrageRow = $stmt->fetch()) {
      $beginnDate = new DateTime($umfrageRow['beginn']);
      $endDate = new DateTime($umfrageRow['ende']);
      $now = new DateTime('now');
      
      if ($now >= $beginnDate && $now <= $endDate) {
          echo "<center>";
          echo "<h1 class='w3-jumbo'>Eure Wunschspeisen</h1>";
          echo "<em><h2 class='w3-xxlarge w3-text-red'>Aktuell: Umfrage vom ".h($beginnDate->format('d.m.y'))." bis ".h($endDate->format('d.m.y'))."</h2>";
          echo "<h2 class='w3-xxlarge'>Neben dem Wahlmenü habt Ihr ab sofort auch die Möglichkeit den Speiseplan selbst zu gestalten.</h2>";
          echo "<h2 class='w3-xxlarge'>Alle drei Monate könnt Ihr eigene Wunschspeisen vorschlagen und für Vorschläge von anderen stimmen.</h2>";
          echo "<h2 class='w3-xxlarge'>Die <b>Top-Sieben</b> Vorschläge werden innerhalb von 2-4 Wochen in den Speiseplan aufgenommen.</h2>";
          echo "<h2 class='w3-xxlarge'>Jeder hat eine Stimme</h2></em>";
          echo "<a href='./wunschTabelle.php' class='w3-bar-item w3-button w3-padding-large w3-black' target='_blank '>";
          echo "<i class='fa fa-line-chart w3-jumbo'></i>";
          echo "<p>Zur Umfrage</p>";
          echo "</a>";
          echo "</center>";
      }
  }
  ?>

  <!-- Kontakt Section -->
  <div class="w3-padding-64 w3-content w3-text-grey" id="kontakt">
    <h1 class="w3-jumbo w3-text-light-grey">Schreibt uns eine Nachricht</h1>
    <hr style="width:200px" class="w3-opacity">
    <h2 class="w3-xxlarge w3-text-light-grey">Teilnehmer mit Sonderkost können am Freitag um 10 bis 10:30 Uhr zur Sprechstunde zur Küche kommen</h2>
    <hr style="width:200px" class="w3-opacity">

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
        
        <!-- Toggle für Antworten -->
        <p>
          <label class="w3-large w3-text-white">
            <input type="checkbox" name="antwort_gewuenscht" class="w3-check"> Ich möchte eine Antwort der Küche erhalten (ein Abruf-Code wird generiert)
          </label>
        </p>

        <p>
          <button class='w3-button w3-light-grey w3-padding-large' type='submit'>
            <i class='fa fa-paper-plane'></i> Abschicken
          </button>
        </p>
      </form>
    <?php else: ?>
      <div class='w3-panel <?php echo $contactIsError ? "w3-red" : "w3-green"; ?> w3-padding-large'>
        <h2 class='w3-text-white'><?php echo $contactMessage; ?></h2>
      </div>
      <p><a href="./index.php#kontakt" class="w3-button w3-light-grey"><i class="fa fa-refresh"></i> Neue Nachricht schreiben</a></p>
    <?php endif; ?>
  </div>

  <!-- Login Section -->
  <div class="w3-padding-64 w3-content" id="login">
    <h1 class="w3-jumbo w3-center">Login Küche</h1>
    
    <?php if (!empty($loginMessage)): ?>
        <p class="w3-text-red w3-large w3-center"><?php echo h($loginMessage); ?></p>
    <?php endif; ?>

    <?php if (!isAdminLoggedIn()): ?>
      <form action='./index.php?#login' method='post'>
        <p><input class='w3-input w3-padding-16' type='text' name='loginname' placeholder='Name'></p>
        <p><input class='w3-input w3-padding-16' type='password' name='passwort' placeholder='Passwort'></p>
        <p>
          <button class='w3-button w3-light-grey w3-padding-large' type='submit'>
            <i class='fa fa-unlock'></i> Login
          </button>
        </p>
      </form>
    <?php else: ?>
      <form action='./login.php'>
        <button class='w3-button w3-blue w3-padding-large' type='submit'>
          <i class='fa fa-unlock'></i> Zum Dashboard
        </button>
      </form>
    <?php endif; ?>
  </div>

<?php 
require_once __DIR__ . '/impressum.php';
require __DIR__ . '/templates/footer.php'; 
?>
