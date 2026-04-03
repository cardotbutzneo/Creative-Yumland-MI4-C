<?php
require('getapikey.php');
session_start();

if (!isset($_SESSION["connecte"]) || $_SESSION["role"] != "Client") {
    header("Location: connexion.php");
    exit();
}

$email = $_SESSION["email"];

function lire_data(string $chemin, string $nom_utilisateur = ""): array {
    if (!file_exists($chemin)) return [];
    $data = json_decode(file_get_contents($chemin), true);
    if ($data === null) return [];
    if ($nom_utilisateur !== "") {
        return $data[$nom_utilisateur] ?? [];
    }
    return $data;
}

function sauvegarder(string $fichier, array $data): void {
    file_put_contents($fichier, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$tous_paniers = lire_data("../data/paniers.json");
$panier = $tous_paniers[$email] ?? ["articles" => [], "total" => 0];

if (count($panier["articles"]) === 0) {
    header("Location: panier.php");
    exit();
}

$plats = [];
foreach ($panier["articles"] as $article) {
    $plats[] = [
        "nom"      => $article["nom"],
        "quantite" => $article["quantite"]
    ];
}

$type_commande = $_POST["type_commande"] ?? "sur place";
$instructions = $_POST["instructions"] ?? "";
$livraison = $type_commande === "livraison";
$livraison_tard = isset($_POST["livraison_plus_tard"]) && $_POST["livraison_plus_tard"] === "1";
$date_livraison = ($livraison_tard && $livraison) ? ($_POST["date_livraison"] ?? "") : "";

$vendeur = "MI-4_C";
$montant = number_format($panier["total"], 2, '.', '');
$date = date("Y-m-d H:i:s");

$commandes = lire_data("../data/commandes.json");
$numero = str_pad(count($commandes) + 1, 8, "0", STR_PAD_LEFT);
$transaction = uniqid();
$nv = [
    "email"        => $email,
    "date"         => $date,
    "montant"      => $montant,
    "numero"       => $numero,
    "livraison"    => $livraison,
    "est-valide"   => false,
    "etat"         => "en_attente",
    "plats"        => $plats,
    "instructions" => $instructions,
    "livreur"      => null
];

if ($livraison && $date_livraison !== "") {
    $nv["date_livraison"] = $date_livraison;
}

$nv["transaction"] = $transaction;
$_SESSION["commande_en_attente"] = $nv;
$retour  = "http://" . $_SERVER["HTTP_HOST"] . "/html/retour_paiement.php";
$api_key = getAPIKey($vendeur);
$control = md5($api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $retour . "#");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement – L'oro di Cicerone</title>
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/paiement.css">
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
    <main>
        <h2>Récapitulatif de votre commande</h2>
        <div class="recap">
            <table>
                <thead>
                    <tr>
                        <th>Plat</th>
                        <th>Quantité</th>
                        <th>Sous-total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($panier["articles"] as $article) { ?>
                    <tr>
                        <td class="nom"><?= $article["nom"] ?></td>
                        <td class="qte">× <?= (int)$article["quantite"] ?></td>
                        <td class="sous-total"><?= number_format($article["prix"] * $article["quantite"], 2, ',', '') ?>€</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="infos-commande">
            <div class="info-ligne">
                <span class="label">Type de commande</span>
                <span class="valeur"><?= $livraison ? "Livraison" : "Sur place" ?></span>
            </div>
            <?php if ($livraison && $date_livraison !== "") { ?>
            <div class="info-ligne">
                <span class="label">Livraison prévue</span>
                <span class="valeur"><?= $date_livraison ?></span>
            </div>
            <?php } ?>
            <?php if ($instructions !== "") { ?>
            <div class="info-ligne">
                <span class="label">Instructions</span>
                <span class="valeur"><?= $instructions ?></span>
            </div>
            <?php } ?>
        </div>
        <div class="total">
            <span>Total à payer</span>
            <span class="montant"><?= $montant ?>€</span>
        </div>
        <form action="https://www.plateforme-smc.fr/cybank/index.php" method="POST">
            <input type="hidden" name="transaction" value="<?= $transaction ?>">
            <input type="hidden" name="montant" value="<?= $montant ?>">
            <input type="hidden" name="vendeur" value="<?= $vendeur ?>">
            <input type="hidden" name="retour" value="<?= $retour ?>">
            <input type="hidden" name="control" value="<?= $control ?>">
            <div class="action">
                <a href="panier.php">Retour au panier</a>
                <button type="submit">Valider et payer</button>
            </div>
        </form>
    </main>
    <footer>
        <p>© 2026 L'oro di Cicerone — Tous droits réservés</p>
        <a href="contact.php">Nous contacter</a>
    </footer>
</body>
</html>