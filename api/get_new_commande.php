<?php 

require_once __DIR__."/../serveur.php";

$data = lire_data("../data/commandes.json");
echo json_encode(['nbCommande' => count($data)]);

?>