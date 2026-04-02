<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/Database.php';

requireAdmin();
$pdo = Database::getInstance()->getConnection();

$messages = [];

// ======= POST PROCESSING =======

// 1. Speise hinzufügen
if (isset($_POST['speise_name'], $_POST['speise_art'])) {
    $stmt = $pdo->prepare("REPLACE INTO speisen (speise_name, speise_art) VALUES (?, ?)");
    if ($stmt->execute([$_POST['speise_name'], $_POST['speise_art']])) {
        $messages['speise_add'] = "<p class='w3-text-green'>Speise wurde erfolgreich hinzugefügt.</p>";
    }
}

// 2. Speise bearbeiten
if (isset($_POST['edit_id'], $_POST['edit_name'], $_POST['edit_art'])) {
    $stmt = $pdo->prepare("UPDATE speisen SET speise_name = ?, speise_art = ? WHERE speise_nr = ?");
    if ($stmt->execute([$_POST['edit_name'], $_POST['edit_art'], $_POST['edit_id']])) {
        $messages['speise_edit'] = "<p class='w3-text-green'>Speise wurde erfolgreich aktualisiert.</p>";
    } else {
        $messages['speise_edit'] = "<p class='w3-text-red'>Fehler beim Aktualisieren.</p>";
    }
}

// 3. Speise löschen
if (isset($_POST['entfernen_id'])) {
    $stmt = $pdo->prepare("DELETE FROM speisen WHERE speise_nr = ?");
    if ($stmt->execute([$_POST['entfernen_id']])) {
        $messages['speise_del'] = "<p class='w3-text-green'>Speise wurde erfolgreich gelöscht.</p>";
    }
}

// 4. Wochenplan speichern
if (isset($_POST['wochenplan_save']) && isset($_POST['date'])) {
    $pdo->query("DELETE FROM wochenplan");
    $d = new DateTime($_POST['date']);
    
    $arten = ["Vollkost", "Leichte_Vollkost", "Vegetarisch"];
    $tage = ["Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag"];
    
    $stmtInsert = $pdo->prepare("INSERT INTO wochenplan (tag, speise_nr, datum) VALUES (?, ?, ?)");
    
    for ($i = 0; $i < 7; $i++) {
        $tag = $tage[$i];
        $tagDatum = (clone $d)->modify("+$i days")->format('Y-m-d');
        
        foreach ($arten as $art) {
            $postKey = "wochenplan_" . $tag . "_" . $art;
            if (isset($_POST[$postKey]) && $_POST[$postKey] > 0) {
                $stmtInsert->execute([$tag, $_POST[$postKey], $tagDatum]);
            }
        }
    }
    $messages['wochenplan'] = "<div class='w3-panel w3-green'><p>Wochenplan erfolgreich gespeichert!</p></div>";
}

// 5. Wunschplan speichern
if (isset($_POST['wp_action'], $_POST['dateWunsch'])) {
    $datumObj = new DateTime($_POST['dateWunsch']);
    $pdo->query("DELETE FROM wunschplan");
    
    $tage = ["Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag"];
    $artenPost = ["Vollkost", "Leichte_Vollkost", "Vegetarisch"];
    $stmtInsert = $pdo->prepare("INSERT INTO wunschplan (tag, speise_nr, datum) VALUES (?, ?, ?)");

    for ($i = 0; $i < 7; $i++) {
        $tag = $tage[$i];
        $sqlDatum = (clone $datumObj)->modify("+$i days")->format('Y-m-d');
        foreach ($artenPost as $art) {
            $postName = "wunsch_" . $tag . "_" . $art;
            if (isset($_POST[$postName]) && $_POST[$postName] > 0) {
                $stmtInsert->execute([$tag, $_POST[$postName], $sqlDatum]);
            }
        }
    }

    if ($_POST['wp_action'] === 'new') {
        $pdo->query("DELETE FROM abstimmung");
        $messages['wunschplan'] = "<div class='w3-panel w3-red w3-display-container'><p>Wunschplan gespeichert & <b>Abstimmung zurückgesetzt!</b></p></div>";
    } else {
        $messages['wunschplan'] = "<div class='w3-panel w3-green w3-display-container'><p>Wunschplan erfolgreich geändert (Abstimmung behalten).</p></div>";
    }
}
// ======= END POST PROCESSING =======


