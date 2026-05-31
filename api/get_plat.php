<?php  

header("Content-Type: application/json");

require_once __DIR__ . "/../serveur.php";

$data = lire_data("../data/plats.json");

if (!is_array($data) || empty($data))  {
    echo json_encode(["success" => false, "data" => [], "erreur" => "fichier vide ou inaccessible"]); // si le fichier n'existe pas ou est vide
    exit;
}

echo json_encode(["success" => true, "data" => $data, "erreur" => ""]);

?>