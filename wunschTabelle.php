<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/Database.php';

initSession();
$pdo = Database::getInstance()->getConnection();

$messages = "";

// 1. Abstimmung Vollkost
if (isset($_POST['wunschauswahl']) && !isset($_SESSION['wunschauswahl'])) {
    $stmt = $pdo->prepare("UPDATE wunschspeisen SET wunschspeise_anzahl = wunschspeise_anzahl + 1 WHERE wunschspeise_nr = ?");
    if ($stmt->execute([$_POST['wunschauswahl']])) {
        $_SESSION['wunschauswahl'] = $_POST['wunschauswahl'];
    }
}

// 2. Abstimmung Vegetarisch
if (isset($_POST['wunschauswahl2']) && !isset($_SESSION['wunschauswahl2'])) {
    $stmt = $pdo->prepare("UPDATE wunschspeisen SET wunschspeise_anzahl = wunschspeise_anzahl + 1 WHERE wunschspeise_nr = ?");
    if ($stmt->execute([$_POST['wunschauswahl2']])) {
        $_SESSION['wunschauswahl2'] = $_POST['wunschauswahl2'];
    }
}

// 3. Neue Wunschspeise eintragen
if (isset($_POST['name'], $_POST['beilage'], $_POST['art'], $_POST['kategorie'])) {
    $neueSpeiseName = trim($_POST['name']) . " mit " . trim($_POST['beilage']);
    $stmt = $pdo->prepare("INSERT INTO wunschspeisen (wunschspeise_name, wunschspeise_art, wunschspeise_kategorie, wunschspeise_anzahl) VALUES (?, ?, ?, 0)");
    $stmt->execute([$neueSpeiseName, $_POST['art'], $_POST['kategorie']]);
    $messages = "<h1 class='w3-jumbo w3-center w3-text-green'>Vielen Dank für die Eingabe</h1>";
}
$pageTitle = 'Wunschspeisen';

// Sidebar
$sidebarHtml = '
  <a href="javascript:void(0)" onclick="openTab(\'eingabe\', event)" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-pencil w3-xxlarge"></i><p>EINGABE</p>
  </a>
  <a href="javascript:void(0)" onclick="openTab(\'vollkost\', event)" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-cutlery w3-xxlarge"></i><p>VOLLKOST</p>
  </a>
  <a href="javascript:void(0)" onclick="openTab(\'vegetarisch\', event)" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-leaf w3-xxlarge"></i><p>VEGETARISCH</p>
  </a>
  <a href="./index.php" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-home w3-xxlarge"></i><p>STARTSEITE</p>
  </a>
';

$navbarSmallHtml = '
    <a href="javascript:void(0)" onclick="openTab(\'eingabe\', event)" class="w3-bar-item w3-button" style="width:25% !important">NEU</a>
    <a href="javascript:void(0)" onclick="openTab(\'vollkost\', event)" class="w3-bar-item w3-button" style="width:25% !important">VOLL</a>
    <a href="javascript:void(0)" onclick="openTab(\'vegetarisch\', event)" class="w3-bar-item w3-button" style="width:25% !important">VEGI</a>
    <a href="./index.php" class="w3-bar-item w3-button" style="width:25% !important">HOME</a>
';

require __DIR__ . '/templates/header.php';
?>

<div id="eingabe" class="tab-content active">
  <header class="hero-header w3-center">
    <h1>Wunschspeise einreichen</h1>
    <p class="w3-text-muted">Habt Ihr eine Idee für den Speiseplan? Schickt sie uns!</p>
  </header>

  <div class="page-container">
    <?php if (!empty($messages)): ?>
        <div class="modern-card w3-center">
          <?php echo $messages; ?>
          <br>
          <a href="./wunschTabelle.php#eingabe" class="modern-btn secondary">Weitere Speise vorschlagen</a>
        </div>
    <?php else: ?>
      <div class="modern-card" style="max-width: 600px; margin: 0 auto;">
        <h2 class="w3-text-white" style="margin-top:0">Neuer Vorschlag</h2>
        <form action='./wunschTabelle.php#eingabe' method='post'>
          <p><input type='text' placeholder='Speise (z.B. Schnitzel)' name='name' required></p>
          <p>
            <select name='beilage' required>
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
            <select name='art' required>
              <option value='' disabled selected>Speiseart wählen</option>
              <option value='Vollkost'>Vollkost</option>
              <option value='Vegetarisch'>Vegetarisch</option>
            </select>
          </p>
          <p>
            <select name='kategorie' required>
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