// Prepare sidebar for templates/header.php
$sidebarHtml = '
  <a href="#" class="w3-bar-item w3-button w3-padding-large w3-black">
    <i class="fa fa-bar-chart w3-xxlarge"></i><p>AUSWERTUNG</p>
  </a>
  <a href="#speisen" class="w3-bar-item w3-button w3-padding-large w3-hover-black">
    <i class="fa fa-bars w3-xxlarge"></i><p>SPEISEN</p>
  </a>
  <a href="#wochenplan" class="w3-bar-item w3-button w3-padding-large w3-hover-black">
    <i class="fa fa-table w3-xxlarge"></i><p>WOCHENPLAN</p>
  </a>
  <a href="#wunschplan" class="w3-bar-item w3-button w3-padding-large w3-hover-black">
    <i class="fa fa-table w3-xxlarge"></i><p>WUNSCHPLAN</p>
  </a>
  <a href="./umfrage.php" class="w3-bar-item w3-button w3-padding-large w3-hover-black">
    <i class="fa fa-line-chart w3-xxlarge"></i><p>UMFRAGE</p>
  </a>
  <form action="./index.php" method="post" style="margin:0;">
    <input type="hidden" name="logout">
    <button type="submit" class="w3-bar-item w3-button w3-padding-large w3-hover-black w3-block">
      <i class="fa fa-lock w3-xxlarge"></i><p>LOGOUT</p>
    </button>
  </form>
';
$navbarSmallHtml = '
    <a href="#" class="w3-bar-item w3-button" style="width:20% !important">AUSWERTUNG</a>
    <a href="#speisen" class="w3-bar-item w3-button" style="width:20% !important">SPEISEN</a>
    <a href="#wochenplan" class="w3-bar-item w3-button" style="width:20% !important">WOCHENPLAN</a>
    <a href="#wunschplan" class="w3-bar-item w3-button" style="width:20% !important">WUNSCHPLAN</a>
    <form action="./index.php" method="post" style="display:inline;"><input type="hidden" name="logout"><button type="submit" class="w3-bar-item w3-button" style="width:20% !important">LOGOUT</button></form>
';

