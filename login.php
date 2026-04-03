<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/Database.php';

requireAdmin();
$pdo = Database::getInstance()->getConnection();

$messages = [];

// ======= POST PROCESSING =======

// --- Nachrichten Handle ---
if (isset($_POST['reply_id'], $_POST['antwort_text'])) {
    $stmt = $pdo->prepare("UPDATE nachrichten SET antwort = ? WHERE nachrichten_nr = ?");
    if ($stmt->execute([$_POST['antwort_text'], $_POST['reply_id']])) {
        $messages['nachricht'] = "<div class='w3-panel w3-green w3-round'><p><i class='fa fa-check'></i> Antwort erfolgreich gespeichert!</p></div>";
    }
}
if (isset($_POST['delete_ticket_id'])) {
    $stmt = $pdo->prepare("DELETE FROM nachrichten WHERE nachrichten_nr = ?");
    if ($stmt->execute([$_POST['delete_ticket_id']])) {
        $messages['nachricht'] = "<div class='w3-panel w3-green w3-round'><p><i class='fa fa-check'></i> Ticket wurde gelöscht.</p></div>";
    }
}
if (isset($_POST['delete_all_tickets'])) {
    $pdo->query("DELETE FROM nachrichten");
    $messages['nachricht'] = "<div class='w3-panel w3-green w3-round'><p><i class='fa fa-info-circle'></i> Alle Nachrichten wurden geleert.</p></div>";
}

// --- Umfrage Handle ---
if (isset($_POST['beginDate'], $_POST['endDate'])) {
    $pdo->query("DELETE FROM umfrage");
    $stmt = $pdo->prepare("INSERT INTO umfrage (beginn, ende) VALUES (?, ?)");
    if ($stmt->execute([$_POST['beginDate'], $_POST['endDate']])) {
         $messages['umfrage'] = "<div class='w3-panel w3-green w3-round'><p><i class='fa fa-play'></i> Umfrage erfolgreich gestartet.</p></div>";
    }
}
if (isset($_POST['stop_umfrage'])) {
    $hasUmfrage = ($pdo->query("SELECT COUNT(*) FROM umfrage")->fetchColumn() > 0);
    if ($hasUmfrage) {
        $pdo->query("DELETE FROM umfrage");
        $pdo->query("CREATE TABLE IF NOT EXISTS ergebnis_umfrage SELECT * FROM wunschspeisen");
        $pdo->query("DELETE FROM ergebnis_umfrage");
        $pdo->query("INSERT INTO ergebnis_umfrage SELECT * FROM wunschspeisen");
        $pdo->query("DELETE FROM wunschspeisen");
        $messages['umfrage'] = "<div class='w3-panel w3-green w3-round'><p><i class='fa fa-stop'></i> Umfrage gestoppt und ausgewertet.</p></div>";
    }
}

// --- Speisen Handle ---
if (isset($_POST['speise_name'], $_POST['speise_art'])) {
    $stmt = $pdo->prepare("REPLACE INTO speisen (speise_name, speise_art) VALUES (?, ?)");
    if ($stmt->execute([$_POST['speise_name'], $_POST['speise_art']])) {
        $messages['speise'] = "<div class='w3-panel w3-green w3-round'><p><i class='fa fa-check'></i> Speise wurde erfolgreich hinzugefügt.</p></div>";
    }
}
if (isset($_POST['edit_id'], $_POST['edit_name'], $_POST['edit_art'])) {
    $stmt = $pdo->prepare("UPDATE speisen SET speise_name = ?, speise_art = ? WHERE speise_nr = ?");
    if ($stmt->execute([$_POST['edit_name'], $_POST['edit_art'], $_POST['edit_id']])) {
        $messages['speise'] = "<div class='w3-panel w3-green w3-round'><p><i class='fa fa-check'></i> Speise wurde erfolgreich aktualisiert.</p></div>";
    }
}
if (isset($_POST['entfernen_id'])) {
    $stmt = $pdo->prepare("DELETE FROM speisen WHERE speise_nr = ?");
    if ($stmt->execute([$_POST['entfernen_id']])) {
        $messages['speise'] = "<div class='w3-panel w3-green w3-round'><p><i class='fa fa-trash'></i> Speise wurde gelöscht.</p></div>";
    }
}

// --- Pläne Handle ---
if (isset($_POST['wochenplan_save'], $_POST['date'])) {
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
    $messages['plaene'] = "<div class='w3-panel w3-green w3-round'><p><i class='fa fa-check'></i> Wochenplan erfolgreich gespeichert!</p></div>";
}
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
        $messages['plaene'] = "<div class='w3-panel w3-red w3-round'><p><i class='fa fa-trash'></i> Wunschplan Reset & Speichern erfolgt!</p></div>";
    } else {
        $messages['plaene'] = "<div class='w3-panel w3-green w3-round'><p><i class='fa fa-check'></i> Wunschplan wurde aktualisiert.</p></div>";
    }
}

