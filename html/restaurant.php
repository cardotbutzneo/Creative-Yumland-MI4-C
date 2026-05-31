<?php
require_once __DIR__."/../api/config.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Restaurant - L'oro di Cicerone</title>
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/restaurant.css">
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
    <section class="section-light">
        <div class="experience">
            <p>Niché au cœur de Paris, à l'intérieur même de la Dame de Fer, L'oro di Cicerone vous invite à vivre une expérience hors du temps. Suspendu à 324 mètres de hauteur, notre restaurant offre un cadre d’exception où la gastronomie italienne s’élève au rang d’art, sublimée par une vue panoramique à couper le souffle sur la capitale.</p>
            <img src="style/img/vue ensemble resto.jpg" alt="Une vue d'ensemble du restaurant">
        </div>
    </section>
    <section class="section-dark">
        <div class="experience">
            <img src="style/img/chef.png" alt="Chef étoilé">
            <p>
                Sous la direction de notre chef, triplement étoilé au guide Michelin, chaque plat devient une ode au raffinement. Inspiré par son enfance en Italie, il revisite avec élégance les grandes recettes traditionnelles, en mariant authenticité, créativité et produits d'exception. Son parcours, forgé au sein des plus grandes tables européennes, l'a naturellement conduit à Paris, ville lumière, où il choisit d'installer L'oro di Cicerone comme l'aboutissement de sa vision gastronomique.
            </p>
        </div>
    </section>
    <section class="section-light">
        <div class="experience">
            <p>
                Dans une atmosphère intime et sophistiquée, entre matériaux nobles et lumières délicatement tamisées, chaque détail a été pensé pour offrir à nos convives un moment privilégié. Ici, le temps semble suspendu, laissant place à une expérience sensorielle unique, où le goût, la vue et l'émotion ne font plus qu'un.
            </p>
            <img src="style/img/raviolis.png" alt="Une image de pâtes italienne">
        </div>
    </section>
    <section class="section-dark">
        <p>
            L'oro di Cicerone n'est pas seulement un restaurant : c'est une invitation au voyage, une célébration de l'excellence italienne au sommet de Paris.
        </p>
    </section>
    <footer>
        <p><?= $text["index"]["footer_rights"] ?></p>
        <a href="contact.php"><?= $text["index"]["footer_contact"] ?></a><span> |</span>
        <a href="condition_generale.php"><?= $text["index"]["footer_privacy"] ?></a>
    </footer>
</body>
</html>