<div id="vollkost" class="tab-content">
  <header class="hero-header w3-center">
    <h1>Vollkost Wunschliste</h1>
    <p class="w3-text-muted">Hier könnt Ihr für Eure Lieblingsgerichte abstimmen.</p>
  </header>

  <div class="page-container">
    <div class="modern-card">
      <h2 class="w3-text-white" style="margin-top:0">Abstimmung Vollkost</h2>
      <div class="w3-margin-bottom">
        <input type="text" id="myInput" onkeyup="myFunction('myInput', 'myTable')" placeholder="Liste durchsuchen..." style="max-width: 400px;">
      </div>

      <form id="my-form" action="./wunschTabelle.php#vollkost" method="post">
        <div class="w3-responsive">
          <table class="modern-table" id="myTable">
            <tr>
              <th style="width:5%;">#</th>
              <th style="width:10%; text-align:center;">Wahl</th>
              <th style="width:10%;">Votes</th>
              <th style="width:15%;">Kategorie</th>
              <th style="width:50%;">Speise</th>
            </tr>
            <?php
            $platz = 1;
            $stmt = $pdo->query("SELECT * FROM wunschspeisen WHERE wunschspeise_art='Vollkost' ORDER BY wunschspeise_anzahl DESC, wunschspeise_kategorie ASC");
            while ($row = $stmt->fetch()) {
                echo "<tr>";
                echo "<td>" . $platz++ . "</td>";
                
                $disabled = isset($_SESSION['wunschauswahl']) ? "disabled" : "";
                $checked = (isset($_SESSION['wunschauswahl']) && $_SESSION['wunschauswahl'] == $row['wunschspeise_nr']) ? "checked" : "";
                
                echo "<td class='w3-center'><div class='modern-radio-container'><input type='radio' name='wunschauswahl' value='" . $row['wunschspeise_nr'] . "' $disabled $checked></div></td>";
                echo "<td>" . h($row['wunschspeise_anzahl']) . "</td>";
                echo "<td>" . h($row['wunschspeise_kategorie']) . "</td>";
                echo "<td>" . h($row['wunschspeise_name']) . "</td>";
                echo "</tr>";
            }
            ?>
          </table>
        </div>
        <br>
        <?php if (!isset($_SESSION['wunschauswahl'])): ?>
          <button class='modern-btn jumbo' type='submit' style="width:100%">
            <i class='fa fa-check'></i> Stimme abgeben
          </button>
        <?php else: ?>
          <div class="w3-panel w3-blue w3-round">
            <p><i class="fa fa-info-circle"></i> Du hast bereits für Vollkost abgestimmt.</p>
          </div>
        <?php endif; ?>
      </form>
    </div>
  </div>
</div>

<div id="vegetarisch" class="tab-content">
  <header class="hero-header w3-center">
    <h1>Vegetarische Wunschliste</h1>
    <p class="w3-text-muted">Hier könnt Ihr für fleischlose Gerichte abstimmen.</p>
  </header>

  <div class="page-container">
    <div class="modern-card">
      <h2 class="w3-text-white" style="margin-top:0">Abstimmung Vegetarisch</h2>
      <div class="w3-margin-bottom">
        <input type="text" id="myInput2" onkeyup="myFunction('myInput2', 'myTable2')" placeholder="Liste durchsuchen..." style="max-width: 400px;">
      </div>

      <form id="my-form2" action="./wunschTabelle.php#vegetarisch" method="post">
        <div class="w3-responsive">
          <table class="modern-table" id="myTable2">
            <tr>
              <th style="width:5%;">#</th>
              <th style="width:10%; text-align:center;">Wahl</th>
              <th style="width:10%;">Votes</th>
              <th style="width:15%;">Kategorie</th>
              <th style="width:50%;">Speise</th>
            </tr>
            <?php
            $platz = 1;
            $stmt = $pdo->query("SELECT * FROM wunschspeisen WHERE wunschspeise_art='Vegetarisch' ORDER BY wunschspeise_anzahl DESC, wunschspeise_kategorie ASC");
            while ($row = $stmt->fetch()) {
                echo "<tr>";
                echo "<td>" . $platz++ . "</td>";
                
                $disabled = isset($_SESSION['wunschauswahl2']) ? "disabled" : "";
                $checked = (isset($_SESSION['wunschauswahl2']) && $_SESSION['wunschauswahl2'] == $row['wunschspeise_nr']) ? "checked" : "";
                
                echo "<td class='w3-center'><div class='modern-radio-container'><input type='radio' name='wunschauswahl2' value='" . $row['wunschspeise_nr'] . "' $disabled $checked></div></td>";
                echo "<td>" . h($row['wunschspeise_anzahl']) . "</td>";
                echo "<td>" . h($row['wunschspeise_kategorie']) . "</td>";
                echo "<td>" . h($row['wunschspeise_name']) . "</td>";
                echo "</tr>";
            }
            ?>
          </table>
        </div>
        <br>
        <?php if (!isset($_SESSION['wunschauswahl2'])): ?>
          <button class='modern-btn jumbo' type='submit' style="width:100%">
            <i class='fa fa-check'></i> Stimme abgeben
          </button>
        <?php else: ?>
          <div class="w3-panel w3-blue w3-round">
            <p><i class="fa fa-info-circle"></i> Du hast bereits für Vegetarisch abgestimmt.</p>
          </div>
        <?php endif; ?>
      </form>
    </div>
  </div>
</div>

<script>
function myFunction(inputId, tableId) {
    var input = document.getElementById(inputId);
    var filter = input.value.toUpperCase();
    var table = document.getElementById(tableId);
    var tr = table.getElementsByTagName("tr");
    
    for (var i = 1; i < tr.length; i++) {
      var display = false;
      var colsToCheck = [3, 4]; // Kategorie, Name
      for (var j = 0; j < colsToCheck.length; j++) {
          var td = tr[i].getElementsByTagName("td")[colsToCheck[j]];
          if (td) {
            var txtValue = td.textContent || td.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
              display = true;
              break;
            }
          }
      }
      tr[i].style.display = display ? "" : "none";
    }
}
</script>

<?php 
require_once __DIR__ . '/impressum.php';
require __DIR__ . '/templates/footer.php'; 
?>
