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

$etat_commande = $bdd_cmd[$derniere_cmd]["etat"];

if (
    $etat_commande !== "payee" &&
    $etat_commande !== "en preparation" &&
    $etat_commande !== "preparee" &&
    $etat_commande !== "livraison" &&
    $etat_commande !== "livree"
) {
    header("Location: remerciement.php");
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

            <div id="statutCommande" class="statut-commande">
                <?php if($etat_commande === "payee") { ?>
                    <p><?= $text["suivi_commande"]["paid_1"] ?></p>
                    <p><?= $text["suivi_commande"]["paid_2"] ?></p>
                <?php } elseif($etat_commande === "en preparation") { ?>
                    <p><?= $text["suivi_commande"]["preparing_1"] ?></p>
                    <p><?= $text["suivi_commande"]["preparing_2"] ?></p>
                <?php } elseif($etat_commande === "preparee") { ?>
                    <p><?= $text["suivi_commande"]["ready_1"] ?></p>
                    <p><?= $text["suivi_commande"]["ready_2"] ?></p>
                <?php } elseif($etat_commande === "livraison") { ?>
                    <p><?= $text["suivi_commande"]["delivery_1"] ?></p>
                    <p><?= $text["suivi_commande"]["delivery_2"] ?></p>
                <?php } elseif($etat_commande === "livree") { ?>
                    <p><?= $text["suivi_commande"]["delivered_1"] ?></p>
                    <p><?= $text["suivi_commande"]["delivered_2"] ?></p>
                    <a class="lien-notation" href="notation.php"><?= $text["suivi_commande"]["rate_link"] ?></a>
                <?php } ?>
            </div>
        </div>
    </main>
    <footer>
        <p><?= $text["suivi_commande"]["footer_rights"] ?></p>
    </footer>
    <script>
        const numeroCommande = <?= json_encode($derniere_cmd) ?>;
        let etatActuel = <?= json_encode($etat_commande) ?>;

        const textesEtat = {
            "payee": {
                ligne1: <?= json_encode($text["suivi_commande"]["paid_1"]) ?>,
                ligne2: <?= json_encode($text["suivi_commande"]["paid_2"]) ?>
            },
            "en preparation": {
                ligne1: <?= json_encode($text["suivi_commande"]["preparing_1"]) ?>,
                ligne2: <?= json_encode($text["suivi_commande"]["preparing_2"]) ?>
            },
            "preparee": {
                ligne1: <?= json_encode($text["suivi_commande"]["ready_1"]) ?>,
                ligne2: <?= json_encode($text["suivi_commande"]["ready_2"]) ?>
            },
            "livraison": {
                ligne1: <?= json_encode($text["suivi_commande"]["delivery_1"]) ?>,
                ligne2: <?= json_encode($text["suivi_commande"]["delivery_2"]) ?>
            },
            "livree": {
                ligne1: <?= json_encode($text["suivi_commande"]["delivered_1"]) ?>,
                ligne2: <?= json_encode($text["suivi_commande"]["delivered_2"]) ?>,
                lienNotation: <?= json_encode($text["suivi_commande"]["rate_link"]) ?>
            }
        };

        function afficherEtatCommande(etat) {
            const blocStatut = document.getElementById("statutCommande");
            if (!textesEtat[etat]) {
                window.location.href = "remerciement.php";
                return;
            }
            let html = `
                <p>${textesEtat[etat].ligne1}</p>
                <p>${textesEtat[etat].ligne2}</p>
            `;
            if (etat === "livree") {
                html += `
                    <a class="lien-notation" href="notation.php">
                        ${textesEtat[etat].lienNotation}
                    </a>
                `;
            }
            blocStatut.innerHTML = html;
        }

        async function verifierEtatCommande() {
            try {
                const reponse = await fetch("../api/get_new_commande.php", {
                    method: "GET",
                    cache: "no-store"
                });
                if (!reponse.ok) {
                    throw new Error("Erreur lors de la récupération des commandes");
                }
                const commandes = await reponse.json();
                if (!commandes[numeroCommande]) {
                    window.location.href = "profil_client.php?err=fetchFailed";
                    return;
                }
                const nouvelEtat = commandes[numeroCommande].etat;
                if (nouvelEtat !== etatActuel) {
                    etatActuel = nouvelEtat;
                    afficherEtatCommande(nouvelEtat);
                }
            } catch (erreur) {
                console.error("Impossible de vérifier l'état de la commande :", erreur);
            }
        }
        //envoie la requete toutes les 15 secondes
        setInterval(verifierEtatCommande, 15000);
    </script>
</body>
</html>