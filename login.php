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
  <a href="javascript:void(0)" onclick="openTab(\'auswertung\', event)" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-bar-chart w3-xxlarge"></i><p>AUSWERTUNG</p>
  </a>
  <a href="javascript:void(0)" onclick="openTab(\'speisen\', event)" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-bars w3-xxlarge"></i><p>SPEISEN</p>
  </a>
  <a href="javascript:void(0)" onclick="openTab(\'wochenplan\', event)" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-calendar w3-xxlarge"></i><p>WOCHENPLAN</p>
  </a>
  <a href="javascript:void(0)" onclick="openTab(\'wunschplan\', event)" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-pencil-square-o w3-xxlarge"></i><p>WUNSCHPLAN</p>
  </a>
  <a href="./umfrage.php" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-line-chart w3-xxlarge"></i><p>UMFRAGE</p>
  </a>
  <form action="./index.php" method="post" style="margin:0;">
    <input type="hidden" name="logout">
    <button type="submit" class="w3-bar-item w3-button w3-padding-large w3-block">
      <i class="fa fa-lock w3-xxlarge"></i><p>LOGOUT</p>
    </button>
  </form>
';
$navbarSmallHtml = '
    <a href="javascript:void(0)" onclick="openTab(\'auswertung\', event)" class="w3-bar-item w3-button" style="width:20% !important">INFO</a>
    <a href="javascript:void(0)" onclick="openTab(\'speisen\', event)" class="w3-bar-item w3-button" style="width:20% !important">SPEISEN</a>
    <a href="javascript:void(0)" onclick="openTab(\'wochenplan\', event)" class="w3-bar-item w3-button" style="width:20% !important">WOCHE</a>
    <a href="javascript:void(0)" onclick="openTab(\'wunschplan\', event)" class="w3-bar-item w3-button" style="width:20% !important">WUNSCH</a>
    <form action="./index.php" method="post" style="display:inline;"><input type="hidden" name="logout"><button type="submit" class="w3-bar-item w3-button" style="width:20% !important">LOGOUT</button></form>
';

$pageTitle = 'Küche Admin';
// we'll set $closeExtraDiv since login.php has #main and #main2... wait we can just close them normally
require __DIR__ . '/templates/header.php';
?>

<div id="auswertung" class="tab-content active">
  <!-- Header/Home -->
  <header class="hero-header w3-center">
    <h1 class="w3-jumbo">Küche - Administration</h1>
    <p class="w3-text-muted">Hier sehen Sie die aktuellen Wünsche und Statistiken.</p>
  </header>

  <div class="page-container">
    <div class="modern-card">
      <h2 class="w3-text-white" style="margin-top:0">Aktuelle Auswertung</h2>
      <div class="w3-responsive">
        <table class="modern-table">
          <tr>
            <th>Wochentag</th>
            <th>Speise</th>
            <th>Art</th>
            <th>Datum</th>
            <th>Anzahl</th>
            <th>%</th>
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
                      echo "<tr><td colspan='6' style='height:10px; border-bottom:none;'></td></tr>";
                  }
                  $lastTag = $wunschRow['tag'];

                  $datumFormatted = (new DateTime($wunschRow['datum']))->format('d.m.Y');
                  $prozent = ($gesamtAnzahlProTag[$wunschRow['tag']] > 0) ? number_format($abstimmungsRow['cnt'] * 100 / $gesamtAnzahlProTag[$wunschRow['tag']], 0) : 0;

                  echo "<tr>";
                  echo "<td><b>" . h($wunschRow['tag']) . "</b></td>";
                  
                  $colClass = "";
                  if ($speisenRow['speise_art'] === 'Vollkost') $colClass = "col-vk";
                  if ($speisenRow['speise_art'] === 'Leichte Vollkost') $colClass = "col-lvk";
                  if ($speisenRow['speise_art'] === 'Vegetarisch') $colClass = "col-veg";

                  echo "<td class='$colClass'>" . h($speisenRow['speise_name']) . "</td>";
                  echo "<td class='$colClass'>" . h($speisenRow['speise_art']) . "</td>";
                  echo "<td>" . h($datumFormatted) . "</td>";
                  echo "<td>" . h($abstimmungsRow['cnt']) . "</td>";
                  echo "<td>" . $prozent . "%</td>";
                  echo "</tr>";
              }
          }
          ?>
        </table>
      </div>
      <br><br>
      <div class="w3-center">
        <a href="./zusatzstoffe.php" class="modern-btn secondary" target="_blank">
          <i class="fa fa-asterisk"></i> Zusatzstoffe
        </a>
        <a href="./nachrichten.php" class="modern-btn" target="_blank">
          <i class="fa fa-envelope-open"></i> Nachrichten lesen
        </a>
      </div>
    </div>
  </div>
