<?php 

require_once __DIR__."/../serveur.php";

header('Content-Type: application/json');

$data = lire_data("../data/client.json");
echo json_encode($data);

?>