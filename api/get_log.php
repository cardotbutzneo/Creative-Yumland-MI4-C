<?php
header('Content-Type: application/json');

require_once __DIR__."/../serveur.php";

$src = ROOT_PATH . "/securite.log";

if (!file_exists($src)) {
    echo json_encode(["success" => false, "message" => "Le fichier de log n'existe pas."]);
    exit;
}

// file() transforme chaque ligne du fichier en une case du tableau
$data_log = file($src, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

if (!is_array($data_log)) {
    echo json_encode(["success" => false, "message" => "Impossible de lire le fichier de log."]);
    exit;
}

// Optionnel : renverser le tableau pour voir les logs les plus récents en premier
$data_log = array_reverse($data_log);

echo json_encode(["success" => true, "data" => $data_log]);
exit;
?>