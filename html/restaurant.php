<?php
require_once __DIR__."/../api/config.php";
?>

<!DOCTYPE html>
<html lang="<?php if ($isFrench) echo "fr"; else echo "en"; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $text["restaurant"]["title"] ?></title>
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/restaurant.css">
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
            <li><a href="connexion.php"><?= $text["restaurant"]["nav_booking"] ?></a></li>
        </ul>
    </nav>
    </header>
    <section class="section-light">
        <div class="experience">
            <p><?= $text["restaurant"]["text_1"] ?></p>
            <img src="style/img/vue ensemble resto.jpg" alt="<?= $text["restaurant"]["img_restaurant_alt"] ?>">
        </div>
    </section>
    <section class="section-dark">
        <div class="experience">
            <img src="style/img/chef.png" alt="<?= $text["restaurant"]["img_chef_alt"] ?>">
            <p>
                <?= $text["restaurant"]["text_2"] ?>
            </p>
        </div>
    </section>
    <section class="section-light">
        <div class="experience">
            <p>
                <?= $text["restaurant"]["text_3"] ?>
            </p>
            <img src="style/img/raviolis.png" alt="<?= $text["restaurant"]["img_pasta_alt"] ?>">
        </div>
    </section>
    <section class="section-dark">
        <p>
            <?= $text["restaurant"]["text_4"] ?>
        </p>
    </section>
    <footer>
        <p><?= $text["index"]["footer_rights"] ?></p>
        <a href="contact.php"><?= $text["index"]["footer_contact"] ?></a><span> |</span>
        <a href="condition_generale.php"><?= $text["index"]["footer_privacy"] ?></a>
    </footer>
</body>
</html>