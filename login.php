
<?php
session_start();
 ?>


<!DOCTYPE html>
<html>
<head>
<title>Kueche Admin</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
body, h1,h2,h3,h4,h5,h6 {font-family: "Arial", sans-serif}
.w3-row-padding img {margin-bottom: 12px}
/* Set the width of the sidebar to 120px */
.w3-sidebar {width: 120px;background: #222;}
/* Add a left margin to the "page content" that matches the width of the sidebar (120px) */
#main {margin-left: 120px}
/* Remove margins from "page content" on small screens */
@media only screen and (max-width: 600px) {#main {margin-left: 0}}
</style>
</head>
<body class="w3-black">

  <?php

  if(!isset($_SESSION['admin'])) {
      die('Bitte zuerst <a href="./index.php#login">einloggen</a>');
  }
  ?>

<!-- Icon Bar (Sidebar - hidden on small screens) -->
<nav class="w3-sidebar w3-bar-block w3-small w3-hide-small w3-center">
  <!-- Avatar image in top left corner -->
  <img src="images/croissant.jpg" style="height:120px">
  <a href="#" class="w3-bar-item w3-button w3-padding-large w3-black">
    <i class="fa fa-bar-chart w3-xxlarge"></i>
    <p>AUSWERTUNG</p>
  </a>
  <a href="#speisen" class="w3-bar-item w3-button w3-padding-large w3-hover-black">
    <i class="fa fa-bars w3-xxlarge"></i>
    <p>SPEISEN</p>
  </a>
  <a href="#wochenplan" class="w3-bar-item w3-button w3-padding-large w3-hover-black">
    <i class="fa fa-table w3-xxlarge"></i>
    <p>WOCHENPLAN</p>
  </a>
  <a href="#wunschplan" class="w3-bar-item w3-button w3-padding-large w3-hover-black">
    <i class="fa fa-table w3-xxlarge"></i>
    <p>WUNSCHPLAN</p>
  </a>
  <a href="./umfrage.php" class="w3-bar-item w3-button w3-padding-large w3-hover-black">
    <i class="fa fa-line-chart w3-xxlarge"></i>
    <p>UMFRAGE</p>
  </a>
    <form action="./index.php" method="post">
      <input type="hidden" name="logout">
      <button type="submit" class="w3-bar-item w3-button w3-padding-large w3-hover-black">
        <i class="fa fa-lock w3-xxlarge"></i>
        <p>LOGOUT</p>
      </button>
    </form>
</nav>

<!-- Navbar on small screens (Hidden on medium and large screens) -->
<div class="w3-top w3-hide-large w3-hide-medium" id="myNavbar">
  <div class="w3-bar w3-black w3-opacity w3-hover-opacity-off w3-center w3-small">
    <a href="#" class="w3-bar-item w3-button" style="width:20% !important">AUSWERTUNG</a>
    <a href="#speisen" class="w3-bar-item w3-button" style="width:20% !important">SPEISEN</a>
    <a href="#wochenplan" class="w3-bar-item w3-button" style="width:20% !important">WOCHENPLAN</a>
    <a href="#wunschplan" class="w3-bar-item w3-button" style="width:20% !important">WUNSCHPLAN</a>
    <a href="./index.php" class="w3-bar-item w3-button" style="width:20% !important">LOGOUT</a>
  </div>
</div>


<!-- Page Content -->
<div class="w3-padding-large" id="main">
  <!-- Header/Home -->
  <header class="w3-container w3-padding-32 w3-center w3-black" id="home">
    <h1 class="w3-jumbo">Küche - Administration</h1>
  </header>

  <div class="w3-responsive">
<table class="w3-table-all w3-text-black">

<tr class="w3-green w3-center">
  <th>Wochentag</th>
  <th>Speise</th>
  <th>Speiseart</th>
  <th>Datum</th>
  <th>Anzahl</th>
  <th>Prozent</th>
</tr>

<?php

    require 'conn.php';

    $gesamtAnzahlProTag = array("Montag" => 0, "Dienstag" => 0, "Mittwoch" => 0, "Donnerstag" => 0, "Freitag" => 0, "Samstag" => 0, "Sonntag" => 0);

    $wunschSql = "SELECT * FROM wunschplan";
    foreach ($conn->query($wunschSql) as $wunschRow) {

      $abstimmungsSql = "SELECT speise_nr, COUNT(*) FROM abstimmung WHERE speise_nr=".$wunschRow['speise_nr']." GROUP BY speise_nr ORDER BY Count(*) DESC";
      foreach ($conn->query($abstimmungsSql) as $abstimmungsRow) {

        $speisenSql = "SELECT * FROM speisen WHERE speise_nr='".$abstimmungsRow['speise_nr']."' ORDER BY speise_art";
        foreach ($conn->query($speisenSql) as $speisenRow) {
              $gesamtAnzahlProTag[$wunschRow['tag']] += $abstimmungsRow[1];
        }
      }
    }

    $tag = "Montag";
    $wunschSql = "SELECT * FROM wunschplan";
    foreach ($conn->query($wunschSql) as $wunschRow) {

      $abstimmungsSql = "SELECT speise_nr, COUNT(*) FROM abstimmung WHERE speise_nr=".$wunschRow['speise_nr']." GROUP BY speise_nr ORDER BY Count(*) DESC";
      foreach ($conn->query($abstimmungsSql) as $abstimmungsRow) {

        $speisenSql = "SELECT * FROM speisen WHERE speise_nr='".$abstimmungsRow['speise_nr']."' ORDER BY speise_art";
        foreach ($conn->query($speisenSql) as $speisenRow) {

          if($wunschRow['tag'] != $tag) {
            echo "<tr>";
            echo "<td></td>";
            echo "<td></td>";
            echo "<td></td>";
            echo "<td></td>";
            echo "<td></td>";
            echo "<td></td>";
            echo "</tr>";
          }
          $tag = $wunschRow['tag'];

            echo "<tr>";
              echo "<td>".$wunschRow['tag']."</td>";
              echo "<td>".$speisenRow['speise_name']."</td>";
              echo "<td>".$speisenRow['speise_art']."</td>";
              $datum = (new DateTime($wunschRow['datum']))->format('d.m.Y');
              echo "<td>".$datum."</td>";
              echo "<td>".$abstimmungsRow[1]."</td>";
              echo "<td>".number_format($abstimmungsRow[1]*100/$gesamtAnzahlProTag[$wunschRow['tag']], 0)."%</td>";
            echo "</tr>";
        }
      }
    }

 ?>
</table>
<a href="./zusatzstoffe.php" class="w3-bar-item w3-button w3-padding-large w3-black" target="_blank  ">
  <i class="fa fa-asterisk w3-xxlarge"></i>
  <p>Zusatzstoffe</p>
</a>
<a href="./nachrichten.php" class="w3-bar-item w3-button w3-padding-large w3-black" target="_blank  ">
  <i class="fa fa-envelope-open w3-xxlarge"></i>
  <p>Nachrichten</p>
</a>
</div>

</div>

  <!-- Speisen hinzufuegen Section -->
  <div class="w3-padding-64 w3-content" id="speisen">

    <h1 class="w3-jumbo w3-center">Speise hinzufügen</h1>

    <?php

      require 'conn.php';


      if(isset($_POST['speise'])) {
        echo "Speise wurde erfolgreich hinzugefügt";
        $statement = $conn->prepare("REPLACE INTO speisen (speise_name, speise_art) VALUES (?, ?)");
        $statement->execute(array($_POST['speise'],$_POST['speise_art']));
        unset($_POST);
      }

      echo "<form action='./login.php#speisen' method='post'>";
      echo "<p><input class='w3-input w3-padding-16' type='text' name='speise' placeholder='Speise' required></p>";
      echo "<select class='w3-input w3-padding-16' name='speise_art' required>";
      echo "<option value='Vollkost'>Vollkost</option>";
      echo "<option value='Leichte Vollkost'>Leichte Vollkost</option>";
      echo "<option value='Vegetarisch'>Vegetarisch</option>";
      echo "</select>";
      echo "<p>";
      echo "<button class='w3-button w3-blue w3-padding-large' type='submit'>";
      echo "<i class='fa fa-envelope'></i> Hinzufügen";
      echo "</button>";
      echo "</p>";
      echo "</form>";
     ?>

  </div>

  <!-- Speisen loeschen Section -->
  <div class="w3-padding-64 w3-content">

    <h1 class="w3-jumbo w3-center">Speise löschen</h1>

    <?php

      require 'conn.php';


      if(isset($_POST['entfernen'])) {
        echo "Speise wurde erfolgreich gelöscht";
        $entfernenSql = "DELETE FROM speisen WHERE speise_nr=".$_POST['entfernen'];
        $conn->query($entfernenSql);
        unset($_POST);
      }

      echo "<form action='./login.php#speisen' method='post'>";
      echo "<select class='w3-input w3-padding-16' name='entfernen' required>";

      $speisenSql = "SELECT * from speisen ORDER BY speise_art, speise_name";
      foreach ($conn->query($speisenSql) as $speisenRow) {

        echo "<option value=".$speisenRow['speise_nr'].">".$speisenRow['speise_art']." - ".$speisenRow['speise_name']."</option>";

      }

      echo "</select>";
      echo "<p>";
      echo "<button class='w3-button w3-red w3-padding-large' type='submit'>";
      echo "<i class='fa fa-envelope'></i> Löschen";
      echo "</button>";
      echo "</p>";
      echo "</form>";
     ?>

  </div>

  <!-- Wochenplan Section -->
  <div class="w3-padding-large" id="main">
    <!-- Header/Home -->
    <header class="w3-container w3-padding-32 w3-center w3-black" id="wochenplan">
      <h1 class="w3-jumbo">Aktuellen Wochenplan erstellen</h1>
    </header>


    <div class="w3-responsive">
  <table class="w3-table-all w3-large w3-text-black">

  <tr class="w3-green w3-center">
    <th class="w3-center">Wochentag</th>
    <th class="w3-center">Vollkost</th>
    <th class="w3-center">Leichte Vollkost</th>
    <th class="w3-center">Vegetarisch</th>
  </tr>

  <?php


  function wochenplanErstellen($tag, $table) {

    require 'conn.php';

    echo "<tr>";
      echo "<td><b>".$tag."</b></td>";
      echo "<td>";
      echo "<select class='w3-input w3-padding-16' name=".$table."_".$tag."_Vollkost>";
      echo "<option value=0></option>";
      $speisenSql = "SELECT * from speisen WHERE speise_art='Vollkost' ORDER BY speise_name";
      foreach ($conn->query($speisenSql) as $speisenRow) {
        $wunschSql = "SELECT EXISTS(SELECT * from wunschplan WHERE speise_nr=".$speisenRow['speise_nr']." AND tag='".$tag."')";
        foreach ($conn->query($wunschSql) as $wunschRow) {
          if($table=="wochenplan" && $wunschRow[0]==1) {
            echo "<option value=".$speisenRow['speise_nr']." selected>".$speisenRow['speise_name']."</option>";
          } else {
            echo "<option value=".$speisenRow['speise_nr'].">".$speisenRow['speise_name']."</option>";
          }
        }
      }
      echo "</td>";
      echo "<td>";
      echo "<select class='w3-input w3-padding-16' name=".$table."_".$tag."_Leichte_Vollkost>";
      echo "<option value=0></option>";
      $speisenSql = "SELECT * from speisen WHERE speise_art='Leichte Vollkost' ORDER BY speise_name";
      foreach ($conn->query($speisenSql) as $speisenRow) {
        $wunschSql = "SELECT EXISTS(SELECT * from wunschplan WHERE speise_nr=".$speisenRow['speise_nr']." AND tag='".$tag."')";
        foreach ($conn->query($wunschSql) as $wunschRow) {
          if($table=="wochenplan" && $wunschRow[0]==1) {
            echo "<option value=".$speisenRow['speise_nr']." selected>".$speisenRow['speise_name']."</option>";
          } else {
            echo "<option value=".$speisenRow['speise_nr'].">".$speisenRow['speise_name']."</option>";
          }
        }
      }
      echo "</td>";
      echo "<td>";
      echo "<select class='w3-input w3-padding-16' name=".$table."_".$tag."_Vegetarisch>";
      echo "<option value=0></option>";
      $speisenSql = "SELECT * from speisen WHERE speise_art='Vegetarisch' ORDER BY speise_name";
      foreach ($conn->query($speisenSql) as $speisenRow) {
        $wunschSql = "SELECT EXISTS(SELECT * from wunschplan WHERE speise_nr=".$speisenRow['speise_nr']." AND tag='".$tag."')";
        foreach ($conn->query($wunschSql) as $wunschRow) {
          if($table=="wochenplan" && $wunschRow[0]==1) {
            echo "<option value=".$speisenRow['speise_nr']." selected>".$speisenRow['speise_name']."</option>";
          } else {
            echo "<option value=".$speisenRow['speise_nr'].">".$speisenRow['speise_name']."</option>";
          }
        }
      }
      echo "</td>";
    echo "</tr>";
  }

  function wochenplanUeberpruefen($tag, $table, $datum) {

    require 'conn.php';

    echo "<tr>";
      echo "<td><b>".$tag."</b></td>";
      echo "<td>";
      echo "<select class='w3-input w3-padding-16' name=".$table."_".$tag."_Vollkost>";
      echo "<option value=0></option>";
      $speisenSql = "SELECT * from speisen WHERE speise_art='Vollkost' ORDER BY speise_name";
      foreach ($conn->query($speisenSql) as $speisenRow) {
        if ($speisenRow['speise_nr'] == $_POST[''.$table."_".$tag.'_Vollkost']) {
          echo "<option value=".$speisenRow['speise_nr']." selected>".$speisenRow['speise_name']."</option>";
          $statement = $conn->prepare("INSERT INTO ".$table." (tag, speise_nr, datum) VALUES (?, ?, ?)");
          $statement->execute(array($tag, $_POST[''.$table."_".$tag.'_Vollkost'], $datum->format('Y-m-d')));
        } else {
          echo "<option value=".$speisenRow['speise_nr'].">".$speisenRow['speise_name']."</option>";
        }
      }
      echo "</td>";
      echo "<td>";
      echo "<select class='w3-input w3-padding-16' name=".$table."_".$tag."_Leichte_Vollkost>";
      echo "<option value=0></option>";
      $speisenSql = "SELECT * from speisen WHERE speise_art='Leichte Vollkost' ORDER BY speise_name";
      foreach ($conn->query($speisenSql) as $speisenRow) {
        if ($speisenRow['speise_nr'] == $_POST[''.$table."_".$tag.'_Leichte_Vollkost']) {
          echo "<option value=".$speisenRow['speise_nr']." selected>".$speisenRow['speise_name']."</option>";
          $statement = $conn->prepare("INSERT INTO ".$table." (tag, speise_nr, datum) VALUES (?, ?, ?)");
          $statement->execute(array($tag, $_POST[''.$table."_".$tag.'_Leichte_Vollkost'], $datum->format('Y-m-d')));
        } else {
          echo "<option value=".$speisenRow['speise_nr'].">".$speisenRow['speise_name']."</option>";
        }
      }
      echo "</td>";
      echo "<td>";
      echo "<select class='w3-input w3-padding-16' name=".$table."_".$tag."_Vegetarisch>";
      echo "<option value=0></option>";
      $speisenSql = "SELECT * from speisen WHERE speise_art='Vegetarisch' ORDER BY speise_name";
      foreach ($conn->query($speisenSql) as $speisenRow) {
        if ($speisenRow['speise_nr'] == $_POST[''.$table."_".$tag.'_Vegetarisch']) {
          echo "<option value=".$speisenRow['speise_nr']." selected>".$speisenRow['speise_name']."</option>";
          $statement = $conn->prepare("INSERT INTO ".$table." (tag, speise_nr, datum) VALUES (?, ?, ?)");
          $statement->execute(array($tag, $_POST[''.$table."_".$tag.'_Vegetarisch'], $datum->format('Y-m-d')));
        } else {
          echo "<option value=".$speisenRow['speise_nr'].">".$speisenRow['speise_name']."</option>";
        }
      }
      echo "</td>";
    echo "</tr>";
  }

  echo "<form action='./login.php#wochenplan' method='post'>";

  echo "<center><label for='date'>Beginn der Woche: </label>";
  echo "<input type='date' id='date' name='date' required></center>";

  if(isset($_POST['wochenplan_Montag_Vollkost'])) {
    $statement = $conn->prepare("DELETE FROM wochenplan");
    $statement->execute();
    echo "Wochenplan erfolgreich aktualisiert";
    wochenplanUeberpruefen("Montag", "wochenplan", ((new DateTime($_POST['date']))->modify('+0 days')));
    wochenplanUeberpruefen("Dienstag", "wochenplan", ((new DateTime($_POST['date']))->modify('+1 days')));
    wochenplanUeberpruefen("Mittwoch", "wochenplan", ((new DateTime($_POST['date']))->modify('+2 days')));
    wochenplanUeberpruefen("Donnerstag", "wochenplan", ((new DateTime($_POST['date']))->modify('+3 days')));
    wochenplanUeberpruefen("Freitag", "wochenplan", ((new DateTime($_POST['date']))->modify('+4 days')));
    wochenplanUeberpruefen("Samstag", "wochenplan", ((new DateTime($_POST['date']))->modify('+5 days')));
    wochenplanUeberpruefen("Sonntag", "wochenplan", ((new DateTime($_POST['date']))->modify('+6 days')));
  } else {
    wochenplanErstellen("Montag", "wochenplan");
    wochenplanErstellen("Dienstag", "wochenplan");
    wochenplanErstellen("Mittwoch", "wochenplan");
    wochenplanErstellen("Donnerstag", "wochenplan");
    wochenplanErstellen("Freitag", "wochenplan");
    wochenplanErstellen("Samstag", "wochenplan");
    wochenplanErstellen("Sonntag", "wochenplan");
  }



  echo "<p>";
  echo "<button class='w3-button w3-green w3-padding-large' type='submit'>";
  echo "<i class='fa fa-envelope'></i> Bestätigen";
  echo "</button>";
  echo "</p>";
  echo "</form>";
  ?>

  </table>

  <!-- End Wochenplan Section -->
  </div>

  <!-- Wunschplan Section -->
  <div class="w3-padding-large" id="main2">
    <!-- Header/Home -->
    <header class="w3-context w3-padding-32 w3-center w3-black" id="wunschplan">
      <h1 class="w3-jumbo">Nächsten Wunschplan erstellen</h1>
    </header>


    <div class="w3-responsive">
  <table class="w3-table-all w3-large w3-text-black">

  <tr class="w3-blue w3-center">
    <th class="w3-center">Wochentag</th>
    <th class="w3-center">Vollkost</th>
    <th class="w3-center">Leichte Vollkost</th>
    <th class="w3-center">Vegetarisch</th>
  </tr>

  <?php

  echo "<form action='./login.php#wunschplan' method='post'>";

  echo "<center><label for='dateWunsch'>Beginn der Woche: </label>";
  echo "<input type='date' id='dateWunsch' name='dateWunsch' required></center>";

  if(isset($_POST['wunschplan_Montag_Vollkost'])) {
    $statement = $conn->prepare("DELETE FROM wunschplan");
    $statement->execute();
    $statement = $conn->prepare("DELETE FROM abstimmung");
    $statement->execute();
    echo "Wunschplan erfolgreich aktualisiert & Abstimmung zurückgesetzt";
    wochenplanUeberpruefen("Montag", "wunschplan", ((new DateTime($_POST['dateWunsch']))->modify('+0 days')));
    wochenplanUeberpruefen("Dienstag", "wunschplan", ((new DateTime($_POST['dateWunsch']))->modify('+1 days')));
    wochenplanUeberpruefen("Mittwoch", "wunschplan", ((new DateTime($_POST['dateWunsch']))->modify('+2 days')));
    wochenplanUeberpruefen("Donnerstag", "wunschplan", ((new DateTime($_POST['dateWunsch']))->modify('+3 days')));
    wochenplanUeberpruefen("Freitag", "wunschplan", ((new DateTime($_POST['dateWunsch']))->modify('+4 days')));
    wochenplanUeberpruefen("Samstag", "wunschplan", ((new DateTime($_POST['dateWunsch']))->modify('+5 days')));
    wochenplanUeberpruefen("Sonntag", "wunschplan", ((new DateTime($_POST['dateWunsch']))->modify('+6 days')));
  } else {
    wochenplanErstellen("Montag", "wunschplan");
    wochenplanErstellen("Dienstag", "wunschplan");
    wochenplanErstellen("Mittwoch", "wunschplan");
    wochenplanErstellen("Donnerstag", "wunschplan");
    wochenplanErstellen("Freitag", "wunschplan");
    wochenplanErstellen("Samstag", "wunschplan");
    wochenplanErstellen("Sonntag", "wunschplan");
  }



  echo "<p>";
  echo "<button class='w3-button w3-blue w3-padding-large' type='submit'>";
  echo "<i class='fa fa-envelope'></i> Bestätigen & Abstimmung zurücksetzen";
  echo "</button>";
  echo "</p>";
  echo "</form>";
  ?>

  </table>

  <!-- End Contact Section -->
  </div>

  <?php
  require 'impressum.php';
   ?>

    <!-- Footer -->
  <footer class="w3-content w3-padding-64 w3-text-grey w3-xlarge">

    <p class="w3-medium">Powered by <a href="https://www.w3schools.com/w3css/default.asp" target="_blank" class="w3-hover-text-green">w3.css</a></p>
  <!-- End footer -->
  </footer>

<!-- END PAGE CONTENT -->
</div>

</body>
</html>
