
<?php
session_start();
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
    <i class="fa fa-asterisk w3-xxlarge"></i>
    <p>WUNSCH</p>
  </a>
</nav>

<!-- Navbar on small screens (Hidden on medium and large screens) -->
<div class="w3-top w3-hide-large w3-hide-medium" id="myNavbar">
  <div class="w3-bar w3-black w3-opacity w3-hover-opacity-off w3-center w3-small">
    <a href="#" class="w3-bar-item w3-button" style="width:25% !important">Wunschspeisen</a>
  </div>
</div>

<!-- Page Content -->
<div class="w3-padding-large" id="main">
  <!-- Header/Home -->
  <header class="w3-container w3-padding-32 w3-center w3-black" id="home">
    <h1 class="w3-jumbo">Wunschspeisen</h1>
  </header>


  <input type="text" id="myInput" onkeyup="myFunction()" placeholder="Suche nach Speise..." title="Type in a name">

  <table class="w3-pale-green" id="myTable">
    <tr class="header w3-green">
      <th style="width:10%;">Auswahl</th>
      <th style="width:20%;">Speiseart</th>
      <th style="width:70%;">Speise</th>
    </tr>
    <?php

      require 'conn.php';

      $zusatzSql = "SELECT * FROM speisen order by speise_art";
      foreach ($conn->query($zusatzSql) as $zusatzRow) {
        echo "<tr>";
          echo "<td class='w3-center'><input type='radio' name='wunschauswahl' value=".$zusatzRow['speise_nr']."></td>";
          echo "<td>".$zusatzRow['speise_art']."</td>";
          echo "<td>".$zusatzRow['speise_name']."</td>";
        echo "</tr>";
      }

    ?>

  </table>
</div>

<?php
require 'impressum.php';
 ?>

<!-- END PAGE CONTENT -->
</div>

<script>
function myFunction() {
  var input, filter, table, tr, td, i, txtValue;
  input = document.getElementById("myInput");
  filter = input.value.toUpperCase();
  table = document.getElementById("myTable");
  tr = table.getElementsByTagName("tr");
  for (i = 0; i < tr.length; i++) {
    td = tr[i].getElementsByTagName("td")[1]
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
    td = tr[i].getElementsByTagName("td")[2]
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
