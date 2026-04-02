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
  <a href="#vollkost" class="w3-bar-item w3-button w3-padding-large w3-black">
    <i class="fa fa-asterisk w3-xxlarge"></i><p>Springe zu Vollkost</p>
  </a>
  <a href="#vegetarisch" class="w3-bar-item w3-button w3-padding-large w3-black">
    <i class="fa fa-asterisk w3-xxlarge"></i><p>Springe zu Vegetarisch</p>
  </a>
';
if (!isset($_SESSION['wunschauswahl'])) {
    $sidebarHtml .= '
    <button type="submit" form="my-form" class="w3-bar-item w3-button w3-padding-large w3-hover-black">
      <i class="fa fa-check w3-xxlarge" style="color: green"></i><p>Vollkost abstimmen</p>
    </button>';
}
if (!isset($_SESSION['wunschauswahl2'])) {
    $sidebarHtml .= '
    <button type="submit" form="my-form2" class="w3-bar-item w3-button w3-padding-large w3-hover-black">
      <i class="fa fa-check w3-xxlarge" style="color: blue"></i><p>Vegetarisch abstimmen</p>
    </button>';
}

$sidebarHtml .= '
  <form action="./index.php" method="post" style="margin:0;">
    <input type="hidden" name="logout">
    <button type="submit" class="w3-bar-item w3-button w3-padding-large w3-hover-black w3-block">
      <i class="fa fa-home w3-xxlarge"></i><p>STARTSEITE</p>
    </button>
  </form>
';

$navbarSmallHtml = '
    <a href="#" class="w3-bar-item w3-button" style="width:25% !important">Wunschspeisen</a>
';

