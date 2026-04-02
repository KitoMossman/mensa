<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/Database.php';

initSession();
$pdo = Database::getInstance()->getConnection();

$pageTitle = 'Zusatzstoffe';
$sidebarHtml = '
  <a href="#" class="w3-bar-item w3-button w3-padding-large w3-black">
    <i class="fa fa-asterisk w3-xxlarge"></i><p>ZUSATZSTOFFE</p>
  </a>
  <a href="javascript:window.close();" class="w3-bar-item w3-button w3-padding-large w3-hover-black">
    <i class="fa fa-close w3-xxlarge"></i><p>SCHLIESSEN</p>
  </a>
';

$navbarSmallHtml = '
    <a href="#" class="w3-bar-item w3-button" style="width:100% !important">Zusatzstoffe</a>
';

require __DIR__ . '/templates/header.php';
?>

  <header class="w3-container w3-padding-32 w3-center w3-black" id="home">
    <h1 class="w3-jumbo">Zusatzstoffe</h1>
  </header>

  <div class="w3-responsive">
    <table class="w3-table-all w3-large w3-text-black">
      <tr class="w3-green">
        <th>Nummer</th>
        <th>Zusatzstoff</th>
      </tr>
      <?php
      $stmt = $pdo->query("SELECT * FROM zusatzstoffe ORDER BY zusatzstoff_nr");
      while ($row = $stmt->fetch()) {
          echo "<tr>";
          echo "<td>" . h($row['zusatzstoff_nr']) . "</td>";
          echo "<td>" . h($row['bezeichnung']) . "</td>";
          echo "</tr>";
      }
      ?>
    </table>
  </div>

<?php 
require_once __DIR__ . '/impressum.php';
require __DIR__ . '/templates/footer.php'; 
?>
