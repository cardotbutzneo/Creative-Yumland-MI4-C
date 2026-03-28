<?php
session_start();
require_once __DIR__."/../serveur.php";

if(!isset($_SESSION["connecte"])){
    header("Location: connexion.php");
    exit;
}

if(isset($_POST["deconnexion"])){
    $bdd_actuelle = lire_data("../data/client.json");
    $email = $_SESSION["email"];

    if(isset($bdd_actuelle[$email])){
        $bdd_actuelle[$email]["securite"]["est_en_ligne"] = false;
        $bdd_actuelle[$email]["securite"]["remember_token"] = null;
        $bdd_actuelle[$email]["securite"]["remember_token_expiration"] = null;
        ecrire_data("../data/client.json", $bdd_actuelle);
    }

    setcookie("remember_token", "", [
        "expires" => time() - 3600,
        "path" => "/",
        "httponly" => true,
        "samesite" => "Strict"
    ]);

    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}
elseif (isset($_POST["abandonner"])){
    header("Location: profil_client.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/authentification.css">
    <title>Déconnexion - L'oro di Cicerone</title>
</head>
<body>

<header>
    <a href="index.php"><h1>L'oro di Cicerone</h1></a>
    <nav>
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="restaurant.php">Le Restaurant</a></li>
            <li><a href="chef.php">Le Chef</a></li>
            <li><a href="presentation.php">Menu</a></li>
            <li><a href="connexion.php">Réserver</a></li>
        </ul>
    </nav>
</header>

<main class="conteneur-connexion">
    <section class="carte-connexion">
        <h2 class="titre-page">Déconnexion</h2>
        <p>Êtes-vous sûr de vouloir vous déconnecter ?</p>
        
        <form method="post" action="">
            <input type="submit" name="deconnexion" value="Se déconnecter" class="bouton-validation">
            <input type="submit" name="abandonner" value="Abandonner la deconnexion" class="bouton-validation">
        </form>
    </section>
</main>
</body>
</html>