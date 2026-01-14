<!-- Kontakt Section -->
<div class="w3-padding-64 w3-content w3-text-grey" id="kontakt">

  <h1 class="w3-jumbo w3-text-light-grey">Schreibt uns eine Nachricht</h1>
  <hr style="width:200px" class="w3-opacity">
  <h2 class="w3-xxlarge w3-text-light-grey">Teilnehmer mit Sonderkost können am Freitag um 10 bis 10:30 Uhr zur Sprechstunde zur Küche kommen</h2>
  <hr style="width:200px" class="w3-opacity">

  <?php
  if (!isset($_POST['thema'])) {
    echo "<form action='./index.php?#kontakt' method='post'>";
      echo "<p><input class='w3-input w3-padding-16' type='text' placeholder='Name (optional) und E-Mail falls Antwort erwünscht' name='name'></p>";
      echo "<p>";
        echo "<select class='w3-input w3-padding-16' placeholder='Thema' name='thema' required>";
        echo "<option value='' disabled selected>Thema auswählen</option>";
        echo "<option value='Essens_Vorschlag'>Essens-Vorschlag</option>";
        echo "<option value='Feedback'>Feedback</option>";
        echo "<option value='Sonstiges'>Sonstiges</option>";
        echo "</select>";
      echo "</p>";
      echo "<p><input class='w3-input w3-padding-16' type='text' placeholder='Nachricht' required name='nachricht'></p>";
      echo "<p>";
        echo "<button class='w3-button w3-light-grey w3-padding-large' type='submit'>";
          echo "<i class='fa fa-paper-plane'></i> Abschicken";
        echo "</button>";
      echo "</p>";
    echo "</form>";
  } else {
    require 'conn.php';
    $statement = $conn->prepare("INSERT INTO nachrichten (absender, thema, nachricht, datum) VALUES (?, ?, ?, ?)");
    $statement->execute(array($_POST['name'], $_POST['thema'], $_POST['nachricht'], date('Y-m-d')));
    echo "<h1 class='w3-jumbo w3-text-light-grey'>Vielen Dank für die Nachricht</h1>";
  }
  ?>

<!-- End Contact Section -->
</div>

<!-- Login Section -->
<div class="w3-padding-64 w3-content" id="login">

  <h1 class="w3-jumbo w3-center">Login Küche</h1>

  <?php

    require 'conn.php';

    $successfulLogin = false;

    if(!isset($_SESSION['admin']) && isset($_POST['name']) && isset($_POST['passwort'])) {
      $name = $_POST['name'];
      $passwort = $_POST['passwort'];


      $statement = $conn->prepare("SELECT * FROM admins WHERE name = :name");
      $result = $statement->execute(array('name' => $name));
      $user = $statement->fetch();

      //Überprüfung des Passworts
      if ($user !== false && $passwort == $user['passwort']) {
         echo "Erfolgreicher Login";
         $successfulLogin = true;
         $_SESSION['admin'] = $successfulLogin;
      } else {
         echo "Bitte Name und Passwort korrekt eingeben";
         $successfulLogin = false;
      }
    }

    if (!isset($_SESSION['admin'])) {
      echo "<form action='./index.php?#login' method='post'>";
      echo "<p><input class='w3-input w3-padding-16' type='text' name='name' placeholder='Name'></p>";
      echo "<p><input class='w3-input w3-padding-16' type='password' name='passwort' placeholder='Passwort'></p>";
      echo "<p>";
      echo "<button class='w3-button w3-light-grey w3-padding-large' type='submit'>";
      echo "<i class='fa fa-unlock'></i> Login";
      echo "</button>";
      echo "</p>";
      echo "</form>";
    } else {
      echo "<form action='./login.php'>";
      echo "<button class='w3-button w3-blue w3-padding-large' type='submit'>";
      echo "<i class='fa fa-unlock'></i> Weiter";
      echo "</button>";
      echo "</form>";
    }



   ?>

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
