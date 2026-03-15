<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <title>L’oro di Cicerone</title>

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
            <li><?php if (isset($_SESSION) and $_SESSION["connecte"] == true) echo '<a href="connexion.php">Profil</a>'; else echo '<a href="connexion.php">se connecter</a>'?></li>
        </ul>
    </nav>
</header>
<section class="hero">
  <video autoplay muted loop playsinline>
    <source src="style/video/main.mp4" type="video/mp4">
  </video>

  <div class="hero-content">
    <h2>L’oro di Cicerone</h2>
    <p>Gastronomie italienne au sommet de Paris</p>
  </div>
</section>

<section class="section-light">
    <div class="experience">
        <img src="style/img/tour Eiffel.jpg" alt="Restaurant dans la Tour Eiffel">
        <div>
            <h3>L’Expérience</h3>
            <p>
                Suspendu au cœur de la Tour Eiffel, L’oro di Cicerone propose une expérience gastronomique unique,
                mêlant l’excellence de la cuisine italienne à l’élégance intemporelle de Paris.
            </p>
        </div>
    </div>
</section>

<section class="section-dark">
    <div class="chef">
        <div>
            <h3>Le Chef</h3>
            <p>
                Triplement étoilé au guide Michelin, notre chef puise son inspiration dans les traditions italiennes
                de son enfance, sublimées par un parcours au sein des plus grandes tables européennes.
            </p>
        </div>
        <img src="style/img/chef.png" alt="Chef étoilé">
    </div>
</section>

<section class="section-light cta">
    <p>Vivez un moment d’exception au sommet de la Ville Lumière</p>
    <a href="connexion.php">Réserver une table</a>
</section>

<footer>
    <p>© 2026 L’oro di Cicerone — Tous droits réservés</p>
    <a href="contact.php">Nous contacter </a><span>|</span>
    <a href="condition_generale.php">Confidentialité</a>
</footer>

</body>
</html>
