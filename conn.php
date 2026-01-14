<?php

$server = "localhost";
$user = "root";
$passwd = "Education23.70";
try {
$conn = new PDO("mysql:host=$server;dbname=mensa", $user, $passwd);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 echo "";
} catch(PDOException $e) {
 echo "Connection failed: " . $e->getMessage();
}
?>