$pageTitle = 'Küche Admin';
// we'll set $closeExtraDiv since login.php has #main and #main2... wait we can just close them normally
require __DIR__ . '/templates/header.php';
?>

  <!-- Header/Home -->
  <header class="w3-container w3-padding-32 w3-center w3-black" id="home">
    <h1 class="w3-jumbo">Küche - Administration</h1>
  </header>

  <!-- AUSWERTUNG -->
  <div class="w3-responsive">
    <table class="w3-table-all w3-text-black">
      <tr class="w3-green w3-center">
        <th>Wochentag</th>
        <th>Speise</th>
        <th>Speiseart</th>
        <th>Datum</th>
        <th>Anzahl</th>
        <th>Prozent</th>
      </tr>

      <?php
      $gesamtAnzahlProTag = ["Montag" => 0, "Dienstag" => 0, "Mittwoch" => 0, "Donnerstag" => 0, "Freitag" => 0, "Samstag" => 0, "Sonntag" => 0];

      $stmtWunsch = $pdo->query("SELECT * FROM wunschplan");
      $wunschplan = $stmtWunsch->fetchAll();

      // Berechne Gesamtzahl pro Tag
      foreach ($wunschplan as $wunschRow) {
          $stmtAbst = $pdo->prepare("SELECT COUNT(*) FROM abstimmung WHERE speise_nr = ?");
          $stmtAbst->execute([$wunschRow['speise_nr']]);
          $cnt = $stmtAbst->fetchColumn();
          $gesamtAnzahlProTag[$wunschRow['tag']] += $cnt;
      }

      $lastTag = "Montag";
      foreach ($wunschplan as $wunschRow) {
          $stmtAbst = $pdo->prepare("SELECT speise_nr, COUNT(*) as cnt FROM abstimmung WHERE speise_nr=? GROUP BY speise_nr");
          $stmtAbst->execute([$wunschRow['speise_nr']]);
          $abstRows = $stmtAbst->fetchAll();
          
          if (!$abstRows) continue; // no votes
          
          foreach ($abstRows as $abstimmungsRow) {
              $stmtSpeise = $pdo->prepare("SELECT * FROM speisen WHERE speise_nr=?");
              $stmtSpeise->execute([$abstimmungsRow['speise_nr']]);
              $speisenRow = $stmtSpeise->fetch();
              if (!$speisenRow) continue;

              if ($wunschRow['tag'] != $lastTag) {
                  echo "<tr><td colspan='6'></td></tr>";
              }
              $lastTag = $wunschRow['tag'];

              $datumFormatted = (new DateTime($wunschRow['datum']))->format('d.m.Y');
              $prozent = ($gesamtAnzahlProTag[$wunschRow['tag']] > 0) ? number_format($abstimmungsRow['cnt'] * 100 / $gesamtAnzahlProTag[$wunschRow['tag']], 0) : 0;

              echo "<tr>";
              echo "<td>" . h($wunschRow['tag']) . "</td>";
              echo "<td>" . h($speisenRow['speise_name']) . "</td>";
              echo "<td>" . h($speisenRow['speise_art']) . "</td>";
              echo "<td>" . h($datumFormatted) . "</td>";
              echo "<td>" . h($abstimmungsRow['cnt']) . "</td>";
              echo "<td>" . $prozent . "%</td>";
              echo "</tr>";
          }
      }
      ?>
    </table>
    <br>
    <a href="./zusatzstoffe.php" class="w3-bar-item w3-button w3-padding-large w3-black" target="_blank">
      <i class="fa fa-asterisk w3-xxlarge"></i><p>Zusatzstoffe</p>
    </a>
    <a href="./nachrichten.php" class="w3-bar-item w3-button w3-padding-large w3-black" target="_blank">
      <i class="fa fa-envelope-open w3-xxlarge"></i><p>Nachrichten</p>
    </a>
  </div>

  <hr>

  <!-- SPEISEN VERWALTEN -->
  <div class="w3-padding-64 w3-content" id="speisen">
    
    <div class="w3-row-padding">
        <!-- Add Speise -->
        <div class="w3-third">
            <h3 class="w3-center">Speise hinzufügen</h3>
            <?php if (isset($messages['speise_add'])) echo $messages['speise_add']; ?>
            <form action="#speisen" method="post">
                <input class="w3-input w3-marginBottom" type="text" name="speise_name" placeholder="Speise" required>
                <select class="w3-input w3-marginBottom" name="speise_art" required>
                    <option value="Vollkost">Vollkost</option>
                    <option value="Leichte Vollkost">Leichte Vollkost</option>
                    <option value="Vegetarisch">Vegetarisch</option>
                </select>
                <button class="w3-button w3-blue w3-block" type="submit"><i class="fa fa-plus"></i> Hinzufügen</button>
            </form>
        </div>

        <!-- Edit Speise -->
        <div class="w3-third">
            <h3 class="w3-center">Speise bearbeiten</h3>
            <?php if (isset($messages['speise_edit'])) echo $messages['speise_edit']; ?>
            <form action="#speisen" method="post">
                <select class="w3-input w3-marginBottom" name="edit_id" id="editSelect" required onchange="loadDishData()">
                    <option value="" disabled selected>-- Bitte wählen --</option>
                    <?php
                    $speisenList = $pdo->query("SELECT * FROM speisen ORDER BY speise_art, speise_name")->fetchAll();
                    foreach ($speisenList as $row) {
                        $safeName = htmlspecialchars($row['speise_name'], ENT_QUOTES);
                        $safeArt = htmlspecialchars($row['speise_art'], ENT_QUOTES);
                        echo "<option value='".$row['speise_nr']."' data-name='".$safeName."' data-art='".$safeArt."'>".h($row['speise_art'])." - ".h($row['speise_name'])."</option>";
                    }
                    ?>
                </select>
                <select class="w3-input w3-marginBottom" name="edit_art" id="editArtSelect" required>
                    <option value="Vollkost">Vollkost</option>
                    <option value="Leichte Vollkost">Leichte Vollkost</option>
                    <option value="Vegetarisch">Vegetarisch</option>
                </select>
                <input class="w3-input w3-marginBottom" type="text" name="edit_name" id="editNameInput" placeholder="Name..." required>
                <button class="w3-button w3-orange w3-text-white w3-block" type="submit"><i class="fa fa-pencil"></i> Ändern</button>
            </form>
        </div>

        <!-- Delete Speise -->
        <div class="w3-third">
            <h3 class="w3-center">Speise löschen</h3>
            <?php if (isset($messages['speise_del'])) echo $messages['speise_del']; ?>
            <form action="#speisen" method="post">
                <select class="w3-input w3-marginBottom" name="entfernen_id" required>
                    <option value="" disabled selected>-- Bitte wählen --</option>
                    <?php
                    foreach ($speisenList as $row) {
                        echo "<option value='".$row['speise_nr']."'>".h($row['speise_art'])." - ".h($row['speise_name'])."</option>";
                    }
                    ?>
                </select>
                <p>&nbsp;</p> <!-- spacer -->
                <button class="w3-button w3-red w3-block" type="submit"><i class="fa fa-trash"></i> Löschen</button>
            </form>
        </div>
    </div>
  </div>

  <script>
  function loadDishData() {
      var selectBox = document.getElementById("editSelect");
      var selectedOption = selectBox.options[selectBox.selectedIndex];
      if (selectedOption) {
          var dishName = selectedOption.getAttribute('data-name');
          var dishArt = selectedOption.getAttribute('data-art');
          if(dishName) document.getElementById("editNameInput").value = dishName;
          if(dishArt) document.getElementById("editArtSelect").value = dishArt;
      }
  }
  </script>
  
  <style>.w3-marginBottom { margin-bottom: 16px; }</style>