// --- Zusatzstoffe Handle ---
if (isset($_POST['add_zs_nr'], $_POST['add_zs_name'])) {
    $stmt = $pdo->prepare("INSERT INTO zusatzstoffe (zusatzstoff_nr, bezeichnung) VALUES (?, ?)");
    if ($stmt->execute([$_POST['add_zs_nr'], $_POST['add_zs_name']])) {
        $messages['zusatz'] = "<div class='w3-panel w3-green w3-round'><p><i class='fa fa-check'></i> Zusatzstoff erfolgreich hinzugefügt.</p></div>";
    }
}
if (isset($_POST['edit_zs_old_nr'], $_POST['edit_zs_new_nr'], $_POST['edit_zs_name'])) {
    $stmt = $pdo->prepare("UPDATE zusatzstoffe SET zusatzstoff_nr = ?, bezeichnung = ? WHERE zusatzstoff_nr = ?");
    if ($stmt->execute([$_POST['edit_zs_new_nr'], $_POST['edit_zs_name'], $_POST['edit_zs_old_nr']])) {
        $messages['zusatz'] = "<div class='w3-panel w3-green w3-round'><p><i class='fa fa-check'></i> Zusatzstoff erfolgreich aktualisiert.</p></div>";
    }
}
if (isset($_POST['delete_zs_nr'])) {
    $stmt = $pdo->prepare("DELETE FROM zusatzstoffe WHERE zusatzstoff_nr = ?");
    if ($stmt->execute([$_POST['delete_zs_nr']])) {
        $messages['zusatz'] = "<div class='w3-panel w3-green w3-round'><p><i class='fa fa-trash'></i> Zusatzstoff wurde gelöscht.</p></div>";
    }
}

// ======= END POST PROCESSING =======

$sidebarHtml = '
  <a href="javascript:void(0)" onclick="openTab(\'auswertung\', event)" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-bar-chart w3-xlarge"></i><p style="font-size:10px; margin-top:-5px;">AUSWERTUNG</p>
  </a>
  <a href="javascript:void(0)" onclick="openTab(\'speisen\', event)" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-cutlery w3-xlarge"></i><p style="font-size:10px; margin-top:-5px;">SPEISEN</p>
  </a>
  <a href="javascript:void(0)" onclick="openTab(\'plaene\', event, \'wochenplan-section\')" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-calendar w3-xlarge"></i><p style="font-size:10px; margin-top:-5px;">WOCHENPLAN</p>
  </a>
  <a href="javascript:void(0)" onclick="openTab(\'plaene\', event, \'wunschplan-section\')" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-pencil-square-o w3-xlarge"></i><p style="font-size:10px; margin-top:-5px;">WUNSCHPLAN</p>
  </a>
  <a href="javascript:void(0)" onclick="openTab(\'nachrichten\', event)" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-envelope-open w3-xlarge"></i><p style="font-size:10px; margin-top:-5px;">NACHRICHTEN</p>
  </a>
  <a href="javascript:void(0)" onclick="openTab(\'umfrage\', event)" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-line-chart w3-xlarge"></i><p style="font-size:10px; margin-top:-5px;">UMFRAGE</p>
  </a>
  <a href="javascript:void(0)" onclick="openTab(\'zusatz\', event)" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-asterisk w3-xlarge"></i><p style="font-size:10px; margin-top:-5px;">ZUSATZ</p>
  </a>
  <form action="./index.php" method="post" style="margin:0;">
    <input type="hidden" name="logout">
    <button type="submit" class="w3-bar-item w3-button w3-padding-large w3-block">
      <i class="fa fa-lock w3-xlarge"></i><p style="font-size:10px; margin-top:-5px;">LOGOUT</p>
    </button>
  </form>
';

$navbarSmallHtml = '
    <a href="javascript:void(0)" onclick="openTab(\'auswertung\', event)" class="w3-bar-item w3-button" style="width:14% !important; font-size:10px; padding:8px 0;">STAT</a>
    <a href="javascript:void(0)" onclick="openTab(\'speisen\', event)" class="w3-bar-item w3-button" style="width:14% !important; font-size:10px; padding:8px 0;">DB</a>
    <a href="javascript:void(0)" onclick="openTab(\'plaene\', event, \'wochenplan-section\')" class="w3-bar-item w3-button" style="width:14% !important; font-size:10px; padding:8px 0;">WEEK</a>
    <a href="javascript:void(0)" onclick="openTab(\'plaene\', event, \'wunschplan-section\')" class="w3-bar-item w3-button" style="width:14% !important; font-size:10px; padding:8px 0;">VOTE</a>
    <a href="javascript:void(0)" onclick="openTab(\'nachrichten\', event)" class="w3-bar-item w3-button" style="width:14% !important; font-size:10px; padding:8px 0;">MAIL</a>
    <a href="javascript:void(0)" onclick="openTab(\'umfrage\', event)" class="w3-bar-item w3-button" style="width:14% !important; font-size:10px; padding:8px 0;">UMFR</a>
    <a href="javascript:void(0)" onclick="openTab(\'zusatz\', event)" class="w3-bar-item w3-button" style="width:14% !important; font-size:10px; padding:8px 0;">ZUS</a>
';

$pageTitle = 'Küche Dashboard';
require __DIR__ . '/templates/header.php';
?>

<!-- Tab: AUSWERTUNG -->
<div id="auswertung" class="tab-content active">
  <header class="hero-header w3-center">
    <h1 class="w3-jumbo">Dashboard</h1>
    <p class="w3-text-muted">Aktuelle Wahlergebnisse und Statistiken.</p>
  </header>

  <div class="page-container">
    <div class="modern-card">
      <h2 style="margin-top:0">Wahl-Beteiligung</h2>
      <div class="w3-responsive">
        <table class="modern-table polished">
          <thead>
            <tr>
              <th style="width:15%">Tag</th>
              <th style="width:45%">Speise / Gericht</th>
              <th style="width:15%">Typ</th>
              <th style="width:10%">Stimmen</th>
              <th style="width:15%">Anteil</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $gesamtAnzahlProTag = ["Montag" => 0, "Dienstag" => 0, "Mittwoch" => 0, "Donnerstag" => 0, "Freitag" => 0, "Samstag" => 0, "Sonntag" => 0];
          $wunschplan = $pdo->query("SELECT * FROM wunschplan")->fetchAll();

          foreach ($wunschplan as $wunschRow) {
              $stmtAbst = $pdo->prepare("SELECT COUNT(*) FROM abstimmung WHERE speise_nr = ?");
              $stmtAbst->execute([$wunschRow['speise_nr']]);
              $gesamtAnzahlProTag[$wunschRow['tag']] += $stmtAbst->fetchColumn();
          }

          $lastTag = "";
          foreach ($wunschplan as $wunschRow) {
              $stmtAbst = $pdo->prepare("SELECT speise_nr, COUNT(*) as cnt FROM abstimmung WHERE speise_nr=? GROUP BY speise_nr");
              $stmtAbst->execute([$wunschRow['speise_nr']]);
              if ($abstRow = $stmtAbst->fetch()) {
                  $stmtSpeise = $pdo->prepare("SELECT * FROM speisen WHERE speise_nr=?");
                  $stmtSpeise->execute([$abstRow['speise_nr']]);
                  $speisenRow = $stmtSpeise->fetch();
                  if (!$speisenRow) continue;

                  if ($lastTag != "" && $wunschRow['tag'] != $lastTag) {
                      echo "<tr class='spacer-row'><td colspan='5'></td></tr>";
                  }
                  $lastTag = $wunschRow['tag'];

                  $prozent = ($gesamtAnzahlProTag[$wunschRow['tag']] > 0) ? number_format($abstRow['cnt'] * 100 / $gesamtAnzahlProTag[$wunschRow['tag']], 0) : 0;
                  
                  $colClass = "";
                  if ($speisenRow['speise_art'] === 'Vollkost') $colClass = "col-vk";
                  if ($speisenRow['speise_art'] === 'Leichte Vollkost') $colClass = "col-lvk";
                  if ($speisenRow['speise_art'] === 'Vegetarisch') $colClass = "col-veg";

                  echo "<tr>";
                  echo "<td><b class='w3-text-white'>" . h($wunschRow['tag']) . "</b><br><small class='w3-text-muted'>".(new DateTime($wunschRow['datum']))->format('d.m.')."</small></td>";
                  echo "<td class='$colClass'>" . formatMealName($speisenRow['speise_name']) . "</td>";
                  echo "<td><span class='type-badge $colClass'>" . h($speisenRow['speise_art']) . "</span></td>";
                  echo "<td><b>" . h($abstRow['cnt']) . "</b></td>";
                  echo "<td>
                          <div class='progress-bg'>
                            <div class='progress-bar $colClass' style='width:$prozent%'></div>
                          </div>
                          <small>$prozent%</small>
                        </td>";
                  echo "</tr>";
              }
          }
          ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Tab: SPEISEN -->
