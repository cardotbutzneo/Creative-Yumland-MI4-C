<?php
/**
 * Fichier de l'API pour récupérer les logs de sécurité.
 * Revoie la liste de tous les logs sous format JSON.
 */

header('Content-Type: application/json');

require_once __DIR__."/../serveur.php";

$src = ROOT_PATH . "/securite.log";

if (!file_exists($src)) {
    echo json_encode(["success" => false, "message" => "Le fichier de log n'existe pas."]);
    exit;
}

// Rappel : file() transforme chaque ligne du fichier en une case du tableau
$src = ROOT_PATH . "/securite.log";
$data_log = file($src, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$limite = 20;
if (count($data_log) > $limite){ // archiver les logs si plus de 20 lignes pour la visibilité
    $index = 1;
    while (file_exists(ROOT_PATH . "archive_log/log_" . $index . ".log")){
        $index++;
    }
    $nom_archive = ROOT_PATH . "archive_log/log_" . $index . ".log";

    if (rename($src,$nom_archive)){
        touch($src);
        $data_log = [];
    }
}

if (!is_array($data_log)) {
    echo json_encode(["success" => false, "message" => "Impossible de lire le fichier de log."]);
    exit;
}

// on reverse le tableau pour afficher les logs les plus récents en premier
$data_log = array_reverse($data_log);

echo json_encode(["success" => true, "data" => $data_log]);
exit;
?>