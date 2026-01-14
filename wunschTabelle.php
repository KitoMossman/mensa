
<?php
session_set_cookie_params(0);
session_start();

require 'conn.php';

if (isset($_POST['wunschauswahl'])){
    $anzahl = 0;
    $wunschSql = "SELECT * FROM wunschspeisen where wunschspeise_nr=".$_POST['wunschauswahl'];
    foreach ($conn->query($wunschSql) as $wunschRow) {
      $anzahl = $wunschRow['wunschspeise_anzahl'];
    }

    $statement = $conn->prepare("UPDATE wunschspeisen SET wunschspeise_anzahl = ? WHERE wunschspeise_nr = ?");
    $statement->execute(array($anzahl+1, $_POST['wunschauswahl']));
    $_SESSION['wunschauswahl'] = $_POST['wunschauswahl'];
}
elseif (isset($_POST['wunschauswahl2'])){
    $anzahl = 0;
    $wunschSql = "SELECT * FROM wunschspeisen where wunschspeise_nr=".$_POST['wunschauswahl2'];
    foreach ($conn->query($wunschSql) as $wunschRow) {
      $anzahl = $wunschRow['wunschspeise_anzahl'];
    }

    $statement = $conn->prepare("UPDATE wunschspeisen SET wunschspeise_anzahl = ? WHERE wunschspeise_nr = ?");
    $statement->execute(array($anzahl+1, $_POST['wunschauswahl2']));
    $_SESSION['wunschauswahl2'] = $_POST['wunschauswahl2'];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Wunschspeisen</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
* {
  box-sizing: border-box;
}
#myInput {
  background-position: 10px 10px;
  background-repeat: no-repeat;
  width: 100%;
  font-size: 16px;
  padding: 12px 20px 12px 40px;
  border: 1px solid #ddd;
  margin-bottom: 12px;
}

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
<!--  <a href="#" class="w3-bar-item w3-button w3-padding-large w3-black">
    <i class="fa fa-asterisk w3-xxlarge"></i>
    <p>Springe zu Eingabe</p>
  </a>
-->
  <a href="#vollkost" class="w3-bar-item w3-button w3-padding-large w3-black">
    <i class="fa fa-asterisk w3-xxlarge"></i>
    <p>Springe zu Vollkost</p>
  </a>
  <a href="#vegetarisch" class="w3-bar-item w3-button w3-padding-large w3-black">
    <i class="fa fa-asterisk w3-xxlarge"></i>
    <p>Springe zu Vegetarisch</p>
  </a>
  <?php
  if (!isset($_SESSION['wunschauswahl'])) {
    echo "<form action='./wunschTabelle.php#vollkost' method='post'>";
      echo "<input type='hidden' name='wunschauswahlbutton'>";
      echo "<button type='submit' form='my-form' class='w3-bar-item w3-button w3-padding-large w3-hover-black'>";
        echo "<i class='fa fa-check w3-xxlarge' style='color: green'></i>";
        echo "<p>Vollkost abstimmen</p>";
      echo "</button>";
    echo "</form>";
  }
  ?>
  <?php
  if (!isset($_SESSION['wunschauswahl2'])) {
    echo "<form action='./wunschTabelle.php#vegetarisch' method='post'>";
      echo "<input type='hidden' name='wunschauswahlbutton2'>";
      echo "<button type='submit' form='my-form2' class='w3-bar-item w3-button w3-padding-large w3-hover-black'>";
        echo "<i class='fa fa-check w3-xxlarge' style='color: blue'></i>";
        echo "<p>Vegetarisch abstimmen</p>";
      echo "</button>";
    echo "</form>";
  }
  ?>
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
    <a href="#" class="w3-bar-item w3-button" style="width:25% !important">Wunschspeisen</a>
  </div>
</div>

