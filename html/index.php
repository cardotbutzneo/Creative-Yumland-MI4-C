<?php
session_start();

require_once __DIR__."/../serveur.php";

$txt = lire_data("../data/langue.json");

$text = $txt[($_COOKIE["langue"]) ?? "fr"];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/notification.css">
    <link rel="stylesheet" href="style/chatbot.css">
    <title>L'oro di Cicerone</title>
    
</head>

<header>
    <a href="index.php"><h1>L'oro di Cicerone</h1></a>
    <nav>
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="restaurant.php">Le Restaurant</a></li>
            <li><a href="chef.php">Le Chef</a></li>
            <li><a href="presentation.php">Menu</a></li>
            <li><?php if (isset($_SESSION["connecte"]) and $_SESSION["connecte"] == true) echo '<a href="connexion.php">Profil</a>'; else echo '<a href="connexion.php">se connecter</a>'?></li>
            <?php require_once "../api/get_accessibilite.php" ?>
        </ul>
    </nav>
</header>

<div class="notification" id="notification" style="display : none">
        <div class="notification-header">
            <span class="notification-titre">Notification</span>
            <button class="notification-close" onclick="this.closest('.notification').style.display='none'">✕</button>
        </div>
        <p class="notification-body">
            Votre inscription a bien été réalisée.
        </p>
        <div class="notification-barre">
            <div class="notification-barre-fill"></div>
        </div>
</div>

<?php if (isset($_GET["flag"]) && $_GET["flag"] === "success"){?>
    <script>
        document.getElementById('notification').style.display = "block";
    </script>
<?php } ?>


<section class="hero">
  <video autoplay muted loop playsinline>
    <source src="style/video/main.mp4" type="video/mp4">
  </video>

  <div class="hero-content">
    <h2>L'oro di Cicerone</h2>
    <p><?= $text["index"]["hero_subtitle"] ?></p>
  </div>
</section>

<section class="section-light">
    <div class="experience">
        <img src="style/img/tour Eiffel.jpg" alt="Restaurant dans la Tour Eiffel">
        <div>
            <h3><?= $text["index"]["experience_title"] ?></h3>
            <p>
                <?= $text["index"]["experience_text"] ?>
            </p>
        </div>
    </div>
</section>

<section class="section-dark">
    <div class="chef">
        <div>
            <h3><?= $text["index"]["chef_title"] ?></h3>
            <p>
                <?= $text["index"]["chef_text"] ?>
            </p>
        </div>
        <img src="style/img/chef.png" alt="Chef étoilé">
    </div>
</section>

<section class="section-light cta">
    <p><?= $text["index"]["cta_text"] ?></p>
    <a href="connexion.php"><?= $text["index"]["cta_button"] ?></a>
</section>

<footer>
    <p><?= $text["index"]["footer_rights"] ?></p>
    <a href="contact.php"><?= $text["index"]["footer_contact"] ?></a><span> |</span>
    <a href="condition_generale.php"><?= $text["index"]["footer_privacy"] ?></a>
</footer>
<script src="../javascript/chatbot.js"></script>
</body>
</html>
