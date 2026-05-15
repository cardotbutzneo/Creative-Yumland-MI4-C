<?php 

require_once __DIR__."/../serveur.php";

$chemin_valide = ["index.php", "restaurant.php", "chef.php", "presentation.php", "connexion.php"];

// Vérification de la session et du rôle de l'utilisateur
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Si la session n'existe pas ou si le rôle n'est pas défini, rediriger vers la page de connexion
if (empty($_SESSION) || !isset($_SESSION["role"]) and !in_array(basename($_SERVER["PHP_SELF"]), $chemin_valide)) {
    ecrire_log("Accès non autorisé à " . basename($_SERVER["PHP_SELF"]));
    header("Location: connexion.php?error=unauthorized");
    exit;
}

// Récupération du rôle de l'utilisateur
$role = $_SESSION["role"];
$_SESSION["derniere-connexion"] = time();

// lecture du fichier de langue
$txt = lire_data("../data/langue.json");
$text = $txt[($_COOKIE["langue"]) ?? "fr"];

$isFrench = false;
if (isset($_COOKIE["langue"])){
    $isFrench = ($_COOKIE["langue"] == "fr");
}

// Définition des constantes de configuration liée aux dates
date_default_timezone_set("Europe/Paris");
$aujourdhui = date("Y-m-d");
$demain = date("Y-m-d",strtotime("+1 day"));

?>