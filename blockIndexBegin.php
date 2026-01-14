<?php
session_start();

if(isset($_POST['logout'])) {
  session_destroy();
  session_start();
}
 ?>

<!DOCTYPE html>
<html>
<head>
<title>Mensaplan</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="./style.css">
<link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
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
input[type='radio'] {
     transform: scale(2.5);
 }
</style>
</head>
<body class="w3-black">

<!-- Icon Bar (Sidebar - hidden on small screens) -->
<nav class="w3-sidebar w3-bar-block w3-small w3-hide-small w3-center">
  <!-- Avatar image in top left corner -->
  <img src="images/croissant.jpg" style="height:120px">
  <a href="#" class="w3-bar-item w3-button w3-padding-large w3-black">
    <i class="fa fa-star w3-xxlarge"></i>
    <p>MENSA-ESSEN</p>
  </a>
  <a href="#abstimmung" class="w3-bar-item w3-button w3-padding-large w3-hover-black">
    <i class="fa fa-pie-chart w3-xxlarge"></i>
    <p>ABSTIMMUNG</p>
  </a>
  <a href="#kontakt" class="w3-bar-item w3-button w3-padding-large w3-hover-black">
    <i class="fa fa-envelope w3-xxlarge"></i>
    <p>KONTAKT</p>
  </a>
  <a href="#login" class="w3-bar-item w3-button w3-padding-large w3-hover-black">
    <i class="fa fa-unlock w3-xxlarge"></i>
    <p>LOGIN</p>
  </a>
  <a href="#impressum" class="w3-bar-item w3-button w3-padding-large w3-hover-black">
    <i class="fa fa-address-card w3-xxlarge"></i>
    <p>IMPRESSUM</p>
  </a>
</nav>

<!-- Navbar on small screens (Hidden on medium and large screens) -->
<div class="w3-top w3-hide-large w3-hide-medium" id="myNavbar">
  <div class="w3-bar w3-black w3-opacity w3-hover-opacity-off w3-center w3-small">
    <a href="#" class="w3-bar-item w3-button" style="width:20% !important">MENSA-ESSEN</a>
    <a href="#abstimmung" class="w3-bar-item w3-button" style="width:20% !important">ABSTIMMUNG</a>
    <a href="#kontakt" class="w3-bar-item w3-button" style="width:20% !important">KONTAKT</a>
    <a href="#login" class="w3-bar-item w3-button" style="width:20% !important">LOGIN</a>
    <a href="#impressum" class="w3-bar-item w3-button" style="width:20% !important">IMPRESSUM</a>
  </div>
</div>

<!-- Page Content -->
<div class="w3-padding-large" id="main">
  <!-- Header/Home -->
  <header class="w3-container w3-padding-32 w3-center w3-black" id="home">
    <?php
      require 'conn.php';

      $firstDay;
      $datumSql = "SELECT * FROM wochenplan order by tag_id ASC LIMIT 1";
      foreach ($conn->query($datumSql) as $datumRow) {
        $firstDay = new DateTime($datumRow['datum']);
        $firstDayString = $firstDay->format('d.m.y');
      }

      $secondDay;
      $datumSql = "SELECT * FROM wochenplan order by tag_id DESC LIMIT 1";
      foreach ($conn->query($datumSql) as $datumRow) {
        $secondDay = new DateTime($datumRow['datum']);
        $secondDayString = $secondDay->format('d.m.y');
      }

      echo "<h1 class='w3-jumbo'>Mensa - Speiseplan</h1>";
      echo "<h2 class='w3-xxlarge'>($firstDayString - $secondDayString)</h2>";
      ?>

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


function wochenplanErstellen($tag) {

  $vollkost = "";
  $leichteVollkost = "";
  $vegetarisch = "";

  require 'conn.php';


  $wochenplanSql = "SELECT * FROM wochenplan WHERE tag = '".$tag."'";
  foreach ($conn->query($wochenplanSql) as $wochenplanRow) {

    $speiseSql = "SELECT * FROM speisen WHERE speise_nr = ".$wochenplanRow['speise_nr']." AND speise_art = 'Vollkost'";
    foreach ($conn->query($speiseSql) as $speiseRow) {
      $vollkost = $speiseRow['speise_name'];
    }
    $speiseSql = "SELECT * FROM speisen WHERE speise_nr = ".$wochenplanRow['speise_nr']." AND speise_art = 'Leichte Vollkost'";
    foreach ($conn->query($speiseSql) as $speiseRow) {
      $leichteVollkost = $speiseRow['speise_name'];
    }
    $speiseSql = "SELECT * FROM speisen WHERE speise_nr = ".$wochenplanRow['speise_nr']." AND speise_art = 'Vegetarisch'";
    foreach ($conn->query($speiseSql) as $speiseRow) {
      $vegetarisch = $speiseRow['speise_name'];
    }
  }

  echo "<tr>";
    echo "<td><b>".$tag."</b></td>";
    echo "<td>".$vollkost."</td>";
    echo "<td>".$leichteVollkost."</td>";
    echo "<td>".$vegetarisch."</td>";
  echo "</tr>";
}

wochenplanErstellen("Montag");
wochenplanErstellen("Dienstag");
wochenplanErstellen("Mittwoch");
wochenplanErstellen("Donnerstag");
wochenplanErstellen("Freitag");
wochenplanErstellen("Samstag");
wochenplanErstellen("Sonntag");
?>

</table>

<a href="./zusatzstoffe.php" class="w3-bar-item w3-button w3-padding-large w3-black" target="_blank  ">
  <i class="fa fa-asterisk w3-xxlarge"></i>
  <p>Zusatzstoffe</p>
</a>


</div>
