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
/* KORREKTUR: Gilt jetzt für #main UND #main2 */
#main, #main2 {margin-left: 120px}

/* Remove margins from "page content" on small screens */
@media only screen and (max-width: 600px) {#main, #main2 {margin-left: 0}}

/* Maximale Breite für Dropdowns, damit sie die Tabelle nicht unnötig sprengen */
td select.w3-input {
    max-width: 500px; 
    min-width: 150px;
    text-overflow: ellipsis;
}
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

      echo "<form action='#speisen' method='post'>";
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
      
  <!-- Speisen bearbeiten Section (NEU) -->
      
  <div class="w3-padding-64 w3-content" id="speisen_bearbeiten">

    <h1 class="w3-jumbo w3-center">Speise bearbeiten</h1>

    <?php
      require 'conn.php';

      // UPDATE LOGIK
      if(isset($_POST['edit_id']) && isset($_POST['edit_name']) && isset($_POST['edit_art'])) {
        $editSql = "UPDATE speisen SET speise_name = ?, speise_art = ? WHERE speise_nr = ?";
        $statement = $conn->prepare($editSql);
        if($statement->execute(array($_POST['edit_name'], $_POST['edit_art'], $_POST['edit_id']))) {
            echo "<p class='w3-text-green'>Speise wurde erfolgreich aktualisiert.</p>";
        } else {
            echo "<p class='w3-text-red'>Fehler beim Aktualisieren.</p>";
        }
        unset($_POST);
      }

      echo "<form action='#speisen_bearbeiten' method='post'>";
      
      echo "<p><label>1. Wähle das zu ändernde Gericht:</label></p>";
      // ID hinzugefügt für JS Zugriff: id='editSelect'
      // onchange Event hinzugefügt
      echo "<select class='w3-input w3-padding-16' name='edit_id' id='editSelect' required onchange='loadDishData()'>";
      
      echo "<option value='' disabled selected>-- Bitte wählen --</option>";

      $speisenSql = "SELECT * from speisen ORDER BY speise_art, speise_name";
      foreach ($conn->query($speisenSql) as $speisenRow) {
        // Wir speichern Name und Art in 'data-' Attributen, damit JS sie lesen kann.
        // htmlspecialchars verhindert Fehler bei Anführungszeichen im Namen.
        $safeName = htmlspecialchars($speisenRow['speise_name'], ENT_QUOTES);
        $safeArt = htmlspecialchars($speisenRow['speise_art'], ENT_QUOTES);
        
        echo "<option value='".$speisenRow['speise_nr']."' data-name='".$safeName."' data-art='".$safeArt."'>";
        echo $speisenRow['speise_art']." - ".$speisenRow['speise_name'];
        echo "</option>";
      }
      echo "</select>";

      echo "<p><label>2. Neue Speiseart:</label></p>";
      // ID hinzugefügt: id='editArtSelect'
      echo "<select class='w3-input w3-padding-16' name='edit_art' id='editArtSelect' required>";
      echo "<option value='Vollkost'>Vollkost</option>";
      echo "<option value='Leichte Vollkost'>Leichte Vollkost</option>";
      echo "<option value='Vegetarisch'>Vegetarisch</option>";
      echo "</select>";

      echo "<p><label>3. Neuer Name des Gerichts:</label></p>";
      // ID hinzugefügt: id='editNameInput'
      echo "<input class='w3-input w3-padding-16' type='text' name='edit_name' id='editNameInput' placeholder='Name erscheint hier...' required>";

      echo "<p>";
      echo "<button class='w3-button w3-orange w3-padding-large w3-text-white' type='submit'>";
      echo "<i class='fa fa-pencil'></i> Ändern";
      echo "</button>";
      echo "</p>";
      echo "</form>";
     ?>

     <script>
     function loadDishData() {
         // Elemente holen
         var selectBox = document.getElementById("editSelect");
         var nameInput = document.getElementById("editNameInput");
         var artSelect = document.getElementById("editArtSelect");
         
         // Die aktuell ausgewählte Option holen
         var selectedOption = selectBox.options[selectBox.selectedIndex];
         
         // Daten aus den data-Attributen lesen
         var dishName = selectedOption.getAttribute('data-name');
         var dishArt = selectedOption.getAttribute('data-art');
         
         // In die Felder schreiben, wenn Daten vorhanden sind
         if(dishName) {
            nameInput.value = dishName;
         }
         if(dishArt) {
            artSelect.value = dishArt;
         }
     }
     </script>

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

      echo "<form action='#speisen' method='post'>";
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
      require 'conn.php';

      // --- 1. FUNKTION ZUM ANZEIGEN DER DROPDOWNS ---
      // $sourceTable bestimmt, woher die "selected" Info kommt (wochenplan oder wunschplan)
      function wochenplanErstellen($tag, $formNamePrefix, $sourceTable) {
        
        require 'conn.php'; 

        echo "<tr>";
          echo "<td><b>".$tag."</b></td>";
          
          $arten = ["Vollkost", "Leichte Vollkost", "Vegetarisch"];
          
          foreach($arten as $art) {
            echo "<td>";
            $selectName = $formNamePrefix . "_" . $tag . "_" . str_replace(" ", "_", $art);
            
            echo "<select class='w3-input w3-padding-16' name='".$selectName."'>";
            echo "<option value=0></option>";

            $speisenSql = "SELECT * from speisen WHERE speise_art='".$art."' ORDER BY speise_name";
            
            foreach ($conn->query($speisenSql) as $speisenRow) {
                $isSelected = "";
                
                // Prüfen ob DIESE Speise in der Quelltabelle an diesem Tag existiert
                $checkQuery = "SELECT COUNT(*) FROM ".$sourceTable." WHERE tag='".$tag."' AND speise_nr=".$speisenRow['speise_nr'];
                $res = $conn->query($checkQuery)->fetchColumn();

                if ($res > 0) {
                    $isSelected = "selected";
                }

                echo "<option value=".$speisenRow['speise_nr']." ".$isSelected.">".$speisenRow['speise_name']."</option>";
            }
            echo "</select>";
            echo "</td>";
          }
        echo "</tr>";
      }

      // --- 2. FUNKTION ZUM SPEICHERN ---
      function wochenplanSpeichern($tag, $table, $datum) {
        require 'conn.php';
        
        $getPostVal = function($art) use ($table, $tag) {
            $name = $table."_".$tag."_".str_replace(" ", "_", $art);
            return isset($_POST[$name]) ? $_POST[$name] : 0;
        };

        $arten = ["Vollkost", "Leichte Vollkost", "Vegetarisch"];
        
        echo "<tr>";
        echo "<td><b>".$tag."</b></td>";
        
        foreach($arten as $art) {
            echo "<td>";
            $speiseNr = $getPostVal($art);
            
            if($speiseNr > 0) {
                 $statement = $conn->prepare("INSERT INTO ".$table." (tag, speise_nr, datum) VALUES (?, ?, ?)");
                 $statement->execute(array($tag, $speiseNr, $datum->format('Y-m-d')));
            }

            echo "<select class='w3-input w3-padding-16' disabled>"; 
            $sSql = "SELECT * FROM speisen WHERE speise_nr=".$speiseNr;
            foreach ($conn->query($sSql) as $row) {
                echo "<option selected>".$row['speise_name']."</option>";
            }
            if($speiseNr == 0) echo "<option selected>---</option>";
            echo "</select>";
            echo "</td>";
        }
        echo "</tr>";
      }

      // --- 3. HAUPTLOGIK & FORMULAR ---
      echo "<form action='#wochenplan' method='post'>";

      // Datum Logik
      $dateValue = "";

      // 1. Priorität: "Aus Wunschplan laden" geklickt -> Datum aus Wunschplan holen
      if(isset($_POST['load_from_wunsch'])) {
          $datumSql = "SELECT datum FROM wunschplan ORDER BY datum ASC LIMIT 1";
          foreach ($conn->query($datumSql) as $dRow) {
              $dateValue = (new DateTime($dRow['datum']))->format('Y-m-d');
          }
      } 
      // 2. Priorität: Formular wurde abgeschickt (z.B. nach Datumsauswahl oder Speichern) -> POST Datum nutzen
      elseif(isset($_POST['date'])) {
          $dateValue = $_POST['date']; 
      } 
      // 3. Fallback: Erstaufruf -> Datum aus aktuellem Wochenplan laden
      else {
          $datumSql = "SELECT datum FROM wochenplan order by tag_id ASC LIMIT 1";
          foreach ($conn->query($datumSql) as $datumRow) {
            $dateValue = (new DateTime($datumRow['datum']))->format('Y-m-d');
          }
      }

      // Wenn immer noch leer, heutiges Datum nehmen
      if(empty($dateValue)) $dateValue = date('Y-m-d'); 

      echo "<center><label for='date'>Beginn der Woche: </label>";
      echo "<input type='date' id='date' name='date' value='".$dateValue."' required></center>";
      echo "<br>";

      if(isset($_POST['wochenplan_save'])) {
        // A) SPEICHERN
        $statement = $conn->prepare("DELETE FROM wochenplan");
        $statement->execute();
        echo "<div class='w3-panel w3-green'><p>Wochenplan erfolgreich gespeichert!</p></div>";
        
        $d = new DateTime($_POST['date']);
        wochenplanSpeichern("Montag", "wochenplan", (clone $d)->modify('+0 days'));
        wochenplanSpeichern("Dienstag", "wochenplan", (clone $d)->modify('+1 days'));
        wochenplanSpeichern("Mittwoch", "wochenplan", (clone $d)->modify('+2 days'));
        wochenplanSpeichern("Donnerstag", "wochenplan", (clone $d)->modify('+3 days'));
        wochenplanSpeichern("Freitag", "wochenplan", (clone $d)->modify('+4 days'));
        wochenplanSpeichern("Samstag", "wochenplan", (clone $d)->modify('+5 days'));
        wochenplanSpeichern("Sonntag", "wochenplan", (clone $d)->modify('+6 days'));

      } else {
        // B) ANZEIGEN (Normal oder Import)
        $sourceTable = "wochenplan"; 
        $msg = "";

        if(isset($_POST['load_from_wunsch'])) {
            $sourceTable = "wunschplan"; 
            $msg = "<div class='w3-panel w3-blue'><p><i class='fa fa-info-circle'></i> Daten (und Datum) aus <b>Wunschplan</b> geladen. Bitte überprüfen und unten bestätigen.</p></div>";
        }

        echo $msg;

        wochenplanErstellen("Montag", "wochenplan", $sourceTable);
        wochenplanErstellen("Dienstag", "wochenplan", $sourceTable);
        wochenplanErstellen("Mittwoch", "wochenplan", $sourceTable);
        wochenplanErstellen("Donnerstag", "wochenplan", $sourceTable);
        wochenplanErstellen("Freitag", "wochenplan", $sourceTable);
        wochenplanErstellen("Samstag", "wochenplan", $sourceTable);
        wochenplanErstellen("Sonntag", "wochenplan", $sourceTable);
      }
  ?>

  </table>
  
  <?php if(!isset($_POST['wochenplan_save'])): ?>
  <div class="w3-row-padding w3-padding-16">
      <div class="w3-half">
        <button class="w3-button w3-indigo w3-text-white w3-block w3-padding-large" type="submit" name="load_from_wunsch">
            <i class="fa fa-refresh"></i> Aus Wunschplan laden<br>
            <span class="w3-small">(Überschreibt Anzeige, speichert noch nicht)</span>
        </button>
      </div>

      <div class="w3-half">
        <button class="w3-button w3-green w3-block w3-padding-large" type="submit" name="wochenplan_save">
            <i class="fa fa-check"></i> Bestätigen & Speichern<br>
            <span class="w3-small">(Schreibt in Datenbank)</span>
        </button>
      </div>
  </div>
  <?php endif; ?>

  </form>
  </div>
  </div>

  <!-- Wunschplan Section -->
  <div class="w3-padding-large" id="main2">
    <header class="w3-context w3-padding-32 w3-center w3-black" id="wunschplan">
      <h1 class="w3-jumbo">Wunschplan Verwaltung</h1>
    </header>

      <?php
      require 'conn.php';

      // --- 1. SPEICHER LOGIK WUNSCHPLAN ---
      if(isset($_POST['wp_action'])) {
          
          $datumObj = new DateTime($_POST['dateWunsch']);
          
          $conn->query("DELETE FROM wunschplan");

          $tage = ["Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag"];
          $i = 0;
          
          // CRITICAL FIX: Helper Funktion als Closure VOR der Schleife definiert
          $insertWunsch = function($conn, $tag, $art, $datum, $postName) {
              if(isset($_POST[$postName]) && $_POST[$postName] > 0) {
                  $stmt = $conn->prepare("INSERT INTO wunschplan (tag, speise_nr, datum) VALUES (?, ?, ?)");
                  $stmt->execute(array($tag, $_POST[$postName], $datum));
              }
          };

          foreach($tage as $tag) {
              $tagDatum = clone $datumObj;
              $tagDatum->modify("+$i days");
              $sqlDatum = $tagDatum->format('Y-m-d');
              $i++;

              $insertWunsch($conn, $tag, "Vollkost", $sqlDatum, "wunsch_".$tag."_Vollkost");
              $insertWunsch($conn, $tag, "Leichte Vollkost", $sqlDatum, "wunsch_".$tag."_Leichte_Vollkost");
              $insertWunsch($conn, $tag, "Vegetarisch", $sqlDatum, "wunsch_".$tag."_Vegetarisch");
          }

          if($_POST['wp_action'] == 'new') {
              $conn->query("DELETE FROM abstimmung");
              echo "<div class='w3-panel w3-red w3-display-container'><p>Wunschplan gespeichert & <b>Abstimmung zurückgesetzt!</b></p></div>";
          } else {
              echo "<div class='w3-panel w3-green w3-display-container'><p>Wunschplan erfolgreich geändert (Abstimmung behalten).</p></div>";
          }
      }

      // --- 2. DATUM LADEN ---
      $wpDateValue = "";
      $datumSql = "SELECT datum FROM wunschplan ORDER BY datum ASC LIMIT 1";
      foreach ($conn->query($datumSql) as $dRow) {
          $wpDateValue = (new DateTime($dRow['datum']))->format('Y-m-d');
      }

      // --- 3. ANZEIGE FUNKTION WUNSCHPLAN ---
      function wunschplanZeile($conn, $tag) {
          echo "<tr>";
          echo "<td><b>".$tag."</b></td>";

          $renderDropdown = function($art) use ($conn, $tag) {
              echo "<td>";
              echo "<select class='w3-input w3-padding-16' name='wunsch_".$tag."_".str_replace(' ', '_', $art)."'>"; 
              echo "<option value='0'></option>";
              
              $selectedID = 0;
              $checkSql = "SELECT speise_nr FROM wunschplan WHERE tag='$tag' AND speise_nr IN (SELECT speise_nr FROM speisen WHERE speise_art='$art')";
              foreach ($conn->query($checkSql) as $row) {
                  $selectedID = $row['speise_nr'];
              }

              $speisenSql = "SELECT * from speisen WHERE speise_art='$art' ORDER BY speise_name";
              foreach ($conn->query($speisenSql) as $speise) {
                  $isSel = ($speise['speise_nr'] == $selectedID) ? "selected" : "";
                  echo "<option value='".$speise['speise_nr']."' $isSel>".$speise['speise_name']."</option>";
              }
              echo "</select>";
              echo "</td>";
          };

          $renderDropdown('Vollkost');
          $renderDropdown('Leichte Vollkost');
          $renderDropdown('Vegetarisch');
          echo "</tr>";
      }
      ?>

    <!-- Formular Start VOR der Tabelle -->
    <form id='wunschForm' action='#wunschplan' method='post'>
      
      <center><div class='w3-padding'>
        <label for='dateWunsch'>Start-Datum der Woche: </label>
        <input type='date' id='dateWunsch' name='dateWunsch' value='<?php echo $wpDateValue; ?>' required>
      </div></center>

    <div class="w3-responsive">
      <table class="w3-table-all w3-large w3-text-black">
      <tr class="w3-blue w3-center">
        <th class="w3-center">Wochentag</th>
        <th class="w3-center">Vollkost</th>
        <th class="w3-center">Leichte Vollkost</th>
        <th class="w3-center">Vegetarisch</th>
      </tr>

      <?php
      wunschplanZeile($conn, "Montag");
      wunschplanZeile($conn, "Dienstag");
      wunschplanZeile($conn, "Mittwoch");
      wunschplanZeile($conn, "Donnerstag");
      wunschplanZeile($conn, "Freitag");
      wunschplanZeile($conn, "Samstag");
      wunschplanZeile($conn, "Sonntag");
      ?>
      </table>
    </div>

      <div class="w3-row-padding w3-padding-16">
        
        <div class="w3-third">
            <button class="w3-button w3-white w3-border w3-block w3-padding-large" type="button" onclick="clearForm()">
                <i class="fa fa-eraser"></i> Felder leeren<br>
                <span class="w3-small">(Für neue Planung)</span>
            </button>
        </div>

        <div class="w3-third">
            <button class="w3-button w3-orange w3-text-white w3-block w3-padding-large" type="submit" name="wp_action" value="update">
                <i class="fa fa-pencil"></i> Wunschplan ändern<br>
                <span class="w3-small">(Abstimmung behalten)</span>
            </button>
        </div>

        <div class="w3-third">
            <button class="w3-button w3-red w3-block w3-padding-large" type="submit" name="wp_action" value="new" onclick="return confirm('Sicher? Die aktuelle Abstimmung wird gelöscht!');">
                <i class="fa fa-trash"></i> Speichern & Reset<br>
                <span class="w3-small">(Abstimmung LÖSCHEN)</span>
            </button>
        </div>

      </div>

      <script>
      function clearForm() {
          var selects = document.querySelectorAll("#wunschForm select");
          for (var i = 0; i < selects.length; i++) {
              selects[i].value = "0"; 
              selects[i].selectedIndex = 0;
          }
          var d = document.getElementById("dateWunsch");
          if(d) d.value = "";
      }
      </script>

      </form>

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