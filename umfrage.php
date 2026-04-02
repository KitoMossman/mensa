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
  <a href="./login.php" class="w3-bar-item w3-button w3-padding-large w3-black">
    <i class="fa fa-arrow-left w3-xxlarge"></i><p>ZURÜCK</p>
  </a>
';
$navbarSmallHtml = '
    <a href="./login.php" class="w3-bar-item w3-button" style="width:100% !important">ZURÜCK</a>
';

require __DIR__ . '/templates/header.php';
?>

  <!-- Header/Home -->
  <header class="w3-container w3-padding-32 w3-center w3-black" id="home">
    <h1 class="w3-jumbo">Umfrage Verwaltung</h1>
  </header>

  <center>
    <?php echo $msg; ?>
    <?php if ($hasUmfrage): ?>
      <h2 class='w3-xxlarge'>Aktuell: Umfrage von <?php echo h($beginnStr); ?> bis <?php echo h($endStr); ?></h2>
    <?php else: ?>
      <h2 class='w3-xxlarge'>Aktuell: keine Umfrage</h2>
    <?php endif; ?>

    <br><br>
    
    <form action='./umfrage.php' method='post'>
      <label for='beginDate' class='w3-xlarge'>Beginn: </label>
      <input type='date' id='beginDate' name='beginDate' class='w3-xlarge' required>
      <label for='endDate' class='w3-xlarge'> Ende: </label>
      <input type='date' id='endDate' name='endDate' class='w3-xlarge' required>
      <br><br>
      <button class='w3-button w3-green w3-padding-large w3-xxlarge' type='submit'>
        <i class='fa fa-play'></i> Starten
      </button>
    </form>
    
    <br><br>

    <form action='./umfrage.php' method='post'>
      <button class='w3-button w3-blue w3-padding-large w3-xxlarge' name='stop' type='submit' onclick="return confirm('Sicher? Aktuelle Ergebnisse werden in die Historie geschrieben und dann zurückgesetzt.');">
        <i class='fa fa-stop'></i> Beenden und Auswerten
      </button>
    </form>
  </center>

  <hr>

  <?php
  function renderUmfrageErgebnis($pdo, $art, $title, $colorClass, $headerClass) {
      echo "<div id='" . strtolower($art) . "'>";
      echo "<header class='w3-container w3-padding-32 w3-center w3-black'>";
      echo "<h1 class='w3-jumbo'>Ergebnis " . h($title) . "</h1>";
      echo "</header>";

      echo "<table class='w3-table-all w3-large $colorClass' id='myTable_" . strtolower($art) . "'>";
      echo "<tr class='$headerClass'>";
      echo "<th style='width:10%;'>Platz</th>";
      echo "<th style='width:15%;'>Anzahl</th>";
      echo "<th style='width:15%;'>Speiseart</th>";
      echo "<th style='width:15%;'>Kategorie</th>";
      echo "<th style='width:45%;'>Speise</th>";
      echo "</tr>";

      $platz = 1;
      $stmt = $pdo->prepare("SELECT * FROM ergebnis_umfrage WHERE wunschspeise_art = ? ORDER BY wunschspeise_anzahl DESC, wunschspeise_kategorie ASC");
      $stmt->execute([$art]);

      while ($row = $stmt->fetch()) {
          echo "<tr>";
          echo "<td>" . $platz++ . "</td>";
          // We remove utf8_encode since the database is already fetching correctly assuming charset was set right
          echo "<td>" . h($row['wunschspeise_anzahl']) . "</td>";
          echo "<td>" . h($row['wunschspeise_art']) . "</td>";
          echo "<td>" . h($row['wunschspeise_kategorie']) . "</td>";
          echo "<td>" . h($row['wunschspeise_name']) . "</td>";
          echo "</tr>";
      }
      echo "</table><br><br></div>";
  }

  // Check if ergebnis_umfrage table exists first
  $tableExists = false;
  try {
      $pdo->query("SELECT 1 FROM ergebnis_umfrage LIMIT 1");
      $tableExists = true;
  } catch (Exception $e) {}

  if ($tableExists) {
      renderUmfrageErgebnis($pdo, 'Vollkost', 'Vollkost Wunschspeisen', 'w3-pale-green', 'w3-green');
      renderUmfrageErgebnis($pdo, 'Vegetarisch', 'Vegetarische Wunschspeisen', 'w3-pale-blue', 'w3-blue');
  } else {
      echo "<center><h2 class='w3-xlarge'>Keine Ergebnisse bisher vorhanden.</h2></center>";
  }
  ?>

<?php 
require_once __DIR__ . '/impressum.php';
require __DIR__ . '/templates/footer.php'; 
?>
