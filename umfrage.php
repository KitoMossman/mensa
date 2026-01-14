
<?php
session_start();
require 'conn.php';
if (isset($_POST['beginDate'])) {
  $statement = $conn->prepare("DELETE FROM umfrage");
  $statement->execute();

  $statement = $conn->prepare("INSERT INTO umfrage (beginn, ende) VALUES (?, ?)");
  $statement->execute(array($_POST['beginDate'], $_POST['endDate']));
}
if (isset($_POST['stop'])) {
  $hatUmfrage = false;
  $sql = "SELECT COUNT(*) FROM umfrage";
  foreach ($conn->query($sql) as $row) {
    if ($row[0] > 0) {
      $hatUmfrage = true;
    }
  }
  if ($hatUmfrage) {
    $statement = $conn->prepare("DELETE FROM umfrage");
    $statement->execute();
    $statement = $conn->prepare("CREATE TABLE IF NOT EXISTS ergebnis_umfrage SELECT * FROM wunschspeisen");
    $statement->execute();
    $statement = $conn->prepare("DELETE FROM ergebnis_umfrage");
    $statement->execute();
    $statement = $conn->prepare("INSERT INTO ergebnis_umfrage SELECT * FROM wunschspeisen");
    $statement->execute();
    $statement = $conn->prepare("DELETE FROM wunschspeisen");
    $statement->execute();
  }
}
 ?>

<!DOCTYPE html>
<html>
<head>
<title>Umfrage</title>
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
#myTable {
  border-collapse: collapse;
  width: 100%;
  border: 1px solid #ddd;
  font-size: 18px;
}

#myTable th, #myTable td {
  text-align: left;
  padding: 12px;
}

#myTable tr {
  border-bottom: 1px solid #ddd;
}

#myInput2 {
  background-position: 10px 10px;
  background-repeat: no-repeat;
  width: 100%;
  font-size: 16px;
  padding: 12px 20px 12px 40px;
  border: 1px solid #ddd;
  margin-bottom: 12px;
}

#myTable2 {
  border-collapse: collapse;
  width: 100%;
  border: 1px solid #ddd;
  font-size: 18px;
}

#myTable2 th, #myTable2 td {
  text-align: left;
  padding: 12px;
}

#myTable2 tr {
  border-bottom: 1px solid #ddd;
}

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
  <a href="./login.php" class="w3-bar-item w3-button w3-padding-large w3-black">
    <i class="fa fa-arrow-left w3-xxlarge"></i>
    <p>ZURÜCK</p>
  </a>
</nav>

<!-- Navbar on small screens (Hidden on medium and large screens) -->
<div class="w3-top w3-hide-large w3-hide-medium" id="myNavbar">
  <div class="w3-bar w3-black w3-opacity w3-hover-opacity-off w3-center w3-small">
    <a href="#" class="w3-bar-item w3-button" style="width:25% !important">Umfrage</a>
  </div>
</div>

<!-- Page Content -->
<div class="w3-padding-large" id="main">
  <!-- Header/Home -->
  <header class="w3-container w3-padding-32 w3-center w3-black" id="home">
    <h1 class="w3-jumbo">Umfrage</h1>
  </header>

