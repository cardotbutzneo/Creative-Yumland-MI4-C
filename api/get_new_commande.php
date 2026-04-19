<?php

require_once __DIR__."/../serveur.php";
header('Content-Type: application/json');

$data = lire_data("../data/commandes.json");

echo json_encode($data);