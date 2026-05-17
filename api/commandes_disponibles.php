<?php
require_once __DIR__."/../api/config.php";
verifier_connexion($role, "livreur");

$bdd_cmd = lire_data("../data/commandes.json");

$disponible = false;
foreach ($bdd_cmd as $cmd) {
    if (($cmd["livraison"] ?? false) === true && ($cmd["etat"] ?? "") === "preparee") {
        $disponible = true;
        break;
    }
}

header("Content-Type: application/json");
echo json_encode(["disponible" => $disponible]);
?>