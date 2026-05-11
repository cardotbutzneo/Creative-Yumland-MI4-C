<?php
require('getapikey.php');
session_start();

if (!isset($_SESSION["connecte"]) || $_SESSION["role"] !== "Client") {
    header("Location: connexion.php");
    exit();
}

require_once __DIR__."/../serveur.php";
$email = $_SESSION["email"];

function lire_json(string $chemin): array {
    if (!file_exists($chemin)) return [];
    return json_decode(file_get_contents($chemin), true) ?? [];
}

$catalogue_plats = lire_json("../data/plats.json");

$is_modification = (isset($_GET['action']) && $_GET['action'] === 'modification' && isset($_SESSION['modif_commande']));

if ($is_modification) {
    $data_modif = $_SESSION['modif_commande'];
    $id_cmd = $data_modif["id_cle"];
    $montant = $data_modif["reste_a_payer"];
    $transaction = substr("MOD" . bin2hex(random_bytes(8)), 0, 15);
    $type_label = "Modification (Supplément)";
    $articles_a_afficher = $data_modif["plats"];
    $id_retour = $id_cmd;

} else {
    $tous_paniers = lire_json("../data/paniers.json");
    $panier = $tous_paniers[$email] ?? ["articles" => [], "total" => 0];
    if (count($panier["articles"]) === 0) {
        header("Location: panier.php");
        exit();
    }
    $type = $_POST["type_commande"] ?? "sur place";
    $type_label = ucfirst(str_replace('_', ' ', $type));
    $montant    = $panier["total"];
    $transaction = substr(bin2hex(random_bytes(10)), 0, 15);
    $articles_a_afficher = $panier["articles"];

    $_SESSION["commande_en_attente"] = [
        "email"        => $email,
        "date"         => date("Y-m-d H:i:s"),
        "montant"      => number_format((float)$montant, 2, '.', ''),
        "livraison"    => ($type === "livraison"),
        "est-valide"   => false,
        "etat"         => "en_attente",
        "plats"        => $articles_a_afficher,
        "instructions" => $_POST["instructions"] ?? ""
    ];
}

$vendeur = "MI-4_C";
$api_key = getAPIKey($vendeur);
$retour = "http://" . $_SERVER["HTTP_HOST"] . "/html/retour_paiement.php";
$montant1 = number_format($montant, 2, '.', '');
$control = md5($api_key . "#" . $transaction . "#" . $montant1 . "#" . $vendeur . "#" . $retour . "#");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement — L'oro di Cicerone</title>
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/paiement.css">
</head>
<body>
    <header>
        <a href="index.php"><h1>L'oro di Cicerone</h1></a>
    </header>
    <main>
        <h2>Récapitulatif de votre règlement</h2>
        <div class="recap">
            <table>
                <thead>
                    <tr>
                        <th>PLAT</th>
                        <th>QUANTITÉ</th>
                        <th style="text-align:right;">SOUS-TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($articles_a_afficher)) {
                        foreach ($articles_a_afficher as $art) {
                            $nom = $art["nom"];
                            $qte = $art["quantite"];
                            $prix_u = $catalogue_plats[$nom]["prix"] ?? 0;
                            $st = $prix_u * $qte;
                    ?>
                        <tr>
                            <td class="nom"><?= htmlspecialchars($nom) ?></td>
                            <td class="qte">x <?= $qte ?></td>
                            <td class="sous-total"><?= number_format($st, 2, '.', '') ?>€</td>
                        </tr>
                    <?php }
                    } else {
                        echo "<tr><td colspan='3' style='text-align:center;padding:20px;'>Aucun article trouvé.</td></tr>";
                    } ?>
                </tbody>
            </table>
        </div>
        <div class="infos-commande">
            <div class="info-ligne">
                <span class="label">Type :</span>
                <span class="valeur"><?= htmlspecialchars($type_label) ?></span>
            </div>
        </div>
        <div class="total">
            <span>Total à régler :</span>
            <span class="montant"><?= $montant1 ?>€</span>
        </div>
        <form action="https://www.plateforme-smc.fr/cybank/index.php" method="POST">
            <input type="hidden" name="transaction" value="<?= $transaction ?>">
            <input type="hidden" name="montant" value="<?= $montant1 ?>">
            <input type="hidden" name="vendeur" value="<?= $vendeur ?>">
            <input type="hidden" name="retour" value="<?= $retour ?>">
            <input type="hidden" name="control" value="<?= $control ?>">
            <div class="action">
                <a href="<?= $is_modification ? 'modifier_commande.php?id=' . htmlspecialchars($id_retour) : 'panier.php' ?>">Retour</a>
                <button type="submit">Procéder au paiement</button>
            </div>
        </form>
    </main>
    <footer>
        <p>© 2026 L'oro di Cicerone — Tous droits réservés</p>
    </footer>
</body>
</html>