<?php

/**
 * Fichier de l'API pour récupérer les données des commandes.
 * Revoie la liste de toutes les commandes sous format JSON.
 */

require_once __DIR__."/../serveur.php";
header('Content-Type: application/json');

$commandes = lire_data("../data/commandes.json");
$data_langue = lire_data("../data/langue.json");
$langue = [];
$langue["en"] = $data_langue["en"]["commandes"];
$langue["fr"] = $data_langue['fr']["commandes"];

echo json_encode(["commandes" => $commandes, "langue" => $langue]);