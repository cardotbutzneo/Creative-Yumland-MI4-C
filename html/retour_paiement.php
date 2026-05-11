<?php
session_start();

if (!isset($_SESSION["connecte"])) {
    header("Location: connexion.php");
    exit;
}

require('getapikey.php');
require_once __DIR__."/../serveur.php";

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
$hash_refused  = md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#refused#");

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
    $commandes = lire_data($chemin_commandes);
    $clients = lire_data($chemin_clients);

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

    } elseif (isset($_SESSION["commande_en_attente"])) {
        $nv_id = generer_id($commandes);
        $nv = $_SESSION["commande_en_attente"];
        $nv["est-valide"] = true;
        $nv["etat"] = "payee";
        $nv["numero"] = str_pad(count($commandes) + 1, 8, "0", STR_PAD_LEFT);

        $commandes[$nv_id] = $nv;
        sauvegarder_data($chemin_commandes, $commandes);

        $paniers = lire_data($chemin_paniers);
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
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Retour paiement — L'oro di Cicerone</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/retour_paiement.css">
</head>
<body>
    <header>
        <a href="index.php"><h1>L'oro di Cicerone</h1></a>
        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="presentation.php">Menu</a></li>
                <li><a href="panier.php">Mon panier</a></li>
                <li><a href="profil_client.php">Profil</a></li>
            </ul>
        </nav>
    </header>

    <main class="status-container">
        <div class="status-card">
            <?php if (!$paiement_valide || $statut_reel === "denied") { ?>
                <div class="icon error">✕</div>
                <h2>Erreur</h2>
                <p>Un problème est survenu lors du traitement de votre paiement.</p>
                <div class="cta"><a href="panier.php">Retour au panier</a></div>

            <?php } elseif ($statut_reel === "refused") { ?>
                <div class="icon error">✕</div>
                <h2>Paiement refusé</h2>
                <p>Votre paiement a été refusé par la banque. Votre commande n'a pas été modifiée.</p>
                <?php if (isset($_SESSION["modif_commande"])) { ?>
                    <div class="cta">
                        <a href="modifier_commande.php?id=<?= htmlspecialchars($_SESSION["modif_commande"]["id_cle"]) ?>">
                            Réessayer la modification
                        </a>
                    </div>
                <?php } else { ?>
                    <div class="cta"><a href="panier.php">Retour au panier</a></div>
                <?php } ?>

            <?php } else { ?>
                <div class="icon success">✓</div>
                <h2>Succès !</h2>
                <p>Transaction validée. Votre commande n°<strong><?= htmlspecialchars($id_affichage ?? 'Inconnue') ?></strong> est enregistrée.</p>
                <div class="cta"><a href="profil_client.php">Voir mes commandes</a></div>
            <?php } ?>
        </div>
    </main>
    <footer>
        <p>© 2026 L'oro di Cicerone — Tous droits réservés</p>
    </footer>
</body>
</html>