require __DIR__ . '/templates/header.php';
?>

  <header class="w3-container w3-padding-32 w3-center w3-black" id="eingabe">
    <h1 class="w3-jumbo">Eingabe Wunschspeisen</h1>
  </header>

  <?php if (!empty($messages)): ?>
      <?php echo $messages; ?>
  <?php else: ?>
      <form action='./wunschTabelle.php#eingabe' method='post'>
        <p><input class='w3-input w3-padding-16' type='text' placeholder='Wunschspeise (z.B. Schnitzel)' name='name' required></p>
        <p>
          <select class='w3-input w3-padding-16' name='beilage' required>
            <option value='' disabled selected>Beilage auswählen</option>
            <?php
            $stmt = $pdo->query("SELECT * FROM beilagen ORDER BY beilage_art, beilage_name");
            while ($row = $stmt->fetch()) {
                echo "<option value='" . h($row['beilage_name']) . "'>" . h($row['beilage_art']) . " - " . h($row['beilage_name']) . "</option>";
            }
            ?>
          </select>
        </p>
        <p>
          <select class='w3-input w3-padding-16' name='art' required>
            <option value='' disabled selected>Speiseart auswählen</option>
            <option value='Vollkost'>Vollkost</option>
            <option value='Vegetarisch'>Vegetarisch</option>
          </select>
        </p>
        <p>
          <select class='w3-input w3-padding-16' name='kategorie' required>
            <option value='' disabled selected>Kategorie auswählen</option>
            <option value='-'>-</option>
            <option value='Fleisch'>Fleisch</option>
            <option value='Fisch'>Fisch</option>
            <option value='Süßes'>Süßes</option>
          </select>
        </p>
        <p>
          <button class='w3-button w3-light-grey w3-padding-large' type='submit'>
            <i class='fa fa-paper-plane'></i> Abschicken
          </button>
        </p>
      </form>
  <?php endif; ?>

  <hr>

  <!-- VOLLKOST WUNSCHSPEISEN -->
  <div id="vollkost">
    <header class="w3-container w3-padding-32 w3-center w3-black">
      <h1 class="w3-jumbo">Vollkost Wunschspeisen</h1>
    </header>

    <input type="text" id="myInput" onkeyup="myFunction('myInput', 'myTable')" placeholder="Suche nach Speise..." class="w3-input w3-border w3-padding-16 w3-marginBottom">

    <form id="my-form" action="./wunschTabelle.php#vollkost" method="post">
      <table class="w3-table-all w3-large w3-pale-green" id="myTable">
        <tr class="w3-green">
          <th style="width:5%;">Platz</th>
          <th style="width:10%;">Auswahl</th>
          <th style="width:10%;">Stimmen</th>
          <th style="width:15%;">Art</th>
          <th style="width:15%;">Kategorie</th>
          <th style="width:45%;">Speise</th>
        </tr>
        <?php
        $platz = 1;
        $stmt = $pdo->query("SELECT * FROM wunschspeisen WHERE wunschspeise_art='Vollkost' ORDER BY wunschspeise_anzahl DESC, wunschspeise_kategorie ASC");
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            echo "<td>" . $platz++ . "</td>";
            
            $disabled = isset($_SESSION['wunschauswahl']) ? "disabled" : "";
            $checked = (isset($_SESSION['wunschauswahl']) && $_SESSION['wunschauswahl'] == $row['wunschspeise_nr']) ? "checked" : "";
            
            echo "<td class='w3-center'><input type='radio' name='wunschauswahl' value='" . $row['wunschspeise_nr'] . "' $disabled $checked></td>";
            echo "<td>" . h($row['wunschspeise_anzahl']) . "</td>";
            echo "<td>" . h($row['wunschspeise_art']) . "</td>";
            echo "<td>" . h($row['wunschspeise_kategorie']) . "</td>";
            echo "<td>" . h($row['wunschspeise_name']) . "</td>";
            echo "</tr>";
        }
        ?>
      </table>
      <br>
      <?php if (!isset($_SESSION['wunschauswahl'])): ?>
        <button class='w3-button w3-jumbo w3-green w3-padding-large' type='submit'>
          <i class='fa fa-paper-plane'></i> Abstimmen
        </button>
      <?php else: ?>
        <p class="w3-text-green">Du hast bereits für Vollkost abgestimmt.</p>
      <?php endif; ?>
    </form>
  </div>

  <hr>

  <!-- VEGETARISCH WUNSCHSPEISEN -->
  <div id="vegetarisch">
    <header class="w3-container w3-padding-32 w3-center w3-black">
      <h1 class="w3-jumbo">Vegetarische Wunschspeisen</h1>
    </header>

    <input type="text" id="myInput2" onkeyup="myFunction('myInput2', 'myTable2')" placeholder="Suche nach Speise..." class="w3-input w3-border w3-padding-16 w3-marginBottom">

    <form id="my-form2" action="./wunschTabelle.php#vegetarisch" method="post">
      <table class="w3-table-all w3-large w3-pale-blue" id="myTable2">
        <tr class="w3-blue">
          <th style="width:5%;">Platz</th>
          <th style="width:10%;">Auswahl</th>
          <th style="width:10%;">Stimmen</th>
          <th style="width:15%;">Art</th>
          <th style="width:15%;">Kategorie</th>
          <th style="width:45%;">Speise</th>
        </tr>
        <?php
        $platz = 1;
        $stmt = $pdo->query("SELECT * FROM wunschspeisen WHERE wunschspeise_art='Vegetarisch' ORDER BY wunschspeise_anzahl DESC, wunschspeise_kategorie ASC");
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            echo "<td>" . $platz++ . "</td>";
            
            $disabled = isset($_SESSION['wunschauswahl2']) ? "disabled" : "";
            $checked = (isset($_SESSION['wunschauswahl2']) && $_SESSION['wunschauswahl2'] == $row['wunschspeise_nr']) ? "checked" : "";
            
            echo "<td class='w3-center'><input type='radio' name='wunschauswahl2' value='" . $row['wunschspeise_nr'] . "' $disabled $checked></td>";
            echo "<td>" . h($row['wunschspeise_anzahl']) . "</td>";
            echo "<td>" . h($row['wunschspeise_art']) . "</td>";
            echo "<td>" . h($row['wunschspeise_kategorie']) . "</td>";
            echo "<td>" . h($row['wunschspeise_name']) . "</td>";
            echo "</tr>";
        }
        ?>
      </table>
      <br>
      <?php if (!isset($_SESSION['wunschauswahl2'])): ?>
        <button class='w3-button w3-jumbo w3-blue w3-padding-large' type='submit'>
          <i class='fa fa-paper-plane'></i> Abstimmen
        </button>
      <?php else: ?>
        <p class="w3-text-blue">Du hast bereits für Vegetarisch abgestimmt.</p>
      <?php endif; ?>
    </form>
  </div>

  <style>.w3-marginBottom { margin-bottom: 16px; }</style>

  <script>
  function myFunction(inputId, tableId) {
    var input = document.getElementById(inputId);
    var filter = input.value.toUpperCase();
    var table = document.getElementById(tableId);
    var tr = table.getElementsByTagName("tr");
    
    for (var i = 1; i < tr.length; i++) { // Skip header row
      var display = false;
      // Check columns 3 (Art), 4 (Kategorie), 5 (Speise name)
      for (var col = 3; col <= 5; col++) {
          var td = tr[i].getElementsByTagName("td")[col];
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
