<?php
// 1. On récupère l'URL demandée par le navigateur (ex: /html/index.php ou /html/style/main.css)
$url = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// 2. On définit la racine du projet (un niveau au-dessus du dossier /api)
$racine_projet = dirname(__DIR__); 

// 3. On construit le chemin absolu vers le fichier demandé sur le disque
$fichier = $racine_projet . urldecode($url);

// 4. Si l'utilisateur tape juste l'adresse de base (localhost:8000/)
if ($url === '/' || $url === '/html/') {
    header("Location: /html/index.php");
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