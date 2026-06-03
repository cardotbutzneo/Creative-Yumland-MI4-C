<?php

require_once __DIR__."/../api/config.php";

verifier_connexion($role,"Client");

$email = $_SESSION["email"];
$bdd_client = lire_data("../data/client.json", $email);

if (empty($bdd_client["dernieres_commandes"])) {
    header("Location: profil_client.php?error=no_order");
    exit;
}

$derniere_cmd = $bdd_client["dernieres_commandes"][0];
$derniere_cmd = strtoupper($derniere_cmd);

$bdd_cmd = $data_commandes;

if (!isset($bdd_cmd[$derniere_cmd])) {
    header("Location: profil_client.php?err=fetchFailed");
    exit;
}

?>

<!DOCTYPE html>
<html lang="<?php if ($isFrench) echo "fr"; else echo "en"; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/suivi_commande.css">
    <title><?= $text["suivi_commande"]["title"] ?></title>
</head>
<body>
    <header>
        <a href="index.php"><h1>L'oro di Cicerone</h1></a>
        <nav>
            <ul>
                <li><a href="index.php"><?php if ($isFrench) echo "Accueil"; else echo "Home page"; ?></a></li>
                <li><a href="presentation.php">Menu</a></li>
                <li><a href="modifier_profil.php"><?= $text["suivi_commande"]["nav_edit_profile"] ?></a></li>
                <li><a href="securite.php"><?= $text["suivi_commande"]["nav_security"] ?></a></li>
                <li><a href="deconnexion.php"><?= $text["suivi_commande"]["nav_logout"] ?></a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="bulle">
            <h1><?= $text["suivi_commande"]["page_title"] ?></h1>
            <?php if($bdd_cmd[$derniere_cmd]["etat"] === "payee") { ?>
                <div class="statut-commande">
                    <p><?= $text["suivi_commande"]["paid_1"] ?></p>
                    <p><?= $text["suivi_commande"]["paid_2"] ?></p>
                </div>
            <?php } elseif($bdd_cmd[$derniere_cmd]["etat"] === "en preparation") { ?>
                <div class="statut-commande">
                    <p><?= $text["suivi_commande"]["preparing_1"] ?></p>
                    <p><?= $text["suivi_commande"]["preparing_2"] ?></p>
                </div>
            <?php } elseif($bdd_cmd[$derniere_cmd]["etat"] === "preparee") { ?>
                <div class="statut-commande">
                    <p><?= $text["suivi_commande"]["ready_1"] ?></p>
                    <p><?= $text["suivi_commande"]["ready_2"] ?></p>
                </div>
            <?php } elseif($bdd_cmd[$derniere_cmd]["etat"] === "livraison") { ?>
                <div class="statut-commande">
                    <p><?= $text["suivi_commande"]["delivery_1"] ?></p>
                    <p><?= $text["suivi_commande"]["delivery_2"] ?></p>
                </div>
            <?php } elseif($bdd_cmd[$derniere_cmd]["etat"] === "livree") { ?>
                <div class="statut-commande">
                    <p><?= $text["suivi_commande"]["delivered_1"] ?></p>
                    <p><?= $text["suivi_commande"]["delivered_2"] ?></p>
                    <a class="lien-notation" href="notation.php"><?= $text["suivi_commande"]["rate_link"] ?></a>
                </div>
            <?php } else {
                header("Location: remerciement.php");
                exit;
            } ?>
        </div>
    </main>
    <footer>
        <p><?= $text["suivi_commande"]["footer_rights"] ?></p>
    </footer>
</body>
</html>