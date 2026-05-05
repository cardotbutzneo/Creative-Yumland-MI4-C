<?php 

require_once __DIR__."/../serveur.php";
header('Content-Type: application/json');

$data = lire_data("../data/commandes.json");

if (isset($_POST["hash"])){
    if (isset($data[$_POST["hash"]])){
        $data[$_POST["hash"]]["etat"] = $_POST["nouvelEtat"];
        ecrire_data("../data/commandes.json",$data);
        echo json_encode(['ok' => true]);
    }
    else echo json_encode(['ok' => false]);
}
else echo json_encode(['ok' => false]);

?>