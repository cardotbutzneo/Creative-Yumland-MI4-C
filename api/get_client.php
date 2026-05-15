<?php 

/**
 * Fichier de l'API pour récupérer les données des clients.
 * Revoie la liste de tous les clients sous format JSON.
 */

require_once __DIR__."/../serveur.php";

header('Content-Type: application/json');

$data = lire_data("../data/client.json");
echo json_encode($data);

?>