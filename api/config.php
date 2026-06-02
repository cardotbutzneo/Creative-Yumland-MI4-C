<?php 

/**
 * Fichier exécuter au lancement du serveur.
 * Vérifie la session de l'utilisateur et redirige vers la page de connexion si nécessaire.
 * Définit les constantes de configuration des variables d'environnement et de la langue.
 * 
 * @see data/client.json, data/commandes.json, data/panier.json, data/plats.json, data/langue.json
 * @see serveur.php
 * 
 * @summary Configuration et gestion de la session utilisateur et données communes.
 */

require_once __DIR__."/../serveur.php";

$chemin_hors_session = ["index.php", "restaurant.php", "chef.php", "presentation.php", "connexion.php", "inscription.php","reservation.php"];

// Vérification de la session et du rôle de l'utilisateur
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Si la session n'existe pas ou si le rôle n'est pas défini, rediriger vers la page de connexion
if (empty($_SESSION) || !isset($_SESSION["role"]) and !in_array(basename($_SERVER["PHP_SELF"]), $chemin_hors_session)) {
    ecrire_log("Accès non autorisé à " . basename($_SERVER["PHP_SELF"]));
    header("Location: connexion.php?error=unauthorized");
    exit;
}

// Définition des constantes de configuration des variables
$data_client = lire_data("../data/client.json");
$data_commandes = lire_data("../data/commandes.json");
$data_panier = lire_data("../data/paniers.json");
$data_plats = lire_data("../data/plats.json");
$data_langue = lire_data("../data/langue.json");    

// Récupération du rôle de l'utilisateur
$role = $_SESSION["role"];
$_SESSION["derniere-connexion"] = time();

// Récupération de la langue (fr par défaut)
$isFrench = true;
if (isset($_COOKIE["langue"])){
    $isFrench = ($_COOKIE["langue"] == "fr");
}

// Définition des constantes de configuration liée aux dates
date_default_timezone_set("Europe/Paris");
$aujourdhui = date("Y-m-d");
$demain = date("Y-m-d",strtotime("+1 day"));

// lecture du fichier de langue
$langue = $_COOKIE["langue"];
$text = $data_langue[($langue) ?? "fr"];
?>