<!-- Page Content -->
<div class="w3-padding-large" id="main">
  <header class="w3-container w3-padding-32 w3-center w3-black" id="eingabe">
    <h1 class="w3-jumbo">Eingabe Wunschspeisen</h1>
  </header>

  <?php
  if (!isset($_POST['art'])) {
    echo "<form action='./wunschTabelle.php?' method='post' accept-charset='UTF-8'>";
      echo "<p><input class='w3-input w3-padding-16' type='text' placeholder='Wunschspeise' name='name'></p>";
      echo "<p>";
        echo "<select class='w3-input w3-padding-16' placeholder='Beilage' name='beilage' required>";
        echo "<option value='' disabled selected>Beilage auswählen</option>";
        require 'conn.php';
        $sql = "SELECT * FROM beilagen order by beilage_art";
        foreach ($conn->query($sql) as $row) {
  
 // änderung - ausgabe des freitextes im auswahlmenü mit umlauten nun korrekt ohne utf8_encode
   
        //  echo "<option value=".utf8_encode($row['beilage_name']).">".utf8_encode($row['beilage_art'])." - ".utf8_encode($row['beilage_name'])."</option>";
            echo "<option value=".           ($row['beilage_name']).">".           ($row['beilage_art'])." - ".           ($row['beilage_name'])."</option>";   


        




        }
        echo "</select>";
      echo "</p>";
      echo "<p>";
        echo "<select class='w3-input w3-padding-16' placeholder='Speiseart' name='art' required>";
        echo "<option value='' disabled selected>Speiseart auswählen</option>";
        echo "<option value='Vollkost'>Vollkost</option>";
        echo "<option value='Vegetarisch'>Vegetarisch</option>";
        echo "</select>";
      echo "</p>";
      echo "<p>";
        echo "<select class='w3-input w3-padding-16' placeholder='Kategorie' name='kategorie' required>";
        echo "<option value='' disabled selected>Kategorie auswählen</option>";
        echo "<option value='-'>-</option>";
        echo "<option value='Fleisch'>Fleisch</option>";
        echo "<option value='Fisch'>Fisch</option>";
        echo "<option value='Süßes'>Süßes</option>";
        echo "</select>";
      echo "</p>";
      echo "<p>";
        echo "<button class='w3-button w3-light-grey w3-padding-large' type='submit'>";
          echo "<i class='fa fa-paper-plane'></i> Abschicken";
        echo "</button>";
      echo "</p>";
    echo "</form>";
  } else {
    require 'conn.php';
    $conn->query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
    $statement = $conn->prepare("INSERT INTO wunschspeisen (wunschspeise_name, wunschspeise_art, wunschspeise_kategorie, wunschspeise_anzahl) VALUES (?, ?, ?, ?)");
    $statement->execute(array(htmlspecialchars($_POST['name'])." mit ".htmlspecialchars($_POST['beilage']), htmlspecialchars($_POST['art']), htmlspecialchars($_POST['kategorie']), 0));
    echo "<h1 class='w3-jumbo w3-center'>Vielen Dank für die Eingabe</h1>";
  }
  ?>

  <!-- Header/Home -->
  <div id="vollkost">
  <header class="w3-container w3-padding-32 w3-center w3-black" id="vollkost_header">
    <h1 class="w3-jumbo">Vollkost Wunschspeisen</h1>
  </header>

  <input type="text" id="myInput" onkeyup="myFunction('myInput', 'myTable')" placeholder="Suche nach Speise..." title="Type in a name">

<?php
  header('Content-Type: text/html; charset=UTF-8');
?>

<form id="my-form" action="./wunschTabelle.php#vollkost" method="post">
  <table class="w3-pale-green" id="myTable">
    <tr class="header w3-green">
      <th style="width:5%;">Platz</th>
      <th style="width:10%;">Auswahl</th>
      <th style="width:10%;">Anzahl</th>
      <th style="width:15%;">Speiseart</th>
      <th style="width:15%;">Kategorie</th>
      <th style="width:45%;">Speise</th>
    </tr>
    <?php

      require 'conn.php';

      $platz = 1;
      $wunschSql = "SELECT * FROM wunschspeisen where wunschspeise_art='Vollkost' order by wunschspeise_anzahl DESC, wunschspeise_kategorie";
      foreach ($conn->query($wunschSql) as $wunschRow) {
        echo "<tr>";
          echo "<td>".$platz++."</td>";
          echo "<td class='w3-center'><input type='radio' name='wunschauswahl' value=".$wunschRow['wunschspeise_nr']."></td>";

        // echo "<td>".utf8_encode($wunschRow['wunschspeise_anzahl'])."</td>";
        // echo "<td>".utf8_encode($wunschRow['wunschspeise_art'])."</td>";
        // echo "<td>".utf8_encode($wunschRow['wunschspeise_kategorie'])."</td>";
        // echo "<td>".utf8_encode($wunschRow['wunschspeise_name'])."</td>";
          
          
// änderung - ausgabe des freitextes in der tabelle mit umlauten nun korrekt ohne utf8_encode		
		          
          echo "<td>".($wunschRow['wunschspeise_anzahl'])."</td>";
          echo "<td>".($wunschRow['wunschspeise_art'])."</td>";
          echo "<td>".($wunschRow['wunschspeise_kategorie'])."</td>";
          echo "<td>".($wunschRow['wunschspeise_name'])."</td>";          

        echo "</tr>";
      }

    ?>

  </table>
  <br>
  <br>
  <?php
  if (!isset($_SESSION['wunschauswahl'])) {
    echo "<button class='w3-button w3-jumbo w3-green w3-padding-large' type='submit'>";
      echo "<i class='fa fa-paper-plane'></i> Abstimmen";
    echo "</button>";
  }
  ?>
 </form>
</div>
 <!---------Vegetarisch--------------->
<div id="vegetarisch">

 <header class="w3-container w3-padding-32 w3-center w3-black" id="vegetarisch_header">
   <h1 class="w3-jumbo">Vegetarische Wunschspeisen</h1>
 </header>

 <?php
   header('Content-Type: text/html; charset=UTF-8');
 ?>
 <input type="text" id="myInput2" onkeyup="myFunction('myInput2', 'myTable2')" placeholder="Suche nach Speise..." title="Type in a name2">

<form id="my-form2" action="./wunschTabelle.php#vegetarisch" method="post">
 <table class="w3-pale-blue" id="myTable2">
   <tr class="header w3-blue">
     <th style="width:5%;">Platz</th>
     <th style="width:10%;">Auswahl</th>
     <th style="width:10%;">Anzahl</th>
     <th style="width:15%;">Speiseart</th>
     <th style="width:15%;">Kategorie</th>
     <th style="width:45%;">Speise</th>
   </tr>
   <?php

     require 'conn.php';

     $platz = 1;
     $wunschSql = "SELECT * FROM wunschspeisen where wunschspeise_art='Vegetarisch' order by wunschspeise_anzahl DESC, wunschspeise_kategorie";
     foreach ($conn->query($wunschSql) as $wunschRow) {
       echo "<tr>";
         echo "<td>".$platz++."</td>";
         echo "<td class='w3-center'><input type='radio' name='wunschauswahl2' value=".$wunschRow['wunschspeise_nr']."></td>";


       //  echo "<td>".utf8_encode($wunschRow['wunschspeise_anzahl'])."</td>";
       //  echo "<td>".utf8_encode($wunschRow['wunschspeise_art'])."</td>";
       //  echo "<td>".utf8_encode($wunschRow['wunschspeise_kategorie'])."</td>";
       //  echo "<td>".utf8_encode($wunschRow['wunschspeise_name'])."</td>";

// änderung: entfernung des charset utf8_encode damit die umlaute korrekt dargestellt werden (23.05.2024 Christian Lehr)

         echo "<td>".($wunschRow['wunschspeise_anzahl'])."</td>";
         echo "<td>".($wunschRow['wunschspeise_art'])."</td>";
         echo "<td>".($wunschRow['wunschspeise_kategorie'])."</td>";
         echo "<td>".($wunschRow['wunschspeise_name'])."</td>";




       echo "</tr>";
     }

   ?>

 </table>
  <br>
  <br>
<?php
if (!isset($_SESSION['wunschauswahl2'])) {
 echo "<button class='w3-button w3-jumbo w3-blue w3-padding-large' type='submit'>";
   echo "<i class='fa fa-paper-plane'></i> Abstimmen";
 echo "</button>";
}
?>
</form>
</div>
</div>
<?php
require 'impressum.php';
 ?>

<!-- END PAGE CONTENT -->
</div>

<script>
function myFunction($input, $table) {
  var input, filter, table, tr, td, i, txtValue;
  input = document.getElementById($input);
  filter = input.value.toUpperCase();
  table = document.getElementById($table);
  tr = table.getElementsByTagName("tr");
  for (i = 0; i < tr.length; i++) {
    td = tr[i].getElementsByTagName("td")[3]
    if (td) {
      txtValue = td.textContent || td.innerText;
      if (txtValue.toUpperCase().indexOf(filter) > -1) {
        tr[i].style.display = "";
      } else {
        tr[i].style.display = "none";
      }
    }
  }
  for (i = 0; i < tr.length; i++) {
    td = tr[i].getElementsByTagName("td")[4]
    if (td) {
      txtValue = td.textContent || td.innerText;
      if (txtValue.toUpperCase().indexOf(filter) > -1) {
        tr[i].style.display = "";
      }
    }
  }
  for (i = 0; i < tr.length; i++) {
    td = tr[i].getElementsByTagName("td")[5]
    if (td) {
      txtValue = td.textContent || td.innerText;
      if (txtValue.toUpperCase().indexOf(filter) > -1) {
        tr[i].style.display = "";
      }
    }
  }
}
</script>

</body>
</html>
