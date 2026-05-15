<?php 

/**
 * Fichier de l'API pour mettre à jour le statut d'une commande.
 * - Reçoit une requête POST avec les paramètres "hash" (identifiant de la commande) et "nouvelEtat" (nouveau statut de la commande).
 * - Met à jour le statut de la commande correspondante dans le fichier JSON des commandes.
 * - Retourne une réponse JSON indiquant si la mise à jour a été effectuée avec succès ou non.
 * 
 * @require serveur.php pour les fonctions de lecture et d'écriture des données JSON.
 */

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