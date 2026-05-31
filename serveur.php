<?php

/**
 * Fichier contenant les fonctions utilitaires communes à tous les fichiers php
 * - lire_data : lit les données à partir d'un fichier JSON
 * - ecrire_data : écrit les données dans un fichier JSON
 * - récupérer_commande : récupère une commande spécifique à partir de son numéro
 * - calculer_points : calcule le nombre de points gagnés en fonction du montant total et du rang du client
 * - ecrire_log : écrit un message dans le fichier de log avec un type (info, warning, critical)
 * - verifier_connexion : vérifie que l'utilisateur est connecté avec le bon rôle et redirige vers la page de connexion si nécessaire
 */

define('ROOT_PATH', __DIR__ . '/'); // on définie la racine du projet
date_default_timezone_set('Europe/Paris');

/** Lit les données à partir d'un fichier JSON. 
 * @param string $chemin - Le chemin du fichier JSON à lire.
 * @param string $nom_utilisateur - (optionnel) Le nom d'utilisateur pour filtrer les données. Si fourni, seules les données associées à ce nom d'utilisateur seront retournées.
 * @return array - Un tableau associatif contenant les données lues du fichier JSON. Si le fichier n'existe pas ou est vide, un tableau vide sera retourné. Si $nom_utilisateur est fourni mais n'existe pas dans les données, un tableau vide sera également retourné.
*/
function lire_data(string $chemin, string $nom_utilisateur = "") : array{
    if (!file_exists($chemin)) return [];
    $data = json_decode(file_get_contents($chemin),true);
    if ($data == null) return [];
    if ($nom_utilisateur != ""){
        if (isset($data[$nom_utilisateur])) return $data[$nom_utilisateur];
    }
    return $data;
}

/** Écrit les données dans un fichier JSON. 
 * @param string $chemin - Le chemin du fichier JSON dans lequel écrire les données.
 * @param array $data - Un tableau associatif contenant les données à écrire dans le fichier JSON.
 * @return bool - Retourne true si les données ont été écrites avec succès, sinon false.
*/
function ecrire_data(string $chemin, array $data) : bool {
    $json_contenu = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if (file_put_contents($chemin, $json_contenu) !== false) {
        return true;
    }
    
    return false;
}

/** Récupère une commande spécifique à partir de son numéro.
 * @param string $numéro - Le numéro de la commande à récupérer.
 * @return array|null - Un tableau associatif contenant les détails de la commande si elle existe
 */
function récupérer_commande(string $numéro) : ?array{
    if (!isset($numéro)) return null;
    $data = lire_data("../data/commandes.json");
    if (!isset($data)) return null;
    if (!isset($data[$numéro]["plats"])) return null;
    return $data[$numéro];
}

/**Renvoie le nombre de point du client apres achat
*Exemple : si on dépense 200€ en étant membre or on gagne : (200*200*1.5)/1000 = 60pts 
*/
function calculer_points(int $montant_total, string $pts) : int{
    if (!isset($montant_total) or !isset($pts)) return -1;
    if ($montant_total <= 0) return -1;
    if ($pts < 500) {$rang = "Amethyste"; $coeff = 1;}
    else if ($pts >= 500 and $pts < 1200) {$rang = "Rubi"; $coeff = 1.2;}
    else {$rang = "Buisson-or"; $coeff = 1.5;}
    $K = 1000; // constante arbitraire
    $pts = (($montant_total**2)*$coeff) / $K;
    return $pts;
}

/** Écrit un message dans le fichier de log. 
 * Types de messages : "info", "warning", "critical". Par défaut, le type est "warning".
 * @param string $msg - Le message à écrire dans le log.
 * @param string $type - Le type de message (info, warning, critical).
 * 
*/
function ecrire_log(string $msg, string $type = "warning") : void {
    if (empty($msg)) return;

    $date = date("Y-m-d H:i:s");
    $colors = [
        "info"     => "\033[33m",
        "warning"  => "\033[38;5;208m",
        "critical" => "\033[31m",
        "reset"    => "\033[0m"
    ];
    if (!in_array($type, array_keys($colors))) $type = "warning"; // si type pas reconnu on le met à la valeur par défaut

    $max_len = 0;
    foreach (array_keys($colors) as $color){
        $max_len = max($max_len,strlen($color));
    }
    $type = strtolower($type);
    $color = $colors[$type] ?? $colors['reset'];
    
    $date = date("Y-m-d H:i:s");
    $format = $color . str_pad(strtoupper($type), 8," ",STR_PAD_RIGHT) . $colors['reset'] . " [" . $date . "] -- " . $msg . PHP_EOL;

    error_log($format, 3 , ROOT_PATH ."securite.log");
}


/**vérifie que l'utilisateur est connecté avec le bon rôle
 * @param string $role - Le rôle actuel de l'utilisateur (ex: "Client", "admin", etc.)
 * @param array | string $roles_autorisés - Les rôles autorisés à accéder à la page (par défaut: ["Client"]). Si un seul rôle est passé, un string est accepté
 * @param bool $inclure_admin - Indique si les utilisateurs avec le rôle "admin" sont également autorisés à accéder à la page (par défaut: true)
 * @return void - Si erreur, redirige vers la page de connexion avec un message d'erreur
 */
function verifier_connexion(string $role, array | string $roles_autorisés = ["Client"], bool $inclure_admin = true) : void {
    if (empty($roles_autorisés) || $roles_autorisés == "") return; // Si aucun rôle n'est spécifié, on considère que tous les rôles sont autorisés
    if (is_string($roles_autorisés)) $roles_autorisés = [$roles_autorisés]; // Si un seul rôle est fourni sous forme de chaîne, on le convertit en tableau
    if ($role == "admin" && $inclure_admin) return; // Si l'utilisateur est un admin et que les admins sont inclus, on autorise l'accès
    if (!in_array($role, $roles_autorisés)){
        $type_log = "";
        if (in_array("admin",$roles_autorisés)) $type_log = "critical";
        ecrire_log("Accès non autorisé à " . basename($_SERVER["PHP_SELF"]) . " par " . $_SESSION["prenom"] . " " . $_SESSION["nom"] . " avec le rôle " . $role, $type_log);
        header("Location: connexion.php?error=unauthorized");
        exit;
    }
}

/**
 * Retourne un booléen en fonction du role de l'utilisateur (Attention ne fonctionne que si l'utilisateur est déjà connecté à une session)
 * @return bool - True si l'utilisateur est un admin, False sinon
 */
function is_admin() : bool {
    if (isset($_SESSION["role"])){
        if ($_SESSION["role"] === "admin") return true;
    }
    return false;
}

?>