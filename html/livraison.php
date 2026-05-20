<?php

require_once __DIR__."/../api/config.php";

verifier_connexion($role,"livreur");

$email_livreur = $_SESSION["email"];
$bdd_client = $data_client;
$bdd_cmd = $data_commandes;

//le livreur choisi une commande
if (isset($_POST["action"]) && $_POST["action"] === "prendre_commande") {

    $num_cmd = $_POST["numero_cmd"] ?? null;

    if ($num_cmd && isset($bdd_cmd[$num_cmd])) {
        $cmd = $bdd_cmd[$num_cmd];

        if ($cmd["livraison"] === true && $cmd["etat"] === "preparee") {
            //mise à jour des données dans la base de données
            $bdd_cmd[$num_cmd]["etat"] = "livraison";
            $bdd_cmd[$num_cmd]["livreur"] = $email_livreur;
            $bdd_client[$email_livreur]["livraison"] = $num_cmd;
            ecrire_data("../data/commandes.json", $bdd_cmd);
            ecrire_data("../data/client.json", $bdd_client);
        }
    }

    header("Location: livraison.php");
    exit;
}

//le livreur termine sa livraison
if (isset($_POST["action"]) && $_POST["action"] === "terminer_livraison") {

    $num_cmd = $_POST["numero_cmd"] ?? null;
    if ($num_cmd && isset($bdd_cmd[$num_cmd])) {
        //mise a jour des données dans la base de données
        $email_client = $bdd_cmd[$num_cmd]["email"] ?? null;
        $bdd_cmd[$num_cmd]["etat"] = "livree";
        $bdd_cmd[$num_cmd]["livraison"] = false;
        $bdd_client[$email_livreur]["livraison"] = false;
        if ($email_client && isset($bdd_client[$email_client])) {
            $bdd_client[$email_client]["livraison"] = false;
        }
        ecrire_data("../data/commandes.json", $bdd_cmd);
        ecrire_data("../data/client.json", $bdd_client);
    }

    header("Location: livraison.php");
    exit;
}

//le livreur abandonne sa livraison
if (isset($_POST["action"]) && $_POST["action"] === "abandonner_livraison") {

    $num_cmd = $_POST["numero_cmd"] ?? null;
    if ($num_cmd && isset($bdd_cmd[$num_cmd])) {
        //mise a jour des données dans la base de données
        $bdd_cmd[$num_cmd]["etat"] = "preparee";
        $bdd_cmd[$num_cmd]["livreur"] = null;
        $bdd_client[$email_livreur]["livraison"] = false;
        ecrire_data("../data/commandes.json", $bdd_cmd);
        ecrire_data("../data/client.json", $bdd_client);
    }

    header("Location: livraison.php");
    exit;
}

$livreur_data = $bdd_client[$email_livreur] ?? [];
$num_cmd_actif = $livreur_data["livraison"] ?? false;

$commande_actuelle = null;
$client_data = null;

//récupèrer information du client de la commande choisie
if ($num_cmd_actif && isset($bdd_cmd[$num_cmd_actif])) {
    $commande_actuelle = $bdd_cmd[$num_cmd_actif];
    $email_client = $commande_actuelle["email"] ?? null;
    if ($email_client && isset($bdd_client[$email_client])) {
        $client_data = $bdd_client[$email_client];
    }
}

$commandes_disponibles = [];

