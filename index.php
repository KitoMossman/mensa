
<?php

require 'blockIndexBegin.php';

 ?>

  <!-- Abstimmung Section -->
  <div class="w3-container w3-padding-64 w3-center" id="abstimmung">

    <?php
      require 'conn.php';

      $firstDay;
      $datumSql = "SELECT * FROM wunschplan order by wtag_id ASC LIMIT 1";
      foreach ($conn->query($datumSql) as $datumRow) {
        $firstDay = new DateTime($datumRow['datum']);
        $firstDayString = $firstDay->format('d.m.y');
      }

      $secondDay;
      $datumSql = "SELECT * FROM wunschplan order by wtag_id DESC LIMIT 1";
      foreach ($conn->query($datumSql) as $datumRow) {
        $secondDay = new DateTime($datumRow['datum']);
        $secondDayString = $secondDay->format('d.m.y');
      }

      echo "<h1 class='w3-jumbo'>Abstimmung für nächste Woche</h1>";
      echo "<h2 class='w3-xxlarge'>($firstDayString - $secondDayString)</h2>";
      ?>
    <h2 class="w3-xxlarge w3-text-red">! Bitte höchstens 1 mal abstimmen !</h2>

    <form action="./abstimmung.php#abstimmung" method="post">
    <div class="w3-responsive">

  <table class="w3-table-all w3-large w3-text-black">
  <tr class="w3-blue w3-center">
    <th class="w3-center">Wochentag</th>
    <th class="w3-center">Auswahl</th>
    <th class="w3-center">Vollkost</th>
    <th class="w3-center">Auswahl</th>
    <th class="w3-center">Leichte Vollkost</th>
    <th class="w3-center">Auswahl</th>
    <th class="w3-center">Vegetarisch</th>
  </tr>

  <?php


  function wunschplanErstellen($tag) {

    $vollkost = "";
    $vollkost_Nr = 0;
    $leichteVollkost = "";
    $leichteVollkost_Nr = 0;
    $vegetarisch = "";
    $vegetarisch_Nr = 0;

    require 'conn.php';


    $wochenplanSql = "SELECT * FROM wunschplan WHERE tag = '".$tag."'";
    foreach ($conn->query($wochenplanSql) as $wochenplanRow) {

      $speiseSql = "SELECT * FROM speisen WHERE speise_nr = ".$wochenplanRow['speise_nr']." AND speise_art = 'Vollkost'";
      foreach ($conn->query($speiseSql) as $speiseRow) {
        $vollkost = $speiseRow['speise_name'];
        $vollkost_Nr = $speiseRow['speise_nr'];
      }
      $speiseSql = "SELECT * FROM speisen WHERE speise_nr = ".$wochenplanRow['speise_nr']." AND speise_art = 'Leichte Vollkost'";
      foreach ($conn->query($speiseSql) as $speiseRow) {
        $leichteVollkost = $speiseRow['speise_name'];
        $leichteVollkost_Nr = $speiseRow['speise_nr'];
      }
      $speiseSql = "SELECT * FROM speisen WHERE speise_nr = ".$wochenplanRow['speise_nr']." AND speise_art = 'Vegetarisch'";
      foreach ($conn->query($speiseSql) as $speiseRow) {
        $vegetarisch = $speiseRow['speise_name'];
        $vegetarisch_Nr = $speiseRow['speise_nr'];
      }
    }

    echo "<tr>";
      echo "<td><b>".$tag."</b></td>";
      if ($vollkost == "") {
        echo "<td class='w3-center'><input type='radio' name='$tag' value=$vollkost_Nr disabled></td>";
      } else {
        echo "<td class='w3-center'><input type='radio' name='$tag' value=$vollkost_Nr></td>";
      }
      echo "<td>".$vollkost."</td>";
      if ($leichteVollkost == "") {
        echo "<td class='w3-center'><input type='radio' name='$tag' value=$leichteVollkost_Nr disabled></td>";
      } else {
        echo "<td class='w3-center'><input type='radio' name='$tag' value=$leichteVollkost_Nr></td>";
      }
      echo "<td>".$leichteVollkost."</td>";
      if ($vegetarisch == "") {
        echo "<td class='w3-center'><input type='radio' name='$tag' value=$vegetarisch_Nr disabled></td>";
      } else {
        echo "<td class='w3-center'><input type='radio' name='$tag' value=$vegetarisch_Nr></td>";
      }
      echo "<td>".$vegetarisch."</td>";
    echo "</tr>";
  }

  wunschplanErstellen("Montag");
  wunschplanErstellen("Dienstag");
  wunschplanErstellen("Mittwoch");
  wunschplanErstellen("Donnerstag");
  wunschplanErstellen("Freitag");
  wunschplanErstellen("Samstag");
  wunschplanErstellen("Sonntag");
  ?>

  </table>
  </div>
  <br>
  <br>
  <button class="w3-button w3-jumbo w3-blue w3-padding-large" type="submit">
    <i class="fa fa-paper-plane"></i> Abstimmen
  </button>
  </form>
  </div>

  <?php
require 'conn.php';
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
  $beginnDate;
  $endDate;
  $sql = "SELECT * FROM umfrage";
  foreach ($conn->query($sql) as $row) {
    $beginnDate = new DateTime($row['beginn']);
    $endDate = new DateTime($row['ende']);
    $beginn = $beginnDate ->format('d.m.y');
    $end = $endDate->format('d.m.y');
  }
  $now = new DateTime('now');
  if ($now >= $beginnDate && $now <= $endDate) {
    echo "<center>";
    echo "<h1 class='w3-jumbo'>Eure Wunschspeisen</h1>";
    echo "<em><h2 class='w3-xxlarge w3-text-red'>Aktuell: Umfrage vom ".$beginn." bis ".$end."</h2>";
    echo "<h2 class='w3-xxlarge'>Neben dem Wahlmenü habt Ihr ab sofort auch die Möglichkeit den Speiseplan selbst zu gestalten.</h2>";
    echo "<h2 class='w3-xxlarge'>Alle drei Monate könnt Ihr eigene Wunschspeisen vorschlagen und für Vorschläge von anderen stimmen.</h2>";
    echo "<h2 class='w3-xxlarge'>Die <b>Top-Sieben</b> Vorschläge werden innerhalb von 2-4 Wochen in den Speiseplan aufgenommen.</h2>";
    echo "<h2 class='w3-xxlarge'>Jeder hat eine Stimme</h2></em>";
    echo "<a href='./wunschTabelle.php' class='w3-bar-item w3-button w3-padding-large w3-black' target='_blank '>";
      echo "<i class='fa fa-line-chart w3-jumbo'></i>";
      echo "<p>Zur Umfrage</p>";
    echo "</a>";
    echo "</center>";
  }
}
?>


  <?php

  require 'blockIndexEnd.php';

   ?>
