<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/Database.php';

initSession();
$pdo = Database::getInstance()->getConnection();

$pageTitle = 'Zusatzstoffe';
$sidebarHtml = '
  <a href="./index.php" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-home w3-xxlarge"></i><p>STARTSEITE</p>
  </a>
  <a href="javascript:window.close();" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-close w3-xxlarge"></i><p>SCHLIESSEN</p>
  </a>
';

$navbarSmallHtml = '
    <a href="./index.php" class="w3-bar-item w3-button" style="width:50% !important">HOME</a>
    <a href="javascript:window.close();" class="w3-bar-item w3-button" style="width:50% !important">SCHLIESSEN</a>
';

require __DIR__ . '/templates/header.php';
?>

<header class="hero-header w3-center">
  <h1>Zusatzstoffe</h1>
  <p class="w3-text-muted">Übersicht der kennzeichnungspflichtigen Inhaltsstoffe.</p>
</header>

<div class="page-container">
  <div class="modern-card">
    <div class="w3-responsive">
      <table class="modern-table">
        <tr>
          <th style="width:15%">Nr.</th>
          <th style="width:85%">Bezeichnung / Inhaltsstoff</th>
        </tr>
        <?php
        $stmt = $pdo->query("SELECT * FROM zusatzstoffe ORDER BY (zusatzstoff_nr + 0) ASC, zusatzstoff_nr ASC");
        while ($row = $stmt->fetch()): ?>
          <tr>
            <td><b class="w3-text-blue" style="font-size:1.1rem;"><?= h($row['zusatzstoff_nr']) ?></b></td>
            <td style="opacity:0.9;"><?= h($row['bezeichnung']) ?></td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>
  </div>
</div>

<?php 
require_once __DIR__ . '/impressum.php';
require __DIR__ . '/templates/footer.php'; 
?>
