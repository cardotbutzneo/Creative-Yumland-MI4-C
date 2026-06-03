<?php

require_once __DIR__."/../api/config.php";

?>
<!DOCTYPE html>
<html lang="<?php if ($isFrench) echo "fr"; else echo "en"; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $text["chef"]["title"] ?></title>
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/chef.css">
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
            <li><a href="connexion.php"><?= $text["chef"]["nav_booking"] ?></a></li>
        </ul>
    </nav>
    </header>
    <main>
    <section class="chef-hero">
    <video autoplay muted loop playsinline>
    <source src="style/video/chef.mp4" type="video/mp4">
    </video>
    <h2><?= $text["chef"]["hero_title"] ?><br>
        <span class="sous-titre"><?= $text["chef"]["hero_subtitle"] ?></span>
    </h2>
    </section>
        <section class="section-light">
            <div class="experience">
                <img src="style/img/chef.png" alt="<?= $text["chef"]["hero_title"] ?>">
                    <div>
                        <h3><?= $text["chef"]["exception_title"] ?></h3>
                        <p>
                        <?= $text["chef"]["exception_text_1"] ?>
                        </p>
                        <p>
                        <?= $text["chef"]["exception_text_2"] ?>
                        </p>
                    </div>
            </div>
        </section>
        <section>
            <div class="philosophy">
                <h3><?= $text["chef"]["philosophy_title"] ?></h3>
                <p>
                <?= $text["chef"]["philosophy_text"] ?>
                </p>
                <p class="quote"><?= $text["chef"]["quote"] ?></p>
            </div>
        </section>
        <section class="section-light">
            <h3 style="text-align:center;"><?= $text["chef"]["awards_title"] ?></h3>
            <div class="awards">
                <div class="award">
                    <h4><?= $text["chef"]["award_michelin_title"] ?></h4>
                    <p><?= $text["chef"]["award_michelin_text"] ?></p>
                </div>
                <div class="award">
                    <h4><?= $text["chef"]["award_year_title"] ?></h4>
                    <p><?= $text["chef"]["award_year_text"] ?></p>
                </div>
                <div class="award">
                    <h4><?= $text["chef"]["award_innovation_title"] ?></h4>
                    <p><?= $text["chef"]["award_innovation_text"] ?></p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>