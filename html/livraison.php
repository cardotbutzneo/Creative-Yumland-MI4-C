<?php
session_start();

if(!isset($_SESSION["connecte"])){
    header("Location: connexion.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/livraison.css">
    <title>Livraison - L’oro di Cicerone</title>
</head>
<body>

<header>
    <a href="index.php"><h1>L’oro di Cicerone</h1></a>
    <nav>
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="presentation.php">Menu</a></li>
        </ul>
    </nav>
</header>

<main class="conteneur-livraison">

    <h2 class="titre-page">Informations de livraison</h2>

    <section class="carte-livraison">

        <div class="ligne-information">
            <span class="intitule">Numéro de commande</span>
            <span class="valeur">02740237</span>
        </div>
        <div class="ligne-information">
            <span class="intitule">Nom</span>
            <span class="valeur">SATHTHIRIYAN</span>
        </div>
        <div class="ligne-information">
            <span class="intitule">Prénom</span>
            <span class="valeur">Rémi</span>
        </div>
        <div class="ligne-information">
            <span class="intitule">Adresse e-mail</span>
            <span class="valeur">
                <a href="mailto:remi.saththiriyan@etu.cyu.fr">
                    remi.saththiriyan@etu.cyu.fr
                </a>
            </span>
        </div>
        <div class="ligne-information">
            <span class="intitule">Téléphone</span>
            <span class="valeur">
                <a href="tel:+33761414423">07 61 41 44 23</a>
            </span>
        </div>
        <div class="bloc-adresse">
            <div class="intitule">Adresse de livraison</div>
            <div class="valeur">
                24 Rue de Malcouture<br>
                95100 Argenteuil<br>
                Appartement 1
            </div>
            <a class="lien-maps" target="_blank"
               href="https://www.google.com/maps/search/?api=1&query=24+Rue+de+Malcouture+95100+Argenteuil">
                Ouvrir dans Google Maps
            </a>
        </div>

        <input type="submit" value="Terminer livraison" class="bouton-validation">

    </section>

</main>

</body>
</html>