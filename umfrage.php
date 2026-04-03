<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/Database.php';

requireAdmin();
$pdo = Database::getInstance()->getConnection();

$hasUmfrage = false;
$msg = "";

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

$hasUmfrage = ($pdo->query("SELECT COUNT(*) FROM umfrage")->fetchColumn() > 0);
$beginnStr = "";
$endStr = "";
if ($hasUmfrage) {
    if ($row = $pdo->query("SELECT * FROM umfrage LIMIT 1")->fetch()) {
         $beginnStr = (new DateTime($row['beginn']))->format('d.m.y');
         $endStr = (new DateTime($row['ende']))->format('d.m.y');
    }
}

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
        renderUmfrageErgebnis($pdo, 'Vegetarisch', 'Vegetarisch');
    } else {
        echo "<div class='modern-card w3-center'><h2 class='w3-text-white'>Keine Ergebnisse bisher vorhanden.</h2></div>";
    }
    ?>
  </div>

<?php 
require_once __DIR__ . '/impressum.php';
require __DIR__ . '/templates/footer.php'; 
?>
