<?php

/**
 * Fichier de l'API pour récupérer les données des commandes.
 * Revoie la liste de toutes les commandes sous format JSON.
 */

require_once __DIR__."/../serveur.php";
header('Content-Type: application/json');

$data = lire_data("../data/commandes.json");

echo json_encode($data);