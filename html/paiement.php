<?php
require('getapikey.php');
require_once __DIR__."/../api/config.php";

verifier_connexion($role, "Client");
$email = $_SESSION["email"];

function lire_json(string $chemin): array { // Fonction pour lire un fichier JSON et retourner son contenu sous forme de tableau associatif
    if (!file_exists($chemin)) return [];
    return json_decode(file_get_contents($chemin), true) ?? [];
}

$catalogue_plats = lire_json("../data/plats.json");

$is_modification = (isset($_GET['action']) && $_GET['action'] === 'modification' && isset($_SESSION['modif_commande'])); // Vérifie si l'utilisateur arrive sur cette page dans le cadre d'une modification de commande (après avoir cliqué sur "Confirmer les modifications" dans modifier_commande.php) ou s'il s'agit d'un paiement classique depuis le panier

if ($is_modification) { // Cas d'une modification de commande : les données à afficher sont récupérées depuis la session (stockées temporairement par modifier_commande.php)
    $data_modif = $_SESSION['modif_commande'];
    $id_cmd = $data_modif["id_cle"];
    $montant = $data_modif["reste_a_payer"];
    $transaction = substr("MOD" . bin2hex(random_bytes(8)), 0, 15);
    $type_label = $text["paiement"]["modification_label"];
    $articles_a_afficher = $data_modif["plats"];
    $id_retour = $id_cmd;
} else { // Cas d'un paiement classique depuis le panier : les données à afficher sont récupérées depuis le panier du client
    $tous_paniers = lire_json("../data/paniers.json");
    $panier = $tous_paniers[$email] ?? ["articles" => [], "total" => 0];

    if (count($panier["articles"]) === 0) { // Si le panier est vide, on redirige vers la page du panier
        header("Location: panier.php");
        exit();
    }

    $form_data = $_SESSION["panier_form"] ?? []; // Récupère les données du formulaire de commande (type de commande, instructions) depuis la session, si elles existent (stockées temporairement par panier.php)
    unset($_SESSION["panier_form"]);
    $type_raw = $_POST["type_commande"] ?? $form_data["type_commande"] ?? "sur_place";

    if ($type_raw === "sur_place") {
        $type_label = $text["paiement"]["type_sur_place"];
    }
    elseif ($type_raw === "livraison") {
        $type_label = $text["paiement"]["type_livraison"];
    }
    elseif ($type_raw === "a_emporter") {
        $type_label = $text["paiement"]["type_a_emporter"];
    }
    else {
        $type_label = ucfirst(str_replace('_', ' ', $type_raw));
    }

    $total_brut_panier = 0;
    foreach ($panier["articles"] as $art) { // Calcule le total brut du panier
        $total_brut_panier += $art["prix"] * $art["quantite"];
    }

    $pts_client = $_SESSION["total-fidelite"] ?? 0; // Récupère le nombre de points de fidélité du client pour appliquer une éventuelle réduction
    if ($pts_client >= 1200){ 
        $montant = ceil($total_brut_panier * 0.70);
    }
    elseif ($pts_client >= 500) { 
        $montant = ceil($total_brut_panier * 0.85);
    }
    else {
        $montant = $total_brut_panier;
    } 

    $transaction = substr(bin2hex(random_bytes(10)), 0, 15); // Génère un identifiant de transaction unique pour le paiement
    $articles_a_afficher = $panier["articles"]; // Articles qui seront affichés dans le récapitulatif
    $plats_enrichis = [];
    foreach ($articles_a_afficher as $art) {
        $art["nom_eng"] = $catalogue_plats[$art["nom"]]["nom_eng"] ?? $art["nom"];
        $plats_enrichis[] = $art;
    }
 
    $_SESSION["commande_en_attente"] = [ // Stocke temporairement les données de la commande en session pour les récupérer après le paiement dans retour_paiement.php
        "email" => $email,
        "date" => date("Y-m-d H:i:s"),
        "montant" => number_format((float)$montant, 2, '.', ''),
        "livraison" => ($type_raw === "livraison"),
        "est-valide" => false,
        "etat" => "en_attente",
        "plats" => $plats_enrichis,
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
<html lang="<?php if ($isFrench) echo "fr"; else echo "en"; ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $text["paiement"]["title"] ?></title>
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/paiement.css">
</head>
<body>
    <header>
        <a href="index.php"><h1>L'oro di Cicerone</h1></a>
    </header>
    <main>
        <h2><?= $text["paiement"]["summary_title"] ?></h2>
        <div class="recap">
            <table>
                <thead>
                    <tr>
                        <th><?= $text["paiement"]["dish"] ?></th>
                        <th><?= $text["paiement"]["quantity"] ?></th>
                        <th style="text-align:right;"><?= $text["paiement"]["subtotal"] ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($articles_a_afficher)) { // Affiche les articles du panier ou de la commande modifiée
                        foreach ($articles_a_afficher as $art) {
                            $nom = $art["nom"];
                            $nom_affiche = $isFrench ? $nom : ($art["nom_eng"] ?? $nom);
                            $qte = $art["quantite"];
                            $prix_u = $catalogue_plats[$nom]["prix"] ?? 0;
                            $st = $prix_u * $qte;
                    ?>
                        <tr>
                            <td class="nom"><?= htmlspecialchars($nom_affiche) ?></td>
                            <td class="qte">x <?= $qte ?></td>
                            <td class="sous-total"><?= $st ?>€</td>
                        </tr>
                    <?php }
                    } else {
                        echo "<tr><td colspan='3' style='text-align:center;padding:20px;'>" . $text["paiement"]["no_article"] . "</td></tr>";
                    } ?>
                </tbody>
            </table>
        </div>
        <div class="infos-commande">
            <div class="info-ligne">
                <span class="label"><?= $text["paiement"]["type"] ?></span>
                <span class="valeur"><?= htmlspecialchars($type_label) ?></span>
            </div>
        </div>
        <div class="total">
            <span><?= $text["paiement"]["total_to_pay"] ?></span>
            <span class="montant"><?= $montant_fmt ?>€</span>
        </div>
        <form action="https://www.plateforme-smc.fr/cybank/index.php" method="POST">
            <input type="hidden" name="transaction" value="<?= $transaction ?>">
            <input type="hidden" name="montant" value="<?= $montant_fmt ?>">
            <input type="hidden" name="vendeur" value="<?= $vendeur ?>">
            <input type="hidden" name="retour" value="<?= $retour ?>">
            <input type="hidden" name="control" value="<?= $control ?>">
            <div class="action">
                <a href="<?= $is_modification ? 'modifier_commande.php?id=' . htmlspecialchars($id_retour) : 'panier.php' ?>"><?= $text["paiement"]["back"] ?></a>
                <button type="submit"><?= $text["paiement"]["pay_button"] ?></button>
            </div>
        </form>
    </main>
    <footer>
        <p><?= $text["index"]["footer_rights"] ?></p>
        <a href="contact.php"><?= $text["index"]["footer_contact"] ?></a><span> |</span>
        <a href="condition_generale.php"><?= $text["index"]["footer_privacy"] ?></a>
    </footer>
</body>
</html>