</div>

  <!-- SPEISEN VERWALTEN -->
  <div id="speisen" class="tab-content">
    <header class="hero-header w3-center">
      <h1>Speisen-Datenbank</h1>
      <p class="w3-text-muted">Hier können Sie das Menü-Sortiment pflegen.</p>
    </header>

    <div class="page-container">
      <div class="modern-card">
        <div class="w3-row-padding">
            <!-- Add Speise -->
            <div class="w3-third">
                <h3>Speise hinzufügen</h3>
                <?php if (isset($messages['speise_add'])) echo $messages['speise_add']; ?>
                <form action="#speisen" method="post">
                    <p><input type="text" name="speise_name" placeholder="Speise Name" required></p>
                    <p>
                      <select name="speise_art" required>
                          <option value="Vollkost">Vollkost</option>
                          <option value="Leichte Vollkost">Leichte Vollkost</option>
                          <option value="Vegetarisch">Vegetarisch</option>
                      </select>
                    </p>
                    <button class="modern-btn" type="submit" style="width:100%"><i class="fa fa-plus"></i> Hinzufügen</button>
                </form>
            </div>

            <!-- Edit Speise -->
            <div class="w3-third">
                <h3>Speise bearbeiten</h3>
                <?php if (isset($messages['speise_edit'])) echo $messages['speise_edit']; ?>
                <form action="#speisen" method="post">
                    <p>
                      <select name="edit_id" id="editSelect" required onchange="loadDishData()">
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
                    </p>
                    <p>
                      <select name="edit_art" id="editArtSelect" required>
                          <option value="Vollkost">Vollkost</option>
                          <option value="Leichte Vollkost">Leichte Vollkost</option>
                          <option value="Vegetarisch">Vegetarisch</option>
                      </select>
                    </p>
                    <p><input type="text" name="edit_name" id="editNameInput" placeholder="Name..." required></p>
                    <button class="modern-btn" type="submit" style="width:100%; background: #f59e0b;"><i class="fa fa-pencil"></i> Ändern</button>
                </form>
            </div>

            <!-- Delete Speise -->
            <div class="w3-third">
                <h3>Speise löschen</h3>
                <?php if (isset($messages['speise_del'])) echo $messages['speise_del']; ?>
                <form action="#speisen" method="post">
                    <p>
                      <select name="entfernen_id" required>
                          <option value="" disabled selected>-- Bitte wählen --</option>
                          <?php
                          foreach ($speisenList as $row) {
                              echo "<option value='".$row['speise_nr']."'>".h($row['speise_art'])." - ".h($row['speise_name'])."</option>";
                          }
                          ?>
                      </select>
                    </p>
                    <br>
                    <button class="modern-btn" type="submit" style="width:100%; background: #ef4444;"><i class="fa fa-trash"></i> Löschen</button>
                </form>
            </div>
        </div>
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

  <!-- WOCHENPLAN -->
  <div id="wochenplan" class="tab-content">
    <header class="hero-header w3-center">
      <h1>Wochenplan Erstellung</h1>
      <p class="w3-text-muted">Legen Sie das Menü für die kommende Woche fest.</p>
    </header>

    <div class="page-container">
      <?php if (isset($messages['wochenplan'])) echo $messages['wochenplan']; ?>

      <div class="modern-card">
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

          echo "<div class='w3-center w3-padding'><label for='date'><b>Beginn der Woche:</b> </label> ";
          echo "<input type='date' id='date' name='date' value='".h($dateValue)."' required style='width:auto;'></div><br>";
          
          if (isset($_POST['load_from_wunsch'])) {
              echo "<div class='w3-panel w3-blue'><p>Daten aus <b>Wunschplan</b> importiert. Bitte prüfen und speichern.</p></div>";
          }
          ?>

          <div class="w3-responsive">
            <table class="modern-table">
              <tr>
                <th>Wochentag</th>
                <th>Vollkost</th>
                <th>Leichte Vollkost</th>
                <th>Vegetarisch</th>
              </tr>
              <?php
              $arten = ["Vollkost", "Leichte Vollkost", "Vegetarisch"];
              $tage = ["Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag"];
              $sourceTable = isset($_POST['load_from_wunsch']) ? "wunschplan" : "wochenplan";

              // Preload all dishes
              $allDishes = $pdo->query("SELECT * FROM speisen ORDER BY speise_name")->fetchAll();

              foreach ($tage as $tag) {
                  echo "<tr><td><b>$tag</b></td>";
                  foreach ($arten as $art) {
                      echo "<td>";
                      $selectName = "wochenplan_" . $tag . "_" . str_replace(" ", "_", $art);
                      
                      echo "<select name='".$selectName."'>";
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
                      echo "</td>";
                  }
                  echo "</tr>";
              }
              ?>
            </table>
          </div>

          <div class="w3-row-padding w3-padding-16">
              <div class="w3-half">
                <button class="modern-btn secondary" type="submit" name="load_from_wunsch" style="width:100%">
                    <i class="fa fa-refresh"></i> Daten aus Wunschplan laden
                </button>
              </div>
              <div class="w3-half">
                <button class="modern-btn" type="submit" name="wochenplan_save" style="width:100%">
                    <i class="fa fa-check"></i> Wochenplan speichern
                </button>
              </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- WUNSCHPLAN -->
  <div id="wunschplan" class="tab-content">
    <header class="hero-header w3-center">
      <h1>Wunschplan Verwaltung</h1>
      <p class="w3-text-muted">Hier bereiten Sie die Abstimmung für die Nutzer vor.</p>
    </header>

    <div class="page-container">
      <?php if (isset($messages['wunschplan'])) echo $messages['wunschplan']; ?>

      <div class="modern-card">
        <form id='wunschForm' action='#wunschplan' method='post'>
          <?php
          $wpDateValue = "";
          $dRow = $pdo->query("SELECT datum FROM wunschplan ORDER BY datum ASC LIMIT 1")->fetch();
          if ($dRow) $wpDateValue = (new DateTime($dRow['datum']))->format('Y-m-d');
          ?>

          <div class='w3-center w3-padding'>
            <label for='dateWunsch'><b>Start-Datum der Woche:</b> </label>
            <input type='date' id='dateWunsch' name='dateWunsch' value='<?php echo h($wpDateValue); ?>' required style="width:auto;">
          </div><br>

          <div class="w3-responsive">
            <table class="modern-table">
            <tr>
              <th>Wochentag</th>
              <th>Vollkost</th>
              <th>Leichte Vollkost</th>
              <th>Vegetarisch</th>
            </tr>

            <?php
            foreach ($tage as $tag) {
                echo "<tr><td><b>" . h($tag) . "</b></td>";
                foreach ($arten as $art) {
                    echo "<td>";
                    echo "<select name='wunsch_" . $tag . "_" . str_replace(' ', '_', $art) . "'>";
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
                <button class="modern-btn secondary" type="button" onclick="clearForm()" style="width:100%">
                    <i class="fa fa-eraser"></i> Felder leeren
                </button>
            </div>
            <div class="w3-third">
                <button class="modern-btn" type="submit" name="wp_action" value="update" style="width:100%; background: #f59e0b;">
                    <i class="fa fa-pencil"></i> Plan ändern <small>(Votes behalten)</small>
                </button>
            </div>
            <div class="w3-third">
                <button class="modern-btn" type="submit" name="wp_action" value="new" onclick="return confirm('Sicher? Die aktuelle Abstimmung wird gelöscht!');" style="width:100%; background: #ef4444;">
                    <i class="fa fa-trash"></i> Reset & Speichern
                </button>
            </div>
          </div>
        </form>
      </div>
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

<?php 
require_once __DIR__ . '/impressum.php';
require __DIR__ . '/templates/footer.php';
?>