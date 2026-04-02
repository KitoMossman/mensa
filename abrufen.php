<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/Database.php';

initSession();
$pdo = Database::getInstance()->getConnection();

$pageTitle = 'Antwort Abrufen';

// Prepare Sidebar HTML
$sidebarHtml = '
  <a href="./index.php" class="w3-bar-item w3-button w3-padding-large w3-black">
    <i class="fa fa-arrow-left w3-xxlarge"></i><p>STARTSEITE</p>
  </a>
';
$navbarSmallHtml = '
    <a href="./index.php" class="w3-bar-item w3-button" style="width:100% !important">STARTSEITE</a>
';

require __DIR__ . '/templates/header.php';
?>

  <header class="w3-container w3-padding-32 w3-center w3-black" id="home">
    <h1 class="w3-jumbo">Antwort der Küche abrufen</h1>
  </header>

  <div class="w3-padding-64 w3-content w3-text-grey">
    <?php
    $messageRecord = null;
    $errorMessage = "";

    if (isset($_POST['ticket_id'], $_POST['geheimwort'])) {
        $stmt = $pdo->prepare("SELECT * FROM nachrichten WHERE ticket_id = ? AND antwort_gewuenscht = 1");
        $stmt->execute([trim($_POST['ticket_id'])]);
        $row = $stmt->fetch();

        if ($row) {
            // Verify Password
            if (password_verify($_POST['geheimwort'], $row['geheimwort_hash'])) {
                $messageRecord = $row;
            } else {
                $errorMessage = "Das eingegebene Geheimwort ist falsch.";
            }
        } else {
            $errorMessage = "Es wurde kein Ticket mit dieser ID gefunden oder es wurde keine Antwort angefordert.";
        }
    }
    ?>

    <?php if ($messageRecord): ?>
      <div class="w3-panel w3-light-grey w3-padding-large w3-border">
        <h2 class="w3-text-black">Ticket: <?php echo h($messageRecord['ticket_id']); ?></h2>
        <p>Datum: <?php echo h((new DateTime($messageRecord['datum']))->format('d.m.Y')); ?></p>
        <hr>
        
        <div class="w3-margin-bottom">
            <h4 class="w3-text-dark-grey"><b>Deine Nachricht (<?php echo h($messageRecord['thema']); ?>)</b></h4>
            <p class="w3-padding w3-white w3-border" style="white-space: pre-wrap;"><?php echo h($messageRecord['nachricht']); ?></p>
        </div>

        <div>
            <h4 class="w3-text-dark-grey"><b>Antwort der Küche</b></h4>
            <?php if (!empty($messageRecord['antwort'])): ?>
                <p class="w3-padding w3-pale-green w3-border" style="white-space: pre-wrap;"><?php echo h($messageRecord['antwort']); ?></p>
            <?php else: ?>
                <p class="w3-padding w3-pale-yellow w3-border"><i>Die Küche hat Deine Nachricht noch nicht beantwortet. Bitte schaue später noch einmal vorbei.</i></p>
            <?php endif; ?>
        </div>
      </div>
      
      <p><a href="./abrufen.php" class="w3-button w3-blue"><i class="fa fa-refresh"></i> Weiteres Ticket prüfen</a></p>
    <?php else: ?>
      
      <?php if ($errorMessage): ?>
        <p class="w3-text-red w3-large w3-center"><?php echo h($errorMessage); ?></p>
      <?php endif; ?>

      <form action='./abrufen.php' method='post'>
        <p><input class='w3-input w3-padding-16' type='text' placeholder='Ticket-ID (z.B. 8A2F1C)' name='ticket_id' required></p>
        <p><input class='w3-input w3-padding-16' type='password' placeholder='Geheimwort' name='geheimwort' required></p>
        <p>
          <button class='w3-button w3-blue w3-padding-large w3-block' type='submit'>
            <i class='fa fa-search'></i> Abrufen
          </button>
        </p>
      </form>
    <?php endif; ?>
  </div>

<?php 
require_once __DIR__ . '/impressum.php';
require __DIR__ . '/templates/footer.php'; 
?>
