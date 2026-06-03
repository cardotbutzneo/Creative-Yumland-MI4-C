<?php

require_once __DIR__."/../serveur.php";
session_start();

$maintenance = false;
    
if ($maintenance) { // on redirige vers la page de maintenanc à chaque requêtte
    include "api/maintenance.php";
    exit;
}

// 1. On récupère l'URL demandée par le navigateur (ex: /html/index.php ou /html/style/main.css)
$url = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// 2. On définit la racine du projet (un niveau au-dessus du dossier /api)
$racine_projet = dirname(__DIR__); 

// 3. On construit le chemin absolu vers le fichier demandé sur le disque
$fichier = $racine_projet . urldecode($url);

$profil = "";
if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION["role"]) && isset($_SESSION["nom"])){
    $profil = " par " . $_SESSION["nom"] . " " . $_SESSION["prenom"] . " avec le rôle " . $_SESSION["role"];
}

// 4. Si l'utilisateur tape juste l'adresse de base (localhost:8000/)
if ($url === '/' || $url === '/html/') {
    header("Location: /html/index.php");
    exit;
}

if (preg_match('/data\//', $url)){
    ecrire_log("Securite : Tentative d'accès aux base de données" . $profil);
    include "api/404_error.php";
    exit;
}

if (preg_match('/securite.log/',$url) || preg_match_all('/archive_log\//',$url)){
    ecrire_log("Securite : Tentative d'accès aux fichiers de log" . $profil, "critical");
    include "api/404_error.php";
    exit;
}

// 5. Si le fichier demandé existe réellement sur le disque (ton CSS, tes images, etc.)
if (file_exists($fichier) && !is_dir($fichier)) {
    // Le serveur de PHP reprend la main et sert le CSS avec le bon Content-Type
    return false; 
}

// 6. Si rien ne correspond : page 404
http_response_code(404);
if (file_exists($racine_projet . '/api/404_error.php')) {
    include "api/404_error.php";
} else {
    echo "Erreur 404 : Page introuvable.";
}
exit;