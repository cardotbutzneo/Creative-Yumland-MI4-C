<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Chef - L’oro di Cicerone</title>
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/chef.css">
</head>
<body>
    <header>
    <a href="index.php"><h1>L’oro di Cicerone</h1></a>
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
    <main>
    <section class="chef-hero">
    <video autoplay muted loop playsinline>
    <source src="style/video/chef.mp4" type="video/mp4">
    </video>
    <h2>Le Chef<br>
        <span class="sous-titre">Maestro de la gastronomie italienne</span>
    </h2>
    </section>
        <section class="section-light">
            <div class="experience">
                <img src="style/img/chef.png" alt="Chef étoilé">
                    <div>
                        <h3>Un parcours d’exception</h3>
                        <p>
                        Originaire d’Italie, notre chef découvre très tôt la richesse de la cuisine méditerranéenne.
                        Formé au sein des plus grandes maisons européennes, il développe une vision culinaire où
                        la tradition italienne se mêle à une recherche constante de perfection.
                        </p>
                        <p>
                        Fasciné par Paris et son rayonnement culturel, il choisit la Tour Eiffel comme écrin pour
                        exprimer pleinement son art et fonder L’oro di Cicerone, symbole d’excellence et de transmission.
                        </p>
                    </div>
            </div>
        </section>
        <section>
            <div class="philosophy">
                <h3>Sa philosophie</h3>
                <p>
                Pour le chef, la cuisine est un langage universel. Chaque plat doit raconter une histoire,
                évoquer une émotion et célébrer l’authenticité des produits.
                </p>
                <p class="quote">« La simplicité est la plus grande des élégances. »</p>
            </div>
        </section>
        <section class="section-light">
            <h3 style="text-align:center;">Distinctions</h3>
            <div class="awards">
                <div class="award">
                    <h4>★★★ Michelin</h4>
                    <p>Excellence gastronomique</p>
                </div>
                <div class="award">
                    <h4>Chef de l’année</h4>
                    <p>Guide européen</p>
                </div>
                <div class="award">
                    <h4>Prix de l’innovation</h4>
                    <p>Cuisine contemporaine</p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
