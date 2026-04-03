<?php
session_start();

if (!isset($_SESSION["connecte"])) {
    header("Location: connexion.php");
    exit;
}

require('getapikey.php');

function lire_data(string $chemin, string $nom_utilisateur = ""): array {
    if (!file_exists($chemin)) return [];
    $data = json_decode(file_get_contents($chemin), true);
    if ($data === null) return [];
    if ($nom_utilisateur !== "") {
        return $data[$nom_utilisateur] ?? [];
    }
    return $data;
}

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

$hash = md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#accepted#");
$hash1 = md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#refused#");

$paiement_valide = false;
$statut_reel = "denied";

if ($control === $hash) {
    $paiement_valide = true;
    $statut_reel = "accepted";
} elseif ($control === $hash1) {
    $paiement_valide = true;
    $statut_reel = "refused";
}

if ($paiement_valide) {
    $paniers = lire_data($chemin_paniers);
    if (isset($paniers[$email])) {
        $paniers[$email]["articles"] = [];
        $paniers[$email]["total"] = 0;
        sauvegarder_data($chemin_paniers, $paniers);
    }
    $commandes = lire_data($chemin_commandes);
    $clients = lire_data($chemin_clients);
    if ($statut_reel === "accepted") {
        if (isset($_SESSION["commande_en_attente"])) {
            $nv_id = generer_id($commandes);
            $nv = $_SESSION["commande_en_attente"];
            $nv["est-valide"] = true;
            $nv["etat"] = "en preparation";
            $nv["numero"] = str_pad(count($commandes) + 1, 8, "0", STR_PAD_LEFT);
            $commandes[$nv_id] = $nv;
            sauvegarder_data($chemin_commandes, $commandes);
            if (isset($clients[$email])) {
                if (!isset($clients[$email]["dernieres_commandes"])) {
                    $clients[$email]["dernieres_commandes"] = [];
                }
                array_unshift($clients[$email]["dernieres_commandes"], $nv_id);
                $clients[$email]["dernieres_commandes"] = array_slice($clients[$email]["dernieres_commandes"], 0, 10);
                sauvegarder_data($chemin_clients, $clients);
            }
            $id_affichage = $nv["numero"];
            unset($_SESSION["commande_en_attente"]); 
        } else {
            if (isset($clients[$email]["dernieres_commandes"][0])) {
                $derniere_cle = $clients[$email]["dernieres_commandes"][0];
                $id_affichage = $commandes[$derniere_cle]["numero"] ?? "Inconnu";
            } else {
                $id_affichage = "En cours...";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Retour paiement – L'oro di Cicerone</title>
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
            <?php if (!$paiement_valide) { ?>
                <div class="icon error">✕</div>
                <h2>Erreur de sécurité</h2>
                <p>La signature de la transaction est invalide. Vos données n'ont pas pu être vérifiées.</p>
                <div class="cta">
                    <a href="panier.php">Retour au panier</a>
                </div>
            <?php } elseif ($statut_reel === "accepted") { ?>
                <div class="icon success">✓</div>
                <h2>Commande confirmée !</h2>
                <?php $id_affichage = count($commandes); ?>
                <p>Merci pour votre confiance. Votre commande n°<strong><?= $id_affichage ?></strong> est désormais en cuisine.</p>
                <div class="cta">
                    <a href="profil_client.php">Suivre ma commande</a>
                </div>
            <?php } else { ?>
                <div class="icon refused">!</div>
                <h2>Paiement refusé</h2>
                <p>La transaction a été déclinée par votre établissement bancaire.</p>
                <div class="cta">
                    <a href="panier.php">Réessayer le paiement</a>
                </div>
            <?php } ?>
        </div>
    </main>
    <footer>
        <p>© 2026 L'oro di Cicerone — Tous droits réservés</p>
    </footer>
</body>
</html>