<div id="speisen" class="tab-content">
  <header class="hero-header w3-center">
    <h1>Speisen-Datenbank</h1>
    <p class="w3-text-muted">Verwalten Sie das Sortiment der Küche.</p>
  </header>
  <div class="page-container" style="max-width:800px;">
    <?php if (isset($messages['speise'])) echo $messages['speise']; ?>
    
    <!-- ADD -->
    <div class="modern-card w3-margin-bottom">
        <h2 style="margin-top:0"><i class="fa fa-plus-circle w3-text-blue"></i> Neu hinzufügen</h2>
        <form action="#speisen" method="post">
            <table class="form-table">
                <tr>
                    <td>Speisename</td>
                    <td><input type="text" name="speise_name" placeholder="z.B. Schnitzel Wiener Art (A,G,I)" required></td>
                </tr>
                <tr>
                    <td>Kategorie</td>
                    <td>
                        <select name="speise_art" required>
                            <option value="Vollkost">Vollkost</option>
                            <option value="Leichte Vollkost">Leichte Vollkost</option>
                            <option value="Vegetarisch">Vegetarisch</option>
                        </select>
                    </td>
                </tr>
            </table>
            <button class="modern-btn jumbo" type="submit" style="width:100%"><i class="fa fa-save"></i> Speise dauerhaft speichern</button>
        </form>
    </div>

    <!-- EDIT -->
    <div class="modern-card w3-margin-bottom">
        <h2 style="margin-top:0"><i class="fa fa-pencil w3-text-orange"></i> Bestehende Speise bearbeiten</h2>
        <form action="#speisen" method="post">
            <table class="form-table">
                <tr>
                    <td>Auswahl</td>
                    <td>
                        <select name="edit_id" id="editSelect" required onchange="loadDishData()">
                            <option value="" disabled selected>Speise wählen...</option>
                            <?php
                            $speisenList = $pdo->query("SELECT * FROM speisen ORDER BY speise_art, speise_name")->fetchAll();
                            foreach ($speisenList as $row) {
                                $safeName = htmlspecialchars($row['speise_name'], ENT_QUOTES);
                                $safeArt = htmlspecialchars($row['speise_art'], ENT_QUOTES);
                                echo "<option value='".$row['speise_nr']."' data-name='".$safeName."' data-art='".$safeArt."'>".h($row['speise_art'])." | ".h($row['speise_name'])."</option>";
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Neuer Name</td>
                    <td><input type="text" name="edit_name" id="editNameInput" placeholder="Name anpassen..." required></td>
                </tr>
                <tr>
                    <td>Neue Kat.</td>
                    <td>
                        <select name="edit_art" id="editArtSelect" required>
                            <option value="Vollkost">Vollkost</option>
                            <option value="Leichte Vollkost">Leichte Vollkost</option>
                            <option value="Vegetarisch">Vegetarisch</option>
                        </select>
                    </td>
                </tr>
            </table>
            <button class="modern-btn secondary jumbo" type="submit" style="width:100%"><i class="fa fa-check"></i> Änderungen übernehmen</button>
        </form>
    </div>

    <!-- DELETE -->
    <div class="modern-card">
        <h2 style="margin-top:0"><i class="fa fa-trash-o w3-text-red"></i> Speise unwiderruflich löschen</h2>
        <form action="#speisen" method="post" onsubmit="return confirm('Möchten Sie diese Speise wirklich aus der Datenbank entfernen?');">
            <table class="form-table">
                <tr>
                    <td>Speise</td>
                    <td>
                        <select name="entfernen_id" required>
                            <option value="" disabled selected>Speise wählen...</option>
                            <?php foreach ($speisenList as $row): ?>
                                <option value="<?= $row['speise_nr'] ?>"><?= h($row['speise_art']) ?> | <?= h($row['speise_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            <button class="modern-btn danger jumbo" type="submit" style="width:100%"><i class="fa fa-warning"></i> Endgültig löschen</button>
        </form>
    </div>
  </div>
</div>

<!-- Combined Tab: PLÄNE -->
<div id="plaene" class="tab-content">
  <div class="page-container">
    <?php if (isset($messages['plaene'])) echo $messages['plaene']; ?>
    
    <!-- SECTION: WOCHENPLAN -->
    <section id="wochenplan-section" style="padding-top: 20px;">
        <header class="hero-header w3-center">
            <h1>Wochenplan</h1>
            <p class="w3-text-muted">Plan für die laufende oder kommende Woche festlegen.</p>
        </header>
        <div class="modern-card">
            <form action="#plaene-wochenplan-section" method="post">
                <?php
                $curDate = date('Y-m-d');
                if (isset($_POST['load_wunsch'])) {
                    $row = $pdo->query("SELECT datum FROM wunschplan LIMIT 1")->fetch();
                    if ($row) $curDate = $row['datum'];
                } else {
                    $row = $pdo->query("SELECT datum FROM wochenplan LIMIT 1")->fetch();
                    if ($row) $curDate = $row['datum'];
                }
                ?>
                <div class="w3-center w3-margin-bottom">
                    <label class="w3-text-muted"><b>Wochenstart (Montag): </b></label>
                    <input type="date" name="date" value="<?= h($curDate) ?>" required style="max-width:300px; margin-top:10px;">
                </div>
                <div class="w3-responsive">
                    <table class="modern-table" style="table-layout: fixed;">
                        <tr>
                            <th style="width:10%">Tag</th>
                            <th style="width:30%">Vollkost</th>
                            <th style="width:30%">Leichte Vollkost</th>
                            <th style="width:30%">Vegetarisch</th>
                        </tr>
                        <?php
                        $arten = ["Vollkost", "Leichte Vollkost", "Vegetarisch"];
                        $tage = ["Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag"];
                        $allS = $pdo->query("SELECT * FROM speisen ORDER BY speise_name")->fetchAll();
                        $source = isset($_POST['load_wunsch']) ? 'wunschplan' : 'wochenplan';

                        foreach ($tage as $tag) {
                            echo "<tr><td><b>$tag</b></td>";
                            foreach ($arten as $art) {
                                $stmtS = $pdo->prepare("SELECT speise_nr FROM $source WHERE tag=? AND speise_nr IN (SELECT speise_nr FROM speisen WHERE speise_art=?)");
                                $stmtS->execute([$tag, $art]);
                                $selId = $stmtS->fetchColumn();
                                echo "<td><select name='wochenplan_{$tag}_".str_replace(" ","_",$art)."'><option value='0'>- leer -</option>";
                                foreach ($allS as $s) {
                                    if ($s['speise_art'] === $art) {
                                        $sel = ($s['speise_nr'] == $selId) ? "selected" : "";
                                        echo "<option value='".$s['speise_nr']."' $sel>".h($s['speise_name'])."</option>";
                                    }
                                }
                                echo "</select></td>";
                            }
                            echo "</tr>";
                        }
                        ?>
                    </table>
                </div>
                <div class="w3-row-padding w3-margin-top">
                    <div class="w3-half"><button type="submit" name="load_wunsch" class="modern-btn secondary" style="width:100%"><i class="fa fa-refresh"></i> Aus Wunschplan laden</button></div>
                    <div class="w3-half"><button type="submit" name="wochenplan_save" class="modern-btn jumbo" style="width:100%"><i class="fa fa-check"></i> Speichern</button></div>
                </div>
            </form>
        </div>
    </section>

    <hr style="border-top:1px solid rgba(255,255,255,0.1); margin: 60px 0;">

    <!-- SECTION: WUNSCHPLAN -->
    <section id="wunschplan-section" style="padding-top: 20px; margin-bottom: 50px;">
        <header class="hero-header w3-center">
            <h1>Wunsch-Wahl Prep</h1>
            <p class="w3-text-muted">Abstimmung der Gerichte für die Nutzer vorbereiten.</p>
        </header>
        <div class="modern-card">
            <form action="#plaene-wunschplan-section" method="post">
                <?php
                $wpD = date('Y-m-d');
                $row = $pdo->query("SELECT datum FROM wunschplan LIMIT 1")->fetch();
                if ($row) $wpD = $row['datum'];
                ?>
                <div class="w3-center w3-margin-bottom">
                    <label class="w3-text-muted"><b>Abstimmwoche (Montag): </b></label>
                    <input type="date" name="dateWunsch" value="<?= h($wpD) ?>" required style="max-width:300px; margin-top:10px;">
                </div>
                <div class="w3-responsive">
                    <table class="modern-table" style="table-layout: fixed;">
                        <tr>
                            <th style="width:10%">Tag</th>
                            <th style="width:30%">Vollkost</th>
                            <th style="width:30%">Leichte Vollkost</th>
                            <th style="width:30%">Vegetarisch</th>
                        </tr>
                        <?php
                        foreach ($tage as $tag) {
                            echo "<tr><td><b>$tag</b></td>";
                            foreach ($arten as $art) {
                                $stmtS = $pdo->prepare("SELECT speise_nr FROM wunschplan WHERE tag=? AND speise_nr IN (SELECT speise_nr FROM speisen WHERE speise_art=?)");
                                $stmtS->execute([$tag, $art]);
                                $selId = $stmtS->fetchColumn();
                                echo "<td><select name='wunsch_{$tag}_".str_replace(" ","_",$art)."'><option value='0'></option>";
                                foreach ($allS as $s) {
                                    if ($s['speise_art'] === $art) {
                                        $sel = ($s['speise_nr'] == $selId) ? "selected" : "";
                                        echo "<option value='".$s['speise_nr']."' $sel>".h($s['speise_name'])."</option>";
                                    }
                                }
                                echo "</select></td>";
                            }
                            echo "</tr>";
                        }
                        ?>
                    </table>
                </div>
                <div class="w3-row-padding w3-margin-top">
                    <div class="w3-half"><button type="submit" name="wp_action" value="update" class="modern-btn secondary" style="width:100%"><i class="fa fa-save"></i> Planen (Votes behalten)</button></div>
                    <div class="w3-half"><button type="submit" name="wp_action" value="new" class="modern-btn danger" style="width:100%; border-color:#ef4444; color:#ef4444;" onclick="return confirm('ACHTUNG: Alle aktuellen Stimmen werden gelöscht!');"><i class="fa fa-trash"></i> Reset & Speichern</button></div>
                </div>
            </form>
        </div>
    </section>
  </div>
</div>

<!-- Tab: NACHRICHTEN -->
<div id="nachrichten" class="tab-content">
  <header class="hero-header w3-center">
    <h1>Posteingang</h1>
    <p class="w3-text-muted">Tickets und Feedback verwalten.</p>
  </header>
  <div class="page-container">
    <?php if (isset($messages['nachricht'])) echo $messages['nachricht']; ?>
    <div class="w3-right-align w3-margin-bottom">
        <form action="#nachrichten" method="post" onsubmit="return confirm('Alle Nachrichten löschen?');">
            <input type="hidden" name="delete_all_tickets">
            <button type="submit" class="modern-btn secondary small-text"><i class="fa fa-trash"></i> Alle leeren</button>
        </form>
    </div>
    <div class="modern-card">
      <div class="w3-responsive">
        <table class="modern-table">
          <tr>
            <th style="width:15%">Status</th>
            <th style="width:20%">Absender</th>
            <th style="width:30%">Nachricht</th>
            <th style="width:35%">Antwort</th>
          </tr>
          <?php
          $stmt = $pdo->query("SELECT * FROM nachrichten ORDER BY antwort_gewuenscht DESC, COALESCE(erstellt_am, datum) DESC");
          while ($row = $stmt->fetch()): ?>
            <tr>
              <td>
                <span class="w3-small"><b>Anfrage:</b> <?= !empty($row['erstellt_am']) ? h((new DateTime($row['erstellt_am']))->format('d.m.y')) : h($row['datum']) ?></span><br>
                <?php if ($row['antwort_gewuenscht']): ?>
                    <?php if ($row['abgerufen_am']): ?>
                        <span class="w3-text-green w3-small">Gelesen: <?= h((new DateTime($row['abgerufen_am']))->format('d.m H:i')) ?></span>
                    <?php else: ?>
                        <span class="w3-text-orange w3-small">ID: <?= h($row['ticket_id']) ?></span>
                    <?php endif; ?>
                <?php endif; ?>
              </td>
              <td><b><?= h($row['thema']) ?></b><br><span class="w3-small"><?= h($row['absender']) ?></span></td>
              <td style="font-size:13px; opacity:0.9;"><?= nl2br(h($row['nachricht'])) ?></td>
              <td>
                <form action="#nachrichten" method="post">
                  <input type="hidden" name="reply_id" value="<?= $row['nachrichten_nr'] ?>">
                  <textarea name="antwort_text" placeholder="Antwort..." rows="2" class="w3-input w3-border w3-round w3-transparent w3-text-white w3-margin-bottom"><?= h($row['antwort']) ?></textarea>
                  <div style="display:flex; gap:5px;">
                      <button type="submit" class="modern-btn small" style="flex:1"><i class="fa fa-reply"></i></button>
                  </div>
                </form>
                <form action="#nachrichten" method="post" style="display:inline;" onsubmit="return confirm('Ticket löschen?');">
                    <input type="hidden" name="delete_ticket_id" value="<?= $row['nachrichten_nr'] ?>">
                    <button type="submit" class="modern-btn secondary small" style="width:100%; margin-top:5px;"><i class="fa fa-trash"></i> Löschen</button>
                </form>
                <?php if (!empty($row['nutzer_rueckantwort'])): ?>
                    <div class="w3-margin-top w3-padding w3-round w3-small" style="background:rgba(59,130,246,0.1); border:1px solid rgba(59,130,246,0.2);">
                        <b>Nutzer sagt:</b> <?= h($row['nutzer_rueckantwort']) ?>
                    </div>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Tab: UMFRAGE -->
<div id="umfrage" class="tab-content">
  <header class="hero-header w3-center">
    <h1>Umfragen</h1>
    <p class="w3-text-muted">Wunschspeisen freischalten und auswerten.</p>
  </header>
  <div class="page-container">
    <?php if (isset($messages['umfrage'])) echo $messages['umfrage']; ?>
    <div class="modern-card">
        <?php 
        $umActive = ($pdo->query("SELECT COUNT(*) FROM umfrage")->fetchColumn() > 0);
        $uB = ""; $uE = "";
        if ($umActive) {
            $uR = $pdo->query("SELECT * FROM umfrage LIMIT 1")->fetch();
            $uB = (new DateTime($uR['beginn']))->format('d.m.y');
            $uE = (new DateTime($uR['ende']))->format('d.m.y');
        }
        ?>
        <div class="w3-center">
            <h2>Status: <?= $umActive ? "<span class='w3-text-green'>AKTIV</span>" : "<span class='w3-text-muted'>INAKTIV</span>" ?></h2>
            <?php if ($umActive): ?>
                <p>Laufzeit: <b><?= $uB ?></b> bis <b><?= $uE ?></b></p>
                <form action="#umfrage" method="post">
                    <button type="submit" name="stop_umfrage" class="modern-btn danger" onclick="return confirm('Beenden & Auswerten?');">Umfrage stoppen & in Historie schreiben</button>
                </form>
            <?php else: ?>
                <form action="#umfrage" method="post" style="max-width:400px; margin:20px auto;">
                    <div style="display:flex; gap:10px; margin-bottom:15px;">
                        <input type="date" name="beginDate" required>
                        <input type="date" name="endDate" required>
                    </div>
                    <button type="submit" class="modern-btn jumbo" style="width:100%"><i class="fa fa-play"></i> Umfrage starten</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="modern-card w3-margin-top">
        <h2 style="margin-top:0">Historie / Ergebnis</h2>
        <div class="w3-responsive">
            <table class="modern-table">
                <tr><th>Platz</th><th>Votes</th><th>Typ</th><th>Name</th></tr>
                <?php
                try {
                    $hist = $pdo->query("SELECT * FROM ergebnis_umfrage ORDER BY wunschspeise_anzahl DESC, wunschspeise_art LIMIT 20")->fetchAll();
                    $pl = 1;
                    foreach ($hist as $h) {
                        echo "<tr><td><b>$pl</b></td><td>".$h['wunschspeise_anzahl']."</td><td>".$h['wunschspeise_art']."</td><td>".h($h['wunschspeise_name'])."</td></tr>";
                        $pl++;
                    }
                } catch(Exception $e) { echo "<tr><td colspan='4'>Keine Historie vorhanden.</td></tr>"; }
                ?>
            </table>
        </div>
    </div>
  </div>
</div>

<!-- Tab: ZUSATZ -->
<div id="zusatz" class="tab-content">
  <header class="hero-header w3-center">
    <h1>Zusatzstoffe-Verwaltung</h1>
    <p class="w3-text-muted">Inhaltsstoffe pflegen und bearbeiten.</p>
  </header>
  <div class="page-container" style="max-width:800px;">
    <?php if (isset($messages['zusatz'])) echo $messages['zusatz']; ?>

    <!-- ADD ZS -->
    <div class="modern-card w3-margin-bottom">
        <h2 style="margin-top:0"><i class="fa fa-plus-circle w3-text-blue"></i> Neu hinzufügen</h2>
        <form action="#zusatz" method="post">
            <table class="form-table">
                <tr>
                    <td>Nummer</td>
                    <td><input type="text" name="add_zs_nr" placeholder="z.B. 1 oder A" required></td>
                </tr>
                <tr>
                    <td>Bezeichnung</td>
                    <td><input type="text" name="add_zs_name" placeholder="Name des Zusatzstoffes" required></td>
                </tr>
            </table>
            <button class="modern-btn jumbo" type="submit" style="width:100%"><i class="fa fa-save"></i> Hinzufügen</button>
        </form>
    </div>

    <!-- EDIT ZS -->
    <div class="modern-card w3-margin-bottom">
        <h2 style="margin-top:0"><i class="fa fa-pencil w3-text-orange"></i> Zusatzstoff bearbeiten</h2>
        <form action="#zusatz" method="post">
            <table class="form-table">
                <tr>
                    <td>Auswahl</td>
                    <td>
                        <select name="edit_zs_old_nr" id="editZSSelect" required onchange="loadZSData()">
                            <option value="" disabled selected>Zusatzstoff wählen...</option>
                            <?php
                            $zsList = $pdo->query("SELECT * FROM zusatzstoffe ORDER BY (zusatzstoff_nr + 0) ASC, zusatzstoff_nr ASC")->fetchAll();
                            foreach ($zsList as $z) {
                                $safeNr = htmlspecialchars($z['zusatzstoff_nr'], ENT_QUOTES);
                                $safeName = htmlspecialchars($z['bezeichnung'], ENT_QUOTES);
                                echo "<option value='".$safeNr."' data-nr='".$safeNr."' data-name='".$safeName."'>".h($z['zusatzstoff_nr'])." - ".h($z['bezeichnung'])."</option>";
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Neue Nr.</td>
                    <td><input type="text" name="edit_zs_new_nr" id="editZSNrInput" required></td>
                </tr>
                <tr>
                    <td>Neu Bez.</td>
                    <td><input type="text" name="edit_zs_name" id="editZSNameInput" required></td>
                </tr>
            </table>
            <button class="modern-btn secondary jumbo" type="submit" style="width:100%"><i class="fa fa-refresh"></i> Aktualisieren</button>
        </form>
    </div>

    <!-- DELETE ZS -->
    <div class="modern-card w3-margin-bottom">
        <h2 style="margin-top:0"><i class="fa fa-trash-o w3-text-red"></i> Zusatzstoff entfernen</h2>
        <form action="#zusatz" method="post" onsubmit="return confirm('Soll dieser Zusatzstoff wirklich entfernt werden?');">
            <table class="form-table">
                <tr>
                    <td>Zusatzstoff</td>
                    <td>
                        <select name="delete_zs_nr" required>
                            <option value="" disabled selected>Zusatzstoff wählen...</option>
                            <?php foreach ($zsList as $z): ?>
                                <option value="<?= $z['zusatzstoff_nr'] ?>"><?= h($z['zusatzstoff_nr']) ?> - <?= h($z['bezeichnung']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            <button class="modern-btn danger jumbo" type="submit" style="width:100%"><i class="fa fa-warning"></i> Löschen</button>
        </form>
    </div>

    <!-- LIST TABLE -->
    <div class="modern-card">
      <h2 style="margin-top:0">Aktuelle Liste</h2>
      <div class="w3-responsive">
        <table class="modern-table">
          <tr><th style="width:15%">Nr.</th><th>Bezeichnung</th></tr>
          <?php
          foreach ($zsList as $z) {
              echo "<tr><td><b>".h($z['zusatzstoff_nr'])."</b></td><td>".h($z['bezeichnung'])."</td></tr>";
          }
          ?>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
function loadDishData() {
    var s = document.getElementById("editSelect");
    var o = s.options[s.selectedIndex];
    if (o) {
        document.getElementById("editNameInput").value = o.getAttribute('data-name');
        document.getElementById("editArtSelect").value = o.getAttribute('data-art');
    }
}

function loadZSData() {
    var s = document.getElementById("editZSSelect");
    var o = s.options[s.selectedIndex];
    if (o) {
        document.getElementById("editZSNrInput").value = o.getAttribute('data-nr');
        document.getElementById("editZSNameInput").value = o.getAttribute('data-name');
    }
}
</script>

<style>
/* Dashboard specific overrides */
.tab-content .hero-header { margin-bottom: 20px; }
.modern-table td { font-size: 14px; }
.polished thead th { background: rgba(255,255,255,0.03); text-transform: uppercase; letter-spacing: 1px; font-size: 11px; color: #94a3b8; }
.spacer-row td { height: 12px; border: none !important; }
.type-badge { padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 600; text-transform: uppercase; }
.progress-bg { background: rgba(255,255,255,0.1); height: 8px; border-radius: 4px; margin-top: 4px; overflow: hidden; }
.progress-bar { height: 100%; border-radius: 4px; transition: width 0.3s ease; }

/* Form Tables */
.form-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
.form-table td { padding: 10px 5px; vertical-align: middle; border-bottom: 1px solid rgba(255,255,255,0.05); }
.form-table tr:last-child td { border-bottom: none; }
.form-table td:first-child { width: 30%; color: #94a3b8; font-weight: 600; font-size: 13px; }
.form-table td:last-child { width: 70%; }

select, input[type="text"], input[type="date"] { 
    padding: 10px; background: rgba(0,0,0,0.3); color: #fff; 
    border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; width: 100%; 
    transition: border-color 0.3s, background-color 0.3s;
}
select:focus, input:focus { border-color: #3b82f6; background: rgba(0,0,0,0.5); outline: none; }

label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600; margin-top: 10px; }

.small-text { font-size: 12px; }
</style>

<?php 
require __DIR__ . '/templates/footer.php'; 
?>