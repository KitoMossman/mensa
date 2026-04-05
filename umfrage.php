<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/Database.php';

requireAdmin();
$pdo = Database::getInstance()->getConnection();

// --- Redirect to consolidated dashboard ---
if (strpos($_SERVER['PHP_SELF'], 'umfrage.php') !== false) {
    // header("Location: ./login.php#umfrage"); // Optional automatic redirect
    $msg = "<div class='w3-panel w3-blue w3-round'><p><i class='fa fa-info-circle'></i> <b>Tipp:</b> Die Umfrage-Verwaltung ist jetzt vollständig in das <a href='./login.php#umfrage' class='w3-text-white'><u>Admin Dashboard</u></a> integriert.</p></div>";
}

$hasUmfrage = false;

if (isset($_POST['beginDate'], $_POST['endDate'])) {
    $pdo->query("DELETE FROM umfrage");
    $stmt = $pdo->prepare("INSERT INTO umfrage (beginn, ende) VALUES (?, ?)");
    if ($stmt->execute([$_POST['beginDate'], $_POST['endDate']])) {
         $msg = "<p class='w3-text-green w3-large'>Umfrage erfolgreich gestartet.</p>";
    }
}

if (isset($_POST['stop'])) {
    $hasUmfrage = ($pdo->query("SELECT COUNT(*) FROM umfrage")->fetchColumn() > 0);
    if ($hasUmfrage) {
        $pdo->query("DELETE FROM umfrage");
        $pdo->query("CREATE TABLE IF NOT EXISTS ergebnis_umfrage SELECT * FROM wunschspeisen");
        $pdo->query("DELETE FROM ergebnis_umfrage");
        $pdo->query("INSERT INTO ergebnis_umfrage SELECT * FROM wunschspeisen");
        $pdo->query("DELETE FROM wunschspeisen");
        $msg = "<p class='w3-text-green w3-large'>Umfrage gestoppt und ausgewertet.</p>";
    }
}

if ($hasUmfrage) {
    if ($row = $pdo->query("SELECT * FROM umfrage LIMIT 1")->fetch()) {
         $beginnStr = (new DateTime($row['beginn']))->format('d.m.y');
         $endStr = (new DateTime($row['ende']))->format('d.m.y');
    }
}

// --- CUSTOM SURVEY HANDLERS ---
if (isset($_POST['create_custom_survey'])) {
    $stmt = $pdo->prepare("INSERT INTO custom_surveys (title, beginn, ende, is_active) VALUES (?, ?, ?, 1)");
    $stmt->execute([$_POST['cs_title'], $_POST['cs_begin'], $_POST['cs_end']]);
    $msg = "<p class='w3-text-green w3-large'>Eigene Umfrage erstellt.</p>";
}

if (isset($_POST['delete_survey'])) {
    $stmt = $pdo->prepare("DELETE FROM custom_surveys WHERE id = ?");
    $stmt->execute([$_POST['survey_id']]);
    $msg = "<p class='w3-text-red w3-large'>Umfrage gelöscht.</p>";
}

