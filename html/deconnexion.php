<?php

require_once __DIR__."/../api/config.php";

if(!isset($_SESSION["connecte"])){
    header("Location: connexion.php");
    exit;
}

if(isset($_POST["deconnexion"])){
    $bdd_actuelle = $data_client;
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
<html lang="<?php if ($isFrench) echo "fr"; else echo "en"; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/authentification.css">
    <title><?= $text["deconnexion"]["title"] ?></title>
</head>
<body>

<header>
    <a href="index.php"><h1>L'oro di Cicerone</h1></a>
    <nav>
        <ul>
            <li><a href="index.php"><?php if ($isFrench) echo "Accueil"; else echo "Home page"; ?></a></li>
            <li><a href="restaurant.php"><?php if ($isFrench) echo "Le Restaurant"; else echo "The Restaurant"; ?></a></li>
            <li><a href="chef.php"><?php if ($isFrench) echo "Le Chef"; else echo "The Chef"; ?></a></li>
            <li><a href="presentation.php">Menu</a></li>
            <li><a href="connexion.php"><?= $text["deconnexion"]["nav_booking"] ?></a></li>
        </ul>
    </nav>
</header>

<main class="conteneur-connexion">
    <section class="carte-connexion">
        <h2 class="titre-page"><?= $text["deconnexion"]["page_title"] ?></h2>
        <p><?= $text["deconnexion"]["confirmation"] ?></p>
        
        <form method="post" action="">
            <input type="submit" name="deconnexion" value="<?= $text["deconnexion"]["logout_button"] ?>" class="bouton-validation">
            <input type="submit" name="abandonner" value="<?= $text["deconnexion"]["cancel_button"] ?>" class="bouton-validation">
        </form>
    </section>
</main>
</body>
</html>