</div>
<!-- End #main, open #main2 to keep original layout style somewhat -->
<div class="w3-padding-large" id="main2">

  <!-- WOCHENPLAN -->
  <header class="w3-container w3-padding-32 w3-center w3-black" id="wochenplan">
    <h1 class="w3-jumbo">Aktuellen Wochenplan erstellen</h1>
  </header>

  <?php if (isset($messages['wochenplan'])) echo $messages['wochenplan']; ?>

  <div class="w3-responsive">
    <form action="#wochenplan" method="post">
      <?php
      $dateValue = date('Y-m-d');
      if (isset($_POST['load_from_wunsch'])) {
          $dRow = $pdo->query("SELECT datum FROM wunschplan ORDER BY datum ASC LIMIT 1")->fetch();
          if ($dRow) $dateValue = (new DateTime($dRow['datum']))->format('Y-m-d');
      } elseif (isset($_POST['date'])) {
          $dateValue = $_POST['date']; 
      } else {
          $dRow = $pdo->query("SELECT datum FROM wochenplan ORDER BY tag_id ASC LIMIT 1")->fetch();
          if ($dRow) $dateValue = (new DateTime($dRow['datum']))->format('Y-m-d');
      }

      echo "<center><label for='date'>Beginn der Woche: </label>";
      echo "<input type='date' id='date' name='date' value='".h($dateValue)."' required></center><br>";
      
      $sourceTable = "wochenplan"; 
      if (isset($_POST['load_from_wunsch'])) {
          $sourceTable = "wunschplan"; 
          echo "<div class='w3-panel w3-blue'><p>Daten aus <b>Wunschplan</b> importiert. Bitte bestätigen.</p></div>";
      }
      ?>

      <table class="w3-table-all w3-large w3-text-black">
        <tr class="w3-green w3-center">
          <th class="w3-center">Wochentag</th>
          <th class="w3-center">Vollkost</th>
          <th class="w3-center">Leichte Vollkost</th>
          <th class="w3-center">Vegetarisch</th>
        </tr>
        <?php
        $arten = ["Vollkost", "Leichte Vollkost", "Vegetarisch"];
        $tage = ["Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag"];

        // Preload all dishes
        $allDishes = $pdo->query("SELECT * FROM speisen ORDER BY speise_name")->fetchAll();

        foreach ($tage as $tag) {
            echo "<tr><td><b>$tag</b></td>";
            foreach ($arten as $art) {
                echo "<td>";
                $selectName = "wochenplan_" . $tag . "_" . str_replace(" ", "_", $art);
                
                if (isset($_POST['wochenplan_save'])) {
                    // Just displaying saved value
                    $speiseNr = isset($_POST[$selectName]) ? $_POST[$selectName] : 0;
                    echo "<select class='w3-input w3-padding-16' disabled>";
                    $found = "---";
                    foreach($allDishes as $d) { if ($d['speise_nr'] == $speiseNr) $found = $d['speise_name']; }
                    echo "<option selected>".h($found)."</option></select>";
                } else {
                    echo "<select class='w3-input w3-padding-16' name='".$selectName."'>";
                    echo "<option value=0></option>";
                    
                    // find selected
                    $stmtCheck = $pdo->prepare("SELECT speise_nr FROM {$sourceTable} WHERE tag=? AND speise_nr IN (SELECT speise_nr FROM speisen WHERE speise_art=?)");
                    $stmtCheck->execute([$tag, $art]);
                    $selectedId = $stmtCheck->fetchColumn();

                    foreach ($allDishes as $d) {
                        if ($d['speise_art'] === $art) {
                            $sel = ($d['speise_nr'] == $selectedId) ? "selected" : "";
                            echo "<option value='".$d['speise_nr']."' $sel>".h($d['speise_name'])."</option>";
                        }
                    }
                    echo "</select>";
                }
                echo "</td>";
            }
            echo "</tr>";
        }
        ?>
      </table>

      <?php if (!isset($_POST['wochenplan_save'])): ?>
      <div class="w3-row-padding w3-padding-16">
          <div class="w3-half">
            <button class="w3-button w3-indigo w3-text-white w3-block w3-padding-large" type="submit" name="load_from_wunsch">
                <i class="fa fa-refresh"></i> Aus Wunschplan laden
            </button>
          </div>
          <div class="w3-half">
            <button class="w3-button w3-green w3-block w3-padding-large" type="submit" name="wochenplan_save">
                <i class="fa fa-check"></i> Bestätigen & Speichern
            </button>
          </div>
      </div>
      <?php endif; ?>
    </form>
  </div>

  <!-- WUNSCHPLAN -->
  <header class="w3-context w3-padding-32 w3-center w3-black" id="wunschplan">
    <h1 class="w3-jumbo">Wunschplan Verwaltung</h1>
  </header>

  <?php if (isset($messages['wunschplan'])) echo $messages['wunschplan']; ?>

  <form id='wunschForm' action='#wunschplan' method='post'>
    <?php
    $wpDateValue = "";
    $dRow = $pdo->query("SELECT datum FROM wunschplan ORDER BY datum ASC LIMIT 1")->fetch();
    if ($dRow) $wpDateValue = (new DateTime($dRow['datum']))->format('Y-m-d');
    ?>

    <center><div class='w3-padding'>
      <label for='dateWunsch'>Start-Datum der Woche: </label>
      <input type='date' id='dateWunsch' name='dateWunsch' value='<?php echo h($wpDateValue); ?>' required>
    </div></center>

    <div class="w3-responsive">
      <table class="w3-table-all w3-large w3-text-black">
      <tr class="w3-blue w3-center">
        <th class="w3-center">Wochentag</th>
        <th class="w3-center">Vollkost</th>
        <th class="w3-center">Leichte Vollkost</th>
        <th class="w3-center">Vegetarisch</th>
      </tr>

      <?php
      foreach ($tage as $tag) {
          echo "<tr><td><b>" . h($tag) . "</b></td>";
          foreach ($arten as $art) {
              echo "<td>";
              echo "<select class='w3-input w3-padding-16' name='wunsch_" . $tag . "_" . str_replace(' ', '_', $art) . "'>";
              echo "<option value='0'></option>";

              $stmtCheck = $pdo->prepare("SELECT speise_nr FROM wunschplan WHERE tag=? AND speise_nr IN (SELECT speise_nr FROM speisen WHERE speise_art=?)");
              $stmtCheck->execute([$tag, $art]);
              $selectedID = $stmtCheck->fetchColumn();

              foreach ($allDishes as $d) {
                  if ($d['speise_art'] === $art) {
                      $isSel = ($d['speise_nr'] == $selectedID) ? "selected" : "";
                      echo "<option value='".$d['speise_nr']."' $isSel>".h($d['speise_name'])."</option>";
                  }
              }
              echo "</select></td>";
          }
          echo "</tr>";
      }
      ?>
      </table>
    </div>

    <div class="w3-row-padding w3-padding-16">
      <div class="w3-third">
          <button class="w3-button w3-white w3-border w3-block w3-padding-large" type="button" onclick="clearForm()">
              <i class="fa fa-eraser"></i> Felder leeren
          </button>
      </div>
      <div class="w3-third">
          <button class="w3-button w3-orange w3-text-white w3-block w3-padding-large" type="submit" name="wp_action" value="update">
              <i class="fa fa-pencil"></i> Wunschplan ändern <span class="w3-small">(Abstimmung behalten)</span>
          </button>
      </div>
      <div class="w3-third">
          <button class="w3-button w3-red w3-block w3-padding-large" type="submit" name="wp_action" value="new" onclick="return confirm('Sicher? Die aktuelle Abstimmung wird gelöscht!');">
              <i class="fa fa-trash"></i> Speichern & Reset <span class="w3-small">(Abstimmung LÖSCHEN)</span>
          </button>
      </div>
    </div>

    <script>
    function clearForm() {
        var selects = document.querySelectorAll("#wunschForm select");
        for (var i = 0; i < selects.length; i++) {
            selects[i].value = "0"; 
            selects[i].selectedIndex = 0;
        }
        var d = document.getElementById("dateWunsch");
        if(d) d.value = "";
    }
    </script>
  </form>

<?php 
require_once __DIR__ . '/impressum.php';
require __DIR__ . '/templates/footer.php';
?>