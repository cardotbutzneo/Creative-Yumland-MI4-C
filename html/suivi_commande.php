<?php
session_start();

if (!isset($_SESSION["email"]) or ($_SESSION["role"] != "Client")){
    header("Location: connexion.php?error=unauthorized");
    exit;
}

require_once __DIR__."/../serveur.php";

$email = $_SESSION["email"];
$bdd_client = lire_data("../data/client.json", $email);

$derniere_cmd = $bdd_client["dernieres_commandes"][0];
$bdd_cmd = lire_data("../data/commandes.json");

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/suivi_commande.css">
    <title>Suivre ma commande - L'oro di Cicerone</title>
</head>
<body>
    <header>
        <a href="index.php"><h1>L'oro di Cicerone</h1></a>
        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="presentation.php">Menu</a></li>
                <li><a href="modifier_profil.php">Modifier le profil</a></li>
                <li><a href="securite.php">Sécurité</a></li>
                <li><a href="deconnexion.php">Se déconnecter</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="bulle">
            <h1>Suivi de ma commande</h1>

            <?php if($bdd_cmd[$derniere_cmd]["etat"] === "en preparation") { ?>
                <div class="statut-commande">
                    <p>Votre commande est en cours de préparation.</p>
                    <p>Notre équipe fait actuellement de son mieux pour préparer le plus rapidement votre commande.</p>
                </div>
            <?php } elseif($bdd_cmd[$derniere_cmd]["etat"] === "preparee") { ?>
                <div class="statut-commande">
                    <p>Votre commande attend actuellement d'être livrée.</p>
                    <p>Nous attendons qu'un livreur vienne chercher votre commande.</p>
                </div>
            <?php } elseif($bdd_cmd[$derniere_cmd]["etat"] === "en livraison") { ?>
                <div class="statut-commande">
                    <p>Votre commande est en cours de livraison.</p>
                    <p>Soyez réactif, le livreur arrivera bientôt !</p>
                </div>
            <?php } elseif($bdd_cmd[$derniere_cmd]["etat"] === "livree") { ?>
                <div class="statut-commande">
                    <p>Votre commande est livrée avec succès !</p>
                    <p>Votre avis nous intéresse.</p>
                    <a class="lien-notation" href="notation.php">Cliquer ici pour noter la commande</a>
                </div>
            <?php } else {
                header("Location: remerciement.php");
            } ?>
        </div>
    </main>

    <footer>
        <p>© 2026 L'oro di Cicerone — Tous droits réservés</p>
    </footer>
</body>
</html>