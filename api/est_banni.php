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
        echo json_encode([
        'banned' => $isBanned,
        'reason' => $reason
        ]);
        $bdd_actuelle = lire_data("../data/client.json");
        $email = $_SESSION["email"];

        if(isset($bdd_actuelle[$email])){
            $bdd_actuelle[$email]["securite"]["est_en_ligne"] = false;
            $bdd_actuelle[$email]["securite"]["remember_token"] = null;
            $bdd_actuelle[$email]["securite"]["remember_token_expiration"] = null;
            ecrire_data("../data/client.json", $bdd_actuelle);
        }

        session_unset();
        session_destroy();
    }
    else {
        echo json_encode(['banned' => false]);
    }
} else {
    echo json_encode(['banned' => false]);
}