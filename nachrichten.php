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

// Handle Delete/Cleanup
if (isset($_POST['delete'])) {
    $pdo->query("DELETE FROM nachrichten");
    $message = "<div class='w3-panel w3-green'><p>Alle Nachrichten wurden gelöscht.</p></div>";
} else {
    // Automatically delete old messages without reply functionality, 
    // BUT keep unanswered items longer or just let everything age out by 30 days.
    $pdo->query("DELETE FROM nachrichten WHERE datum < NOW() - INTERVAL 30 DAY");
}

$pageTitle = 'Nachrichten';
$sidebarHtml = '
  <a href="#" class="w3-bar-item w3-button w3-padding-large w3-black">
    <i class="fa fa-envelope-open w3-xxlarge"></i><p>NACHRICHTEN</p>
  </a>
  <form action="./nachrichten.php" method="post" style="margin:0;" onsubmit="return confirm(\'Alle Nachrichten löschen?\');">
    <input type="hidden" name="delete">
    <button type="submit" class="w3-bar-item w3-button w3-padding-large w3-hover-black w3-block">
      <i class="fa fa-trash w3-xxlarge"></i><p>LEEREN</p>
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
          <th style="width:10%">Datum</th>
          <th style="width:15%">Thema / Absender</th>
          <th style="width:35%">Nachricht</th>
          <th style="width:40%">Antwort</th>
        </tr>
        <?php
        $stmt = $pdo->query("SELECT * FROM nachrichten ORDER BY antwort_gewuenscht DESC, datum DESC, thema ASC");
        while ($row = $stmt->fetch()) {
            $dateFormatted = (new DateTime($row['datum']))->format('d.m.Y');
            
            echo "<tr>";
            echo "<td>" . h($dateFormatted) . "</td>";
            
            echo "<td>";
            echo "<b>" . h($row['thema']) . "</b><br>";
            echo "<span class='w3-small'>Von: " . h($row['absender']) . "</span>";
            if ($row['antwort_gewuenscht']) {
                echo "<br><span class='w3-badge w3-blue w3-tiny'>Ticket: " . h($row['ticket_id']) . "</span>";
            }
            echo "</td>";
            
            echo "<td><p style='white-space: pre-wrap;'>" . h($row['nachricht']) . "</p></td>";
            
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
            } else {
                echo "<span class='w3-text-grey w3-small'>Keine Antwort angefordert.</span>";
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