if (isset($_POST['add_question'])) {
    $stmt = $pdo->prepare("INSERT INTO survey_questions (survey_id, question_text, type) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['survey_id'], $_POST['q_text'], $_POST['q_type']]);
}

if (isset($_POST['delete_question'])) {
    $stmt = $pdo->prepare("DELETE FROM survey_questions WHERE id = ?");
    $stmt->execute([$_POST['q_id']]);
}

if (isset($_POST['add_option'])) {
    $stmt = $pdo->prepare("INSERT INTO survey_options (question_id, option_text) VALUES (?, ?)");
    $stmt->execute([$_POST['q_id'], $_POST['o_text']]);
}

if (isset($_POST['delete_option'])) {
    $stmt = $pdo->prepare("DELETE FROM survey_options WHERE id = ?");
    $stmt->execute([$_POST['o_id']]);
}
// ------------------------------

$pageTitle = 'Umfrage';
$sidebarHtml = '
  <a href="./login.php" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-arrow-left w3-xxlarge"></i><p>ZURÜCK</p>
  </a>
';
$navbarSmallHtml = '
    <a href="./login.php" class="w3-bar-item w3-button" style="width:100% !important">ZURÜCK</a>
';

require __DIR__ . '/templates/header.php';
?>

  <header class="hero-header w3-center">
    <h1>Umfrage Verwaltung</h1>
    <p class="w3-text-muted">Hier können Sie neue Umfragen starten oder Ergebnisse einsehen.</p>
  </header>

  <div class="page-container">
    <div class="w3-center">
      <?php echo $msg; ?>
      <div class="modern-card" style="max-width: 800px; margin: 0 auto 40px auto;">
        <?php if ($hasUmfrage): ?>
          <h2 class='w3-text-white'>Aktuell: Umfrage läuft</h2>
          <p class="w3-text-muted">Von <b><?php echo h($beginnStr); ?></b> bis <b><?php echo h($endStr); ?></b></p>
        <?php else: ?>
          <h2 class='w3-text-white'>Keine aktive Umfrage</h2>
        <?php endif; ?>

        <div class="w3-padding-32">
          <h3 class="w3-text-white">Neue Umfrage starten</h3>
          <form action='./umfrage.php' method='post'>
             <div class="w3-row-padding">
                <div class="w3-half">
                  <label class="w3-text-muted">Beginn:</label>
                  <input type='date' name='beginDate' required style="margin-top:5px;">
                </div>
                <div class="w3-half">
                  <label class="w3-text-muted">Ende:</label>
                  <input type='date' name='endDate' required style="margin-top:5px;">
                </div>
             </div>
             <br>
             <button class='modern-btn jumbo' type='submit' style="width:100%">
                <i class='fa fa-play'></i> Umfrage starten
             </button>
          </form>
          
          <?php if ($hasUmfrage): ?>
            <hr class="w3-opacity">
            <form action='./umfrage.php' method='post'>
              <button class='modern-btn secondary jumbo' name='stop' type='submit' onclick="return confirm('Sicher? Aktuelle Ergebnisse werden in die Historie geschrieben und dann zurückgesetzt.');" style="width:100%; border-color: #ef4444; color: #ef4444;">
                <i class='fa fa-stop'></i> Beenden und Auswerten
              </button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php
    function renderUmfrageErgebnis($pdo, $art, $title) {
        echo "<div id='" . strtolower($art) . "' class='modern-card w3-margin-bottom'>";
        echo "<h2 class='w3-text-white' style='margin-top:0'>Ergebnis " . h($title) . "</h2>";

        echo "<div class='w3-responsive'>";
        echo "<table class='modern-table'>";
        echo "<tr>";
        echo "<th>Platz</th>";
        echo "<th>Votes</th>";
        echo "<th>Kategorie</th>";
        echo "<th>Speise</th>";
        echo "</tr>";

        $platz = 1;
        $stmt = $pdo->prepare("SELECT * FROM ergebnis_umfrage WHERE wunschspeise_art = ? ORDER BY wunschspeise_anzahl DESC, wunschspeise_kategorie ASC");
        $stmt->execute([$art]);

        while ($row = $stmt->fetch()) {
            echo "<tr>";
            echo "<td><b>" . $platz++ . "</b></td>";
            echo "<td>" . h($row['wunschspeise_anzahl']) . "</td>";
            echo "<td>" . h($row['wunschspeise_kategorie']) . "</td>";
            echo "<td>" . h($row['wunschspeise_name']) . "</td>";
            echo "</tr>";
        }
        echo "</table></div></div>";
    }

    $tableExists = false;
    try {
        $pdo->query("SELECT 1 FROM ergebnis_umfrage LIMIT 1");
        $tableExists = true;
    } catch (Exception $e) {}

    if ($tableExists) {
        renderUmfrageErgebnis($pdo, 'Vollkost', 'Vollkost');
        renderUmfrageErgebnis($pdo, 'Leichte Vollkost', 'Leichte Vollkost');
        renderUmfrageErgebnis($pdo, 'Vegetarisch', 'Vegetarisch');
    } else {
        echo "<div class='modern-card w3-center'><h2 class='w3-text-white'>Keine Ergebnisse bisher vorhanden.</h2></div>";
    }
    ?>

    <div class="modern-card">
      <h2 class="w3-text-white">Eigene Umfragen (FRAGEN)</h2>
      <p class="w3-text-muted">Hier können Sie individuelle Fragen zu Themen wie Buffet, Getränken etc. erstellen.</p>
      
      <hr class="w3-opacity">
      
      <!-- New Survey Form -->
      <h3 class="w3-text-white">Neue Umfrage erstellen</h3>
      <form action="./umfrage.php" method="post" class="w3-margin-bottom">
        <div class="w3-row-padding">
          <div class="w3-third">
            <input type="text" name="cs_title" placeholder="Titel (z.B. Feedback zum Salat)" required>
          </div>
          <div class="w3-third">
            <input type="date" name="cs_begin" required>
          </div>
          <div class="w3-third">
            <input type="date" name="cs_end" required>
          </div>
        </div>
        <br>
        <button type="submit" name="create_custom_survey" class="modern-btn">
          <i class="fa fa-plus"></i> Umfrage erstellen
        </button>
      </form>

      <hr class="w3-opacity">

      <!-- Survey List & Editor -->
      <?php
      $surveys = $pdo->query("SELECT * FROM custom_surveys ORDER BY id DESC");
      while ($s = $surveys->fetch()):
      ?>
        <div class="modern-card" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); margin-bottom: 20px;">
          <div style="display:flex; justify-content:space-between; align-items:center;">
             <h3 style="margin:0;"><?php echo h($s['title']); ?> 
               <small class="w3-text-muted">(<?php echo h($s['beginn']); ?> - <?php echo h($s['ende']); ?>)</small>
             </h3>
             <form action="./umfrage.php" method="post" onsubmit="return confirm('Sicher löschen?');">
               <input type="hidden" name="survey_id" value="<?php echo $s['id']; ?>">
               <button type="submit" name="delete_survey" class="w3-button w3-red w3-round w3-small"><i class="fa fa-trash"></i></button>
             </form>
          </div>

          <div class="w3-padding-16">
            <!-- Add Question Form -->
             <form action="./umfrage.php" method="post" style="display:flex; gap:10px;">
               <input type="hidden" name="survey_id" value="<?php echo $s['id']; ?>">
               <input type="text" name="q_text" placeholder="Neue Frage..." required style="flex:1;">
               <select name="q_type" style="width:150px;">
                 <option value="radio">Single Choice</option>
                 <option value="checkbox">Multiple Choice</option>
               </select>
               <button type="submit" name="add_question" class="modern-btn secondary small"><i class="fa fa-plus"></i> Frage</button>
             </form>
          </div>

          <!-- Questions & Options -->
          <?php
          $questions = $pdo->prepare("SELECT * FROM survey_questions WHERE survey_id = ?");
          $questions->execute([$s['id']]);
          while ($q = $questions->fetch()):
          ?>
            <div class="w3-padding w3-margin-bottom" style="background: rgba(0,0,0,0.2); border-radius:8px;">
               <div style="display:flex; justify-content:space-between;">
                 <b>Frage: <?php echo h($q['question_text']); ?> (<?php echo $q['type']; ?>)</b>
                 <form action="./umfrage.php" method="post">
                   <input type="hidden" name="q_id" value="<?php echo $q['id']; ?>">
                   <button type="submit" name="delete_question" class="w3-text-red w3-transparent" style="border:none; cursor:pointer;"><i class="fa fa-times"></i></button>
                 </form>
               </div>
               
               <div class="w3-padding-small">
                 <?php
                 $options = $pdo->prepare("SELECT * FROM survey_options WHERE question_id = ?");
                 $options->execute([$q['id']]);
                 while ($o = $options->fetch()):
                 ?>
                   <div style="display:flex; justify-content:space-between; font-size:13px; margin: 5px 0;">
                     <span>- <?php echo h($o['option_text']); ?> (<b><?php echo $o['votes']; ?> Votes</b>)</span>
                     <form action="./umfrage.php" method="post">
                       <input type="hidden" name="o_id" value="<?php echo $o['id']; ?>">
                       <button type="submit" name="delete_option" class="w3-text-muted w3-transparent" style="border:none; cursor:pointer;"><i class="fa fa-times"></i></button>
                     </form>
                   </div>
                 <?php endwhile; ?>
                 
                 <!-- Add Option form -->
                 <form action="./umfrage.php" method="post" style="display:flex; gap:10px; margin-top:10px;">
                    <input type="hidden" name="q_id" value="<?php echo $q['id']; ?>">
                    <input type="text" name="o_text" placeholder="Antwortmöglichkeit..." required style="flex:1; padding:5px 10px !important;">
                    <button type="submit" name="add_option" class="modern-btn secondary small" style="padding: 5px 15px !important;">+</button>
                 </form>
               </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

<?php 
require_once __DIR__ . '/impressum.php';
require __DIR__ . '/templates/footer.php'; 
?>
