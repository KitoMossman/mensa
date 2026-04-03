<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/Database.php';

initSession();
$pdo = Database::getInstance()->getConnection();

$pageTitle = 'Zusatzstoffe';
$sidebarHtml = '
  <a href="#" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-asterisk w3-xxlarge"></i><p>ZUSATZSTOFFE</p>
  </a>
  <a href="javascript:window.close();" class="w3-bar-item w3-button w3-padding-large">
    <i class="fa fa-close w3-xxlarge"></i><p>SCHLIESSEN</p>
  </a>
';

$navbarSmallHtml = '
    <a href="#" class="w3-bar-item w3-button" style="width:100% !important">Zusatzstoffe</a>
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
            <th>Nr.</th>
            <th>Bezeichnung</th>
          </tr>
          <?php
          $stmt = $pdo->query("SELECT * FROM zusatzstoffe ORDER BY zusatzstoff_nr");
          while ($row = $stmt->fetch()) {
              echo "<tr>";
              echo "<td><b>" . h($row['zusatzstoff_nr']) . "</b></td>";
              echo "<td>" . h($row['bezeichnung']) . "</td>";
              echo "</tr>";
          }
          ?>
        </table>
      </div>
    </div>
  </div>

<?php 
require_once __DIR__ . '/impressum.php';
require __DIR__ . '/templates/footer.php'; 
?>
