<?php
require_once __DIR__."/../api/config.php";
verifier_connexion($role,"Client");

require('getapikey.php');

function sauvegarder_data(string $chemin, array $data): void {
    file_put_contents($chemin, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function generer_id(array $tab): string {
    do {
        $id = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
    } while (isset($tab[$id]));
    return $id;
}

$chemin_paniers = "../data/paniers.json";
$chemin_commandes = "../data/commandes.json";
$chemin_clients = "../data/client.json";

$transaction = $_GET["transaction"] ?? '';
$montant = $_GET["montant"] ?? '';
$vendeur = $_GET["vendeur"] ?? '';
$control = $_GET["control"] ?? '';

$email = $_SESSION["email"];
$api_key = getAPIKey($vendeur);

$hash_accepted = md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#accepted#");
$hash_refused = md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#refused#");

$paiement_valide = false;
$statut_reel = "denied";

if ($control === $hash_accepted) {
    $paiement_valide = true;
    $statut_reel = "accepted";
} elseif ($control === $hash_refused) {
    $paiement_valide = true;
    $statut_reel = "refused";
}

if ($paiement_valide && $statut_reel === "accepted") {
    $commandes = $data_commandes;
    $clients = $data_client;

    if (strpos($transaction, 'MOD') === 0 && isset($_SESSION["modif_commande"])) {
        $m = $_SESSION["modif_commande"];
        $id_cle = $m["id_cle"];
        if (isset($commandes[$id_cle])) {
            $commandes[$id_cle]["plats"] = $m["plats"];
            $commandes[$id_cle]["montant"] = number_format((float)$m["total"], 2, '.', '');
            $commandes[$id_cle]["deja_modifie"] = true;

            sauvegarder_data($chemin_commandes, $commandes);
            $id_affichage = $commandes[$id_cle]["numero"];
            unset($_SESSION["modif_commande"]);
        }

    } elseif (isset($_SESSION["commande_en_attente"])) { // sauvegarder la commande
        $nv_id = generer_id($commandes);
        $nv = $_SESSION["commande_en_attente"];
        $nv["est-valide"] = true;
        $nv["etat"] = "payee";
        $nv["numero"] = str_pad(count($commandes) + 1, 8, "0", STR_PAD_LEFT);

        $commandes[$nv_id] = $nv;
        sauvegarder_data($chemin_commandes, $commandes);

        foreach ($data_panier[$email]["articles"] as $nom_plat => $details){
            if (!isset($data_plats[$nom_plat])) {
                continue; 
            }

            if ($data_plats[$nom_plat]["categorie"] !== "entrees" && $data_plats[$nom_plat]["categorie"] !== "plats") {
                continue;
            }
            $data_plats[$nom_plat]["nb_commandee"] += $details["quantite"];
        }

        sauvegarder_data("../data/plats.json",$data_plats); // on sauvegarde

        $paniers = $data_panier;
        if (isset($paniers[$email])) {
            $paniers[$email]["articles"] = [];
            $paniers[$email]["total"] = 0;
            sauvegarder_data($chemin_paniers, $paniers);
        }

        if (isset($clients[$email])) {
            if (!isset($clients[$email]["dernieres_commandes"])) {
                $clients[$email]["dernieres_commandes"] = [];
            }
            array_unshift($clients[$email]["dernieres_commandes"], $nv_id);
            $clients[$email]["dernieres_commandes"] = array_slice($clients[$email]["dernieres_commandes"], 0, 10);

            if (function_exists('calculer_points')) {
                $pts = calculer_points($nv["montant"], $clients[$email]["total-fidelite"]);
                $clients[$email]["total-fidelite"] += $pts;
                $clients[$email]["pts-fidelite"] += $pts;
                $_SESSION["total-fidelite"] = $clients[$email]["total-fidelite"];
                $_SESSION["pts-fidelite"] = $clients[$email]["pts-fidelite"];
            }
            sauvegarder_data($chemin_clients, $clients);
        }
        $id_affichage = $nv["numero"];
        unset($_SESSION["commande_en_attente"]);
    }
}
?>

<!DOCTYPE html>
<html lang="<?php if ($isFrench) echo "fr"; else echo "en"; ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $text["retour_paiement"]["title"] ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/retour_paiement.css">
</head>
<body>
    <header>
        <a href="index.php"><h1>L'oro di Cicerone</h1></a>
        <nav>
            <ul>
                <li><a href="index.php"><?php if ($isFrench) echo "Accueil"; else echo "Home page"; ?></a></li>
                <li><a href="presentation.php">Menu</a></li>
                <li><a href="panier.php"><?= $text["retour_paiement"]["nav_cart"] ?></a></li>
                <li><a href="profil_client.php"><?= $text["retour_paiement"]["nav_profile"] ?></a></li>
            </ul>
        </nav>
    </header>
    <main class="status-container">
        <div class="status-card">
            <?php if (!$paiement_valide || $statut_reel === "denied") { ?>
                <div class="icon error">✕</div>
                <h2><?= $text["retour_paiement"]["error_title"] ?></h2>
                <p><?= $text["retour_paiement"]["error_message"] ?></p>
                <div class="cta"><a href="panier.php"><?= $text["retour_paiement"]["back_to_cart"] ?></a></div>
            <?php } elseif ($statut_reel === "refused") { ?>
                <div class="icon error">✕</div>
                <h2><?= $text["retour_paiement"]["refused_title"] ?></h2>
                <p><?= $text["retour_paiement"]["refused_message"] ?></p>
                <?php if (isset($_SESSION["modif_commande"])) { ?>
                    <div class="cta">
                        <a href="modifier_commande.php?id=<?= htmlspecialchars($_SESSION["modif_commande"]["id_cle"]) ?>">
                            <?= $text["retour_paiement"]["retry_modification"] ?>
                        </a>
                    </div>
                <?php } else { ?>
                    <div class="cta"><a href="panier.php"><?= $text["retour_paiement"]["back_to_cart"] ?></a></div>
                <?php } ?>

            <?php } else { ?>
                <div class="icon success">✓</div>
                <h2><?= $text["retour_paiement"]["success_title"] ?></h2>
                <p>
                    <?= $text["retour_paiement"]["success_message_before"] ?><strong><?= htmlspecialchars($id_affichage ?? $text["retour_paiement"]["unknown_order"]) ?></strong>
                    <?= $text["retour_paiement"]["success_message_after"] ?>
                </p>
                <div class="cta"><a href="profil_client.php"><?= $text["retour_paiement"]["view_orders"] ?></a></div>
            <?php } ?>
        </div>
    </main>
    <footer>
        <p><?= $text["index"]["footer_rights"] ?></p>
        <a href="contact.php"><?= $text["index"]["footer_contact"] ?></a><span> |</span>
        <a href="condition_generale.php"><?= $text["index"]["footer_privacy"] ?></a>
    </footer>
</body>
</html>