<?php
require 'conn.php';

  echo "<center>";
  $hatUmfrage = false;
  $sql = "SELECT COUNT(*) FROM umfrage";
  foreach ($conn->query($sql) as $row) {
    if ($row[0] > 0) {
      $hatUmfrage = true;
    }
  }
  if ($hatUmfrage) {
    $beginn;
    $end;
    $sql = "SELECT * FROM umfrage";
    foreach ($conn->query($sql) as $row) {
      $beginn = (new DateTime($row['beginn']))->format('d.m.y');
      $end = (new DateTime($row['ende']))->format('d.m.y');
    }
    echo "<h2 class='w3-xxlarge'>Aktuell: Umfrage von ".$beginn." bis ".$end."</h1>";
  } else {
    echo "<h2 class='w3-xxlarge'>Aktuell: keine Umfrage</h1>";
  }

  echo "<br>";
  echo "<br>";
  echo "<form action='./umfrage.php' method='post'>";
      echo "<label for='beginDate' class='w3-xlarge'>Beginn: </label>";
      echo "<input type='date' id='beginDate' name='beginDate' class='w3-xlarge' required>";
      echo "<label for='endDate' class='w3-xlarge'> Ende: </label>";
      echo "<input type='date' id='endDate' name='endDate' class='w3-xlarge' required>";
      echo "<br>";
      echo "<br>";
      echo "<br>";
        echo "<button class='w3-button w3-green w3-padding-large w3-xxlarge' type='submit'>";
          echo "<i class='fa fa-play'></i> Starten";
        echo "</button>";

  echo "</form>";
  echo "<br>";
  echo "<br>";

  echo "<form action='./umfrage.php' method='post'>";
        echo "<button class='w3-button w3-blue w3-padding-large w3-xxlarge' id='stop' name='stop' type='submit'>";
          echo "<i class='fa fa-stop'></i> Beenden und Auswerten";
        echo "</button>";
  echo "</form>";

  echo "</center>";
  ?>


  <div id="vollkost">
  <header class="w3-container w3-padding-32 w3-center w3-black" id="vollkost_header">
    <h1 class="w3-jumbo">Ergebnis Vollkost Wunschspeisen</h1>
  </header>

  <table class="w3-pale-green" id="myTable">
    <tr class="header w3-green">
      <th style="width:10%;">Platz</th>
      <th style="width:15%;">Anzahl</th>
      <th style="width:15%;">Speiseart</th>
      <th style="width:15%;">Kategorie</th>
      <th style="width:45%;">Speise</th>
    </tr>
    <?php

      require 'conn.php';

      $platz = 1;
      $wunschSql = "SELECT * FROM ergebnis_umfrage where wunschspeise_art='Vollkost' order by wunschspeise_anzahl DESC, wunschspeise_kategorie";
      foreach ($conn->query($wunschSql) as $wunschRow) {
        echo "<tr>";
          echo "<td>".$platz++."</td>";
          echo "<td>".utf8_encode($wunschRow['wunschspeise_anzahl'])."</td>";
          echo "<td>".utf8_encode($wunschRow['wunschspeise_art'])."</td>";
          echo "<td>".utf8_encode($wunschRow['wunschspeise_kategorie'])."</td>";
          echo "<td>".utf8_encode($wunschRow['wunschspeise_name'])."</td>";
        echo "</tr>";
      }

    ?>

  </table>
  <br>
  <br>
  </div>


  <!---------Vegetarisch--------------->
  <div id="vegetarisch">

  <header class="w3-container w3-padding-32 w3-center w3-black" id="vegetarisch_header">
   <h1 class="w3-jumbo">Ergebnis Vegetarische Wunschspeisen</h1>
  </header>

  <table class="w3-pale-blue" id="myTable2">
   <tr class="header w3-blue">
     <th style="width:10%;">Platz</th>
     <th style="width:15%;">Anzahl</th>
     <th style="width:15%;">Speiseart</th>
     <th style="width:15%;">Kategorie</th>
     <th style="width:45%;">Speise</th>
   </tr>
   <?php

     require 'conn.php';

     $platz = 1;
     $wunschSql = "SELECT * FROM ergebnis_umfrage where wunschspeise_art='Vegetarisch' order by wunschspeise_anzahl DESC, wunschspeise_kategorie";
     foreach ($conn->query($wunschSql) as $wunschRow) {
       echo "<tr>";
         echo "<td>".$platz++."</td>";
         echo "<td>".utf8_encode($wunschRow['wunschspeise_anzahl'])."</td>";
         echo "<td>".utf8_encode($wunschRow['wunschspeise_art'])."</td>";
         echo "<td>".utf8_encode($wunschRow['wunschspeise_kategorie'])."</td>";
         echo "<td>".utf8_encode($wunschRow['wunschspeise_name'])."</td>";
       echo "</tr>";
     }

   ?>

  </table>
  <br>
  <br>
  </div>


<?php
require 'impressum.php';
 ?>

<!-- END PAGE CONTENT -->
</div>

</body>
</html>
