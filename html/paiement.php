<?php
require('getapikey.php');
require_once __DIR__."/../api/config.php";

verifier_connexion($role, "Client");
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

    $form_data = $_SESSION["panier_form"] ?? [];
    unset($_SESSION["panier_form"]);
    $type_raw = $_POST["type_commande"] ?? $form_data["type_commande"] ?? "sur_place";
    $type_label = ucfirst(str_replace('_', ' ', $type_raw));

    $total_brut_panier = 0;
    foreach ($panier["articles"] as $art) {
        $total_brut_panier += $art["prix"] * $art["quantite"];
    }

    $pts_client = $_SESSION["total-fidelite"] ?? 0;
    if ($pts_client >= 1200){
        $montant = ceil($total_brut_panier * 0.70);
    }
    elseif ($pts_client >= 500) { 
        $montant = ceil($total_brut_panier * 0.85);
    }
    else {
        $montant = $total_brut_panier;
    } 

    $transaction = substr(bin2hex(random_bytes(10)), 0, 15);
    $articles_a_afficher = $panier["articles"];

    $_SESSION["commande_en_attente"] = [
        "email" => $email,
        "date" => date("Y-m-d H:i:s"),
        "montant" => number_format((float)$montant, 2, '.', ''),
        "livraison" => ($type_raw === "livraison"),
        "est-valide" => false,
        "etat" => "en_attente",
        "plats" => $articles_a_afficher,
        "instructions" => $_POST["instructions"] ?? $form_data["instructions"] ?? ""
    ];
}

$vendeur = "MI-4_C";
$api_key = getAPIKey($vendeur);
$retour = "http://" . $_SERVER["HTTP_HOST"] . "/html/retour_paiement.php";
$montant_fmt = number_format((float)$montant, 2, '.', '');
$control = md5($api_key . "#" . $transaction . "#" . $montant_fmt . "#" . $vendeur . "#" . $retour . "#");
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
                            <td class="sous-total"><?= $st ?>€</td>
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
            <span class="montant"><?= $montant_fmt ?>€</span>
        </div>
        <form action="https://www.plateforme-smc.fr/cybank/index.php" method="POST">
            <input type="hidden" name="transaction" value="<?= $transaction ?>">
            <input type="hidden" name="montant" value="<?= $montant_fmt ?>">
            <input type="hidden" name="vendeur" value="<?= $vendeur ?>">
            <input type="hidden" name="retour" value="<?= $retour ?>">
            <input type="hidden" name="control" value="<?= $control ?>">

            <div class="action">
                <a href="<?= $is_modification ? 'modifier_commande.php?id=' . htmlspecialchars($id_retour) : 'panier.php' ?>">Retour</a>
                <button type="submit">Procéder au paiement</button>
            </div>
        </form>
    </main>
    <footer style="padding:20px 0;text-align:center;border-top:1px solid rgba(255,255,255,0.05);margin-top:50px;">
        <p style="color:rgba(245,245,245,0.3);font-size:13px;">© 2026 L'oro di Cicerone — Tous droits réservés</p>
    </footer>
</body>
</html>