if (!$num_cmd_actif) {
    //parcours des commandes disponibles à prendre à afficher
    foreach ($bdd_cmd as $num => $cmd) {
        if (isset($cmd["livraison"], $cmd["etat"]) && $cmd["livraison"] === true && $cmd["etat"] === "preparee") {
            $commandes_disponibles[$num] = $cmd;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/livraison.css">
    <title>Livraison - L'oro di Cicerone</title>
</head>
<body>

<header>
    <a href="index.php"><h1>L'oro di Cicerone</h1></a>
    <nav>
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="presentation.php">Menu</a></li>
            <li><a href="deconnexion.php">se déconnecter</a></li>
        </ul>
    </nav>
</header>

<main class="conteneur-livraison">

    <h2 class="titre-page">Espace livreur</h2>

    <?php if ($commande_actuelle && $client_data) { ?>

        <section class="carte-livraison">

            <div class="ligne-information">
                <span class="intitule">Numéro de commande</span>
                <span class="valeur"><?= htmlspecialchars($num_cmd_actif) ?></span>
            </div>
            <div class="ligne-information">
                <span class="intitule">Nom</span>
                <span class="valeur"><?= htmlspecialchars(strtoupper($client_data["nom"] ?? "—")) ?></span>
            </div>
            <div class="ligne-information">
                <span class="intitule">Prénom</span>
                <span class="valeur"><?= htmlspecialchars(ucfirst($client_data["prenom"] ?? "—")) ?></span>
            </div>
            <div class="ligne-information">
                <span class="intitule">Adresse e-mail</span>
                <span class="valeur">
                    <?php $mail = htmlspecialchars($client_data["contact"]["adresse email"] ?? "") ?>
                    <a href="mailto:<?= $mail ?>"><?= $mail ?></a>
                </span>
            </div>
            <div class="ligne-information">
                <span class="intitule">Téléphone</span>
                <span class="valeur">
                    <?php $tel = htmlspecialchars($client_data["contact"]["téléphone"] ?? "") ?>
                    <a href="tel:<?= $tel ?>"><?= $tel ?></a>
                </span>
            </div>
            <div class="bloc-adresse">
                <div class="intitule">Adresse de livraison</div>
                <?php
                    $adresse = $client_data["contact"]["adresse"] ?? "";
                    $complement = $client_data["contact"]["complément d'adresse"] ?? "";
                    $query = urlencode($adresse);
                ?>
                <div class="valeur">
                    <?= htmlspecialchars($adresse) ?>
                    <?php if ($complement){ ?>
                        <br><?= htmlspecialchars($complement) ?>
                    <?php } ?>
                </div>
                <a class="lien-maps" target="_blank"
                   href="https://www.google.com/maps/search/?api=1&query=<?= $query ?>">
                    Ouvrir dans Google Maps
                </a>
            </div>

            <form method="POST" action="livraison.php">
                <input type="hidden" name="action" value="terminer_livraison">
                <input type="hidden" name="numero_cmd" value="<?= htmlspecialchars($num_cmd_actif) ?>">
                <input type="submit" value="Terminer la livraison" class="bouton-validation">
            </form>

            <form method="POST" action="livraison.php">
                <input type="hidden" name="action" value="abandonner_livraison">
                <input type="hidden" name="numero_cmd" value="<?= htmlspecialchars($num_cmd_actif) ?>">
                <input type="submit" value="Abandonner la commande" class="bouton-abandon">
            </form>

        </section>

    <?php } elseif (!empty($commandes_disponibles)) { ?>

        <p class="message-info">Vous n'avez pas de livraison en cours. Prenez une commande ci-dessous :</p>

        <?php foreach ($commandes_disponibles as $num => $cmd) {
            $email_client_cmd = $cmd["email"] ?? null;
            $cli = $email_client_cmd ? ($bdd_client[$email_client_cmd] ?? []) : [];
            $adresse_cmd = $cli["contact"]["adresse"] ?? "Adresse inconnue";
            $complement_cmd = $cli["contact"]["complément d'adresse"] ?? "";
        ?>
        <section class="carte-livraison carte-disponible">

            <div class="ligne-information">
                <span class="intitule">Numéro de commande</span>
                <span class="valeur"><?= htmlspecialchars($num) ?></span>
            </div>
            <div class="ligne-information">
                <span class="intitule">Adresse</span>
                <span class="valeur">
                    <?= htmlspecialchars($adresse_cmd) ?>
                    <?= $complement_cmd ? " - " . htmlspecialchars($complement_cmd) : "" ?>
                </span>
            </div>
            <?php if (!empty($cmd["date"])) { ?>
            <div class="ligne-information">
                <span class="intitule">Date de commande</span>
                <span class="valeur"><?= htmlspecialchars($cmd["date"]) ?></span>
            </div>
            <?php } ?>

            <form method="POST" action="livraison.php">
                <input type="hidden" name="action" value="prendre_commande">
                <input type="hidden" name="numero_cmd" value="<?= htmlspecialchars($num) ?>">
                <input type="submit" value="Prendre cette commande" class="bouton-validation">
            </form>

        </section>
        <?php } ?>

    <?php } else { ?>

        <section class="carte-livraison">
            <p class="message-info">Aucune commande en attente de livraison pour le moment.</p>
        </section>

    <?php } ?>

</main>
<?php if (empty($commandes_disponibles) && !$commande_actuelle) { ?>
<script>
    setInterval(async () => {
        const res = await fetch("../api/commandes_disponibles.php");
        const data = await res.json();
        if (data.disponible) location.reload();
    }, 10000);
</script>
<?php } ?>

</body>
</html>
</body>
</html>