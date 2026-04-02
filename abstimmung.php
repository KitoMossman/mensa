<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/Database.php';

initSession();
$pdo = Database::getInstance()->getConnection();

$tage = ["Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag"];

if (!isset($_SESSION['abgestimmt'])) {
    $stmt = $pdo->prepare("INSERT INTO abstimmung (speise_nr) VALUES (?)");
    foreach ($tage as $tag) {
        if (isset($_POST[$tag]) && $_POST[$tag] > 0) {
            $stmt->execute([$_POST[$tag]]);
        }
    }
    $_SESSION['abgestimmt'] = true;
}

$pageTitle = 'Abstimmung';
$sidebarHtml = '
  <a href="./index.php" class="w3-bar-item w3-button w3-padding-large w3-black">
    <i class="fa fa-arrow-left w3-xxlarge"></i><p>ZURÜCK</p>
  </a>
';
$navbarSmallHtml = '
    <a href="./index.php" class="w3-bar-item w3-button" style="width:100% !important">ZURÜCK</a>
';

require __DIR__ . '/templates/header.php';
?>

  <!-- Abstimmung Section -->
  <div class="w3-container w3-padding-64 w3-center" id="abstimmung">
    <h1 class="w3-text-blue" style="font-size:100px;">Vielen Dank für die Abstimmung</h1>
    <p><a href="./index.php" class="w3-button w3-large w3-light-grey"><i class="fa fa-home"></i> Zurück zur Startseite</a></p>
  </div>

<?php 
require_once __DIR__ . '/impressum.php';
require __DIR__ . '/templates/footer.php'; 
?>
