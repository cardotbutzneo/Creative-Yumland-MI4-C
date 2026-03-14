<?php
session_start();

if(!isset($_SESSION["connecte"])){
    header("Location: connexion.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/profil_client.css"> 
    <title>Profil Client - L’oro di Cicerone</title>
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
            <li><a href="#">Modifier le profil</a></li>
        </ul>
    </nav>
</header>
    <section>
        <hr>
        <div class="contenent">
            <h2>Favori</h2>
        </div>
        <hr>
        <div class="contenent">
            <h2>Nos sugestions</h2>
                <nav>
                    <ul class="sugestions">
                    <li>
                        <span>Pizza à la truffe</span> <span>35€</span>
                    </li>
                    <li>
                        <span>Pizza au homard</span> <span>50€</span>
                    </li>
                    <li>
                        Pizza aux St Jacques <span class="plats">65€</span>
                    </li>
                </ul>
            </nav>
        </div>
        <hr>
        <div class="contenent">
            <h2>Vos points fidélités</h2>
            <div class="block">
                <p>0 point.s</p>
            </div>
        </div>
    </section>
<footer>
    <p>© 2026 L’oro di Cicerone — Tous droits réservés</p>
    <a href="contact.php">Nous contacter</a>
</footer>
</body>
</html>