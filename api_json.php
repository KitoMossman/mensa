<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");


require 'conn.php';


$statement = $conn->prepare("SELECT *
FROM wochenplan
INNER JOIN speisen ON wochenplan.speise_nr = speisen.speise_nr; ");
$statement->execute();
$data = $statement->fetchAll(PDO::FETCH_ASSOC);

// set response code - 200 OK
http_response_code(200);
echo json_encode($data);

?>
