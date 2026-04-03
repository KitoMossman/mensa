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

  <header class="hero-header w3-center">
    <h1>Antwort der Küche abrufen</h1>
    <p class="w3-text-muted">Geben Sie hier Ihre Ticket-ID und Ihr Geheimwort ein.</p>
  </header>

  <div class="page-container">
    <?php
    $messageRecord = null;
    $errorMessage = "";
    $successMessage = "";

    // Handle Rückantwort Submission
    if (isset($_POST['rueckantwort'], $_POST['ticket_nr'])) {
        $stmtUpdate = $pdo->prepare("UPDATE nachrichten SET nutzer_rueckantwort = ? WHERE nachrichten_nr = ?");
        if ($stmtUpdate->execute([$_POST['rueckantwort'], $_POST['ticket_nr']])) {
            $successMessage = "Deine Rückantwort wurde erfolgreich gespeichert.";
        }
    }

    if (isset($_POST['ticket_id'], $_POST['geheimwort'])) {
        $stmt = $pdo->prepare("SELECT * FROM nachrichten WHERE ticket_id = ? AND antwort_gewuenscht = 1");
        $stmt->execute([trim($_POST['ticket_id'])]);
        $row = $stmt->fetch();

        if ($row) {
            // Verify Password
            if (password_verify($_POST['geheimwort'], $row['geheimwort_hash'])) {
                $messageRecord = $row;
                
                // Set abgerufen_am if kitchen has replied and it's not set
                if (!empty($messageRecord['antwort']) && empty($messageRecord['abgerufen_am'])) {
                    $pdo->prepare("UPDATE nachrichten SET abgerufen_am = NOW() WHERE nachrichten_nr = ?")
                        ->execute([$messageRecord['nachrichten_nr']]);
                    // Update variable to avoid mismatch on UI
                    $messageRecord['abgerufen_am'] = date('Y-m-d H:i:s');
                }
            } else {
                $errorMessage = "Das eingegebene Geheimwort ist falsch.";
            }
        } else {
            $errorMessage = "Es wurde kein Ticket mit dieser ID gefunden oder es wurde keine Antwort angefordert.";
        }
    }
    ?>

    <?php if ($successMessage): ?>
        <div class="w3-panel w3-green w3-padding-large w3-margin-bottom">
            <p><?php echo h($successMessage); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($messageRecord): ?>
      <div class="modern-card">
        <h2 class="w3-text-white" style="margin-top:0">Ticket: <?php echo h($messageRecord['ticket_id']); ?></h2>
        <?php if (!empty($messageRecord['erstellt_am'])): ?>
            <p class="w3-text-muted">Erstellt am: <?php echo h((new DateTime($messageRecord['erstellt_am']))->format('d.m.Y H:i')); ?> Uhr</p>
        <?php else: ?>
            <p class="w3-text-muted">Datum: <?php echo h((new DateTime($messageRecord['datum']))->format('d.m.Y')); ?></p>
        <?php endif; ?>
        <hr class="w3-opacity">
        
        <div class="w3-margin-bottom">
            <h4 class="w3-text-white"><b>Deine Nachricht (<?php echo h($messageRecord['thema']); ?>)</b></h4>
            <div class="w3-padding w3-round" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); white-space: pre-wrap;"><?php echo h($messageRecord['nachricht']); ?></div>
        </div>

        <div>
            <h4 class="w3-text-white"><b>Antwort der Küche</b></h4>
            <?php if (!empty($messageRecord['antwort'])): ?>
                <div class="w3-padding w3-round" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); white-space: pre-wrap; color: #93c5fd;"><?php echo h($messageRecord['antwort']); ?></div>
                
                <!-- Rückantwort Section -->
                <div class="w3-margin-top">
                    <?php if (empty($messageRecord['nutzer_rueckantwort'])): ?>
                        <details style="margin-top:20px;">
                          <summary class="modern-btn secondary small" style="cursor:pointer; display:inline-block;"><i class="fa fa-reply"></i> Auf Antwort reagieren</summary>
                          <form action="./abrufen.php" method="post" class="w3-margin-top">
                              <input type="hidden" name="ticket_id" value="<?php echo h($_POST['ticket_id']); ?>">
                              <input type="hidden" name="geheimwort" value="<?php echo h($_POST['geheimwort']); ?>">
                              <input type="hidden" name="ticket_nr" value="<?php echo h($messageRecord['nachrichten_nr']); ?>">
                              <textarea name="rueckantwort" class="w3-input w3-border w3-round w3-transparent w3-text-white" rows="3" placeholder="Ihre abschließende Rückantwort..." required style="background: rgba(0,0,0,0.2) !important;"></textarea>
                              <button type="submit" class="modern-btn w3-margin-top"><i class="fa fa-paper-plane"></i> Senden</button>
                          </form>
                        </details>
                    <?php else: ?>
                        <h4 class="w3-text-white w3-margin-top"><b>Deine Rückantwort</b></h4>
                        <div class="w3-padding w3-round" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); white-space: pre-wrap;"><?php echo h($messageRecord['nutzer_rueckantwort']); ?></div>
                        <p class="w3-small w3-text-muted"><i>(Notiz: Dieses Ticket hat bereits die maximale Konversationstiefe erreicht.)</i></p>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <div class="w3-padding w3-round" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); color: #fcd34d;"><i>Die Küche hat Deine Nachricht noch nicht beantwortet. Bitte schaue später noch einmal vorbei.</i></div>
            <?php endif; ?>
        </div>
        <br>
        <p><a href="./abrufen.php" class="modern-btn secondary"><i class="fa fa-refresh"></i> Weiteres Ticket prüfen</a></p>
      </div>
      
    <?php else: ?>
      
      <div class="modern-card" style="max-width: 500px; margin: 0 auto;">
        <h2 class="w3-text-white" style="margin-top:0">Ticket laden</h2>
        <?php if ($errorMessage): ?>
          <p class="w3-text-red w3-center"><?php echo h($errorMessage); ?></p>
        <?php endif; ?>

        <form action='./abrufen.php' method='post'>
          <p><input type='text' placeholder='Ticket-ID (z.B. 8A2F1C)' name='ticket_id' required></p>
          <p><input type='password' placeholder='Geheimwort' name='geheimwort' required></p>
          <p>
            <button class='modern-btn' type='submit' style="width:100%;">
              <i class='fa fa-search'></i> Abrufen
            </button>
          </p>
        </form>
      </div>
    <?php endif; ?>
  </div>

<?php 
require_once __DIR__ . '/impressum.php';
require __DIR__ . '/templates/footer.php'; 
?>
