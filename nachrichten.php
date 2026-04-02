<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/Database.php';

requireAdmin();
$pdo = Database::getInstance()->getConnection();

$message = "";

// Handle Reply
if (isset($_POST['reply_id'], $_POST['antwort_text'])) {
    $stmt = $pdo->prepare("UPDATE nachrichten SET antwort = ? WHERE nachrichten_nr = ?");
    if ($stmt->execute([$_POST['antwort_text'], $_POST['reply_id']])) {
        $message = "<div class='w3-panel w3-green'><p>Antwort gespeichert!</p></div>";
    }
}

// Handle Single Delete
if (isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM nachrichten WHERE nachrichten_nr = ?");
    if ($stmt->execute([$_POST['delete_id']])) {
        $message = "<div class='w3-panel w3-green'><p>Ticket wurde erfolgreich gelöscht.</p></div>";
    }
}

// Handle Delete All
if (isset($_POST['delete'])) {
    $pdo->query("DELETE FROM nachrichten");
    $message = "<div class='w3-panel w3-green'><p>Alle Nachrichten wurden gelöscht.</p></div>";
}

$pageTitle = 'Nachrichten';
$sidebarHtml = '
  <a href="#" class="w3-bar-item w3-button w3-padding-large w3-black">
    <i class="fa fa-envelope-open w3-xxlarge"></i><p>NACHRICHTEN</p>
  </a>
  <form action="./nachrichten.php" method="post" style="margin:0;" onsubmit="return confirm(\'Alle Nachrichten wirklich löschen?\');">
    <input type="hidden" name="delete">
    <button type="submit" class="w3-bar-item w3-button w3-padding-large w3-hover-black w3-block">
      <i class="fa fa-trash w3-xxlarge"></i><p>ALLE LEEREN</p>
    </button>
  </form>
  <a href="./login.php" class="w3-bar-item w3-button w3-padding-large w3-hover-black">
    <i class="fa fa-arrow-left w3-xxlarge"></i><p>ZURÜCK</p>
  </a>
';

$navbarSmallHtml = '
    <a href="#" class="w3-bar-item w3-button" style="width:50% !important">Nachrichten</a>
    <a href="./login.php" class="w3-bar-item w3-button" style="width:50% !important">Zurück</a>
';

require __DIR__ . '/templates/header.php';
?>

  <header class="w3-container w3-padding-32 w3-center w3-black" id="home">
    <h1 class="w3-jumbo">Nachrichten</h1>
  </header>

  <div class="w3-padding-large">
    <?php echo $message; ?>
    
    <div class="w3-responsive">
      <table class="w3-table-all w3-large w3-text-black">
        <tr class="w3-green">
          <th style="width:15%">Status / Verlauf</th>
          <th style="width:20%">Thema / Absender</th>
          <th style="width:30%">Nachricht</th>
          <th style="width:35%">Küche & Rückantwort</th>
        </tr>
        <?php
        $stmt = $pdo->query("SELECT * FROM nachrichten ORDER BY antwort_gewuenscht DESC, COALESCE(erstellt_am, datum) DESC, thema ASC");
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            
            // Status & Zeiten
            echo "<td>";
            if (!empty($row['erstellt_am'])) {
                echo "<div class='w3-small'><b>Erstellt am:</b><br>" . h((new DateTime($row['erstellt_am']))->format('d.m.y H:i')) . "</div><br>";
            } else {
                echo "<div class='w3-small'><b>Datum:</b><br>" . h((new DateTime($row['datum']))->format('d.m.y')) . "</div><br>";
            }
            if ($row['antwort_gewuenscht']) {
                if (!empty($row['abgerufen_am'])) {
                    echo "<div class='w3-small w3-text-green'><b>Gelesen am:</b><br>" . h((new DateTime($row['abgerufen_am']))->format('d.m.y H:i')) . "</div><br>";
                    // Ticket Löschen Button (nur wenn abgerufen)
                    echo "<form action='./nachrichten.php' method='post' onsubmit=\"return confirm('Dieses abgerufene Ticket löschen?');\">";
                    echo "<input type='hidden' name='delete_id' value='" . $row['nachrichten_nr'] . "'>";
                    echo "<button type='submit' class='w3-button w3-red w3-tiny'><i class='fa fa-trash'></i> Ticket löschen</button>";
                    echo "</form>";
                } else {
                    echo "<div class='w3-small w3-text-orange'><b>Status:</b> Ungelesen</div>";
                }
            }
            echo "</td>";
            
            // Thema
            echo "<td>";
            echo "<b>" . h($row['thema']) . "</b><br>";
            echo "<span class='w3-small'>Von: " . h($row['absender']) . "</span>";
            if ($row['antwort_gewuenscht']) {
                echo "<br><span class='w3-badge w3-blue w3-tiny'>Ticket: " . h($row['ticket_id']) . "</span>";
            }
            echo "</td>";
            
            // Nachricht
            echo "<td><p style='white-space: pre-wrap; margin-top:0;'>" . h($row['nachricht']) . "</p></td>";
            
            // Antworten
            echo "<td>";
            if ($row['antwort_gewuenscht']) {
                // Formular für die Antwort
                echo "<form action='./nachrichten.php' method='post'>";
                echo "<input type='hidden' name='reply_id' value='" . $row['nachrichten_nr'] . "'>";
                echo "<textarea name='antwort_text' class='w3-input w3-border w3-margin-bottom' rows='3' placeholder='Antwort verfassen...'>" . h($row['antwort']) . "</textarea>";
                if (empty($row['antwort'])) {
                    echo "<button type='submit' class='w3-button w3-blue w3-small'><i class='fa fa-reply'></i> Antworten</button>";
                } else {
                    echo "<button type='submit' class='w3-button w3-orange w3-text-white w3-small'><i class='fa fa-pencil'></i> Antwort bearbeiten</button>";
                }
                echo "</form>";

                // Anzeige Rückantwort Nutzer
                if (!empty($row['nutzer_rueckantwort'])) {
                    echo "<div class='w3-margin-top w3-padding w3-pale-blue w3-border w3-small'>";
                    echo "<b>Rückantwort des Nutzers:</b><br>";
                    echo "<span style='white-space: pre-wrap;'>" . h($row['nutzer_rueckantwort']) . "</span>";
                    echo "</div>";
                }
            } else {
                echo "<span class='w3-text-grey w3-small'>Keine Antwort angefordert.</span>";
                echo "<form action='./nachrichten.php' method='post' class='w3-margin-top' onsubmit=\"return confirm('Diesen Gruß/Kommentar löschen?');\">";
                echo "<input type='hidden' name='delete_id' value='" . $row['nachrichten_nr'] . "'>";
                echo "<button type='submit' class='w3-button w3-red w3-tiny'><i class='fa fa-trash'></i> Löschen</button>";
                echo "</form>";
            }
            echo "</td>";
            
            echo "</tr>";
        }
        ?>
      </table>
    </div>
  </div>

<?php 
require_once __DIR__ . '/impressum.php';
require __DIR__ . '/templates/footer.php'; 
?>
