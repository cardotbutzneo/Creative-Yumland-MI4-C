<?php

require_once __DIR__."/../api/config.php";

verifier_connexion($role,"Cuisinier");

?>
<!DOCTYPE html>
<html lang=<?= $langue ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/commandes.css">
    <link rel="stylesheet" href="style/notification.css">
    <script src="../script.js" defer></script>
    <script src="../javascript/commande.js"></script>
    <title><?= $text["commandes"]["title"] ?></title>
</head>
<body>
    <input type="hidden" id="nb-cmd" data-nb_cmd="<?= count($data_commandes) ?>">
    <header>
    <a href="index.php"><h1>L'oro di Cicerone</h1></a>
    <nav>
        <ul>
            <li><a href="index.php"><?= $text["commandes"]["nav_home"] ?></a></li>
            <li><a href="historique_notation.php"><?= $text["commandes"]["nav_reviews"] ?></a></li>
            <li><a href="deconnexion.php"><?= $text["commandes"]["nav_logout"] ?></a></li>
        </ul>
    </nav>
</header>
    <div>
        <form method="POST" id="barre-recherche">
            <input type="text" name="id_cmd" placeholder='<?= $text["commandes"]["search_placeholder"] ?>'>
            <input type="submit" name="id_cmd">
        </form>
    </div>
    <div class="notification" id="notification" style="display : none">
        <div class="notification-header">
            <span class="notification-titre">Notification</span>
            <button class="notification-close" onclick="this.closest('.notification').style.display='none'">✕</button>
        </div>
        <p class="notification-body">
            <?= $text["commmandes"]["notification_body"] ?>
        </p>
        <div class="notification-barre">
            <div class="notification-barre-fill"></div>
        </div>
    </div>

    <div id="liste-commandes">
        <p><?= $text["commandes"]["loading"] ?></p>
    </div>
    <script>
        // chargement des commandes au démarages
        chargerCommandes();
        setInterval(chargerCommandes,10000);
    </script>
</body>
</html>