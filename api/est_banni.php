<?php
session_start();
require_once __DIR__."/../serveur.php"; // Pour lire_data()

header('Content-Type: application/json');

// Si l'utilisateur n'est même pas connecté, on arrête
if (!isset($_SESSION["email"])) {
    echo json_encode(['banned' => false]);
    exit;
}

$data = lire_data(__DIR__ . "/../data/client.json");
$username = $_SESSION["email"];
if (isset($data[$username])) {
    $isBanned = $data[$username]['securite']['est_banni'];
    $reason = $data[$username]['securite']['raison_ban'] ?? "";

    if ($isBanned){
        $bdd_actuelle = lire_data("../data/client.json");
        $commandes_actuelles = lire_data("../data/commandes.json");
        $email = $_SESSION["email"];

        if (!empty($bdd_actuelle[$email]["dernieres_commandes"])) {
            
            $idCommande = $bdd_actuelle[$email]["dernieres_commandes"][0];

            if (isset($commandes_actuelles[$idCommande])) {
                unset($commandes_actuelles[$idCommande]);
            }

            array_shift($bdd_actuelle[$email]["dernieres_commandes"]);
        }

        ecrire_data("../data/client.json", $bdd_actuelle);
        ecrire_data("../data/commandes.json", $commandes_actuelles);

        session_unset();
        session_destroy();
        ecrire_log("L'utilisateur" . $_SESSION["prenom"] . $_SESSION["nom"] . "est banni", "info");

        echo json_encode([
        'banned' => $isBanned,
        'reason' => $reason
        ]);
    }
    else {
        echo json_encode(['banned' => false]);
    }
} else {
    echo json_encode(['banned' => false]);
}