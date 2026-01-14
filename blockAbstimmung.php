<!-- Abstimmung Section -->
<div class="w3-container w3-padding-64 w3-center" id="abstimmung">

  <?php

  require 'conn.php';

  if(!isset($_SESSION['abgestimmt'])) {
    if (isset($_POST['Montag'])){
      $statement = $conn->prepare("INSERT INTO abstimmung (speise_nr) VALUES (?)");
      $statement->execute(array($_POST['Montag']));
    }
    if (isset($_POST['Dienstag'])){
      $statement = $conn->prepare("INSERT INTO abstimmung (speise_nr) VALUES (?)");
      $statement->execute(array($_POST['Dienstag']));
    }
    if (isset($_POST['Mittwoch'])){
      $statement = $conn->prepare("INSERT INTO abstimmung (speise_nr) VALUES (?)");
      $statement->execute(array($_POST['Mittwoch']));
    }
    if (isset($_POST['Donnerstag'])){
      $statement = $conn->prepare("INSERT INTO abstimmung (speise_nr) VALUES (?)");
      $statement->execute(array($_POST['Donnerstag']));
    }
    if (isset($_POST['Freitag'])){
      $statement = $conn->prepare("INSERT INTO abstimmung (speise_nr) VALUES (?)");
      $statement->execute(array($_POST['Freitag']));
    }
    if (isset($_POST['Samstag'])){
      $statement = $conn->prepare("INSERT INTO abstimmung (speise_nr) VALUES (?)");
      $statement->execute(array($_POST['Samstag']));
    }
    if (isset($_POST['Sonntag'])){
      $statement = $conn->prepare("INSERT INTO abstimmung (speise_nr) VALUES (?)");
      $statement->execute(array($_POST['Sonntag']));
    }
  }
  $_SESSION['abgestimmt'] = true;
  ?>

  <h1 class="w3-text-blue" style="font-size:100px;">Vielen Dank für die Abstimmung</h1>


</div>
