<?php

require_once __DIR__."/../api/config.php";

verifier_connexion($role,"Client");

$a = "../data/paniers.json";
$email = $_SESSION["email"];

function calcul(array $tab_articles) : float {
    $somme = 0;
    foreach ($tab_articles as $article) {
        $somme += $article["prix"] * $article["quantite"];
    }
    return $somme;
}

function sauvegarder(string $fichier, array $data): void {
    file_put_contents($fichier, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$action = $_GET["action"] ?? '';
$id_plat = $_GET["id"] ?? '';

if (!isset($data_panier[$email])) {
    $data_panier[$email] = ["articles" => [], "total" => 0];
}

if (isset($_GET["id_cmd"])) {
    $id_cmd = $_GET["id_cmd"];
    $commande = récupérer_commande($id_cmd);

    if ($commande == null) {
        header("Location: profil_client.php?err=fetchFailed");
        exit;
    }

    $plats_catalogue = $data_plats;
    foreach ($commande["plats"] as $plat_cmd) {
        $id_plat_cmd = $plat_cmd["nom"];
        $plat = $plats_catalogue[$id_plat_cmd] ?? null;
        if ($plat !== null) { // on écrase l'ancien panier pour eviter les duplications
            $data_panier[$email]["articles"][$id_plat_cmd] = [
                "nom" => $plat["nom"],
                "prix" => $plat["prix"],
                "quantite" => $plat_cmd["quantite"]
            ];
        }
    }
    $data_panier[$email]["total"] = calcul($data_panier[$email]["articles"]);
    sauvegarder($a, $data_panier);
    header("Location: panier.php");
    exit;
}

if ($action === "set_qte" && $id_plat !== '' && isset($_GET["qte"])) {
    $qte = (int)$_GET["qte"];
    if (isset($data_panier[$email]["articles"][$id_plat])) {
        if ($qte > 0) {
            $data_panier[$email]["articles"][$id_plat]["quantite"] = $qte;
        } else {
            unset($data_panier[$email]["articles"][$id_plat]);
        }
        $data_panier[$email]["total"] = calcul($data_panier[$email]["articles"]);
        sauvegarder($a, $data_panier);
    }
    exit; 
}

if ($action === "ajouter") {
    if (isset($data_panier[$email]["articles"][$id_plat])) {
        $data_panier[$email]["articles"][$id_plat]["quantite"]++;
    } else {
        $plats = $data_plats;
        $plat = $plats[$id_plat] ?? null;
        if ($plat !== null) {
            $data_panier[$email]["articles"][$id_plat] = [
                "nom" => $plat["nom"],
                "prix" => $plat["prix"],
                "quantite" => 1
            ];
        }
    }
}

if ($action === "retirer") {
    if (isset($data_panier[$email]["articles"][$id_plat])) {
        $data_panier[$email]["articles"][$id_plat]["quantite"]--;
        if ($data_panier[$email]["articles"][$id_plat]["quantite"] <= 0) {
            unset($data_panier[$email]["articles"][$id_plat]);
        }
    }
}

if ($action === "supprimer") {
    unset($data_panier[$email]["articles"][$id_plat]);
}

if($action === "tous_supprimer") {
    $data_panier[$email]["articles"] = [];
}

if ($action !== '' && $action !== 'set_qte') {
    $pts = $_SESSION["total-fidelite"] ?? 0;
    $total_brut = calcul($data_panier[$email]["articles"]);
    $nv_total = $total_brut;

    if ($pts >= 500 && $pts < 1200) {
        $reduc = 0.15;
        $nv_total = ceil($total_brut * (1 - $reduc));
    } elseif ($pts >= 1200) {
        $reduc = 0.30;
        $nv_total = ceil($total_brut * (1 - $reduc));
    }

    $data_panier[$email]["total"] = $nv_total;
    sauvegarder($a, $data_panier);
    header("Location: panier.php");
    exit;
}

$articles = $data_panier[$email]["articles"];
$total_brut = calcul($articles);
$pts = $_SESSION["total-fidelite"] ?? 0;
$nv_total = $total_brut;

$plats_catalogue = $data_plats;

$plats_catalogue = $data_plats;

foreach ($articles as $cle => $article) { // on ajoute une clé d'ordre d'affichage pour pouvoir trier les articles du panier dans le même ordre que le catalogue
    $articles[$cle]["ordre_affichage"] = $plats_catalogue[$cle]["ordre_affichage"]; 
}

uasort($articles, function ($a, $b) { // tri selon l'ordre d'affichage du catalogue
    return $a["ordre_affichage"] <=> $b["ordre_affichage"]; // on utilise le spaceship operator pour comparer les valeurs d'ordre d'affichage 
});

if ($pts >= 500 && $pts < 1200) {
    $reduc = 0.15;
    $nv_total = ceil($total_brut * (1 - $reduc));
} elseif ($pts >= 1200) {
    $reduc = 0.30;
    $nv_total = ceil($total_brut * (1 - $reduc));
}

$minDateTime = date("Y-m-d\TH:i");
?>
<!DOCTYPE html>
<html lang="<?php if ($isFrench) echo "fr"; else echo "en"; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $text["panier"]["title"] ?></title>
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/panier.css">
    <script src="../javascript/panier.js" defer></script>
</head>
<body>
    <header>
        <a href="index.php"><h1>L'oro di Cicerone</h1></a>
        <nav>
            <ul>
                <li><a href="index.php"><?php if ($isFrench) echo "Accueil"; else echo "Home page"; ?></a></li>
                <li><a href="presentation.php">Menu</a></li>
                <li><a href="profil_client.php"><?= $text["panier"]["nav_profile"] ?></a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h2><?= $text["panier"]["page_title"] ?></h2>
        <?php if (count($articles) === 0){ ?>
            <p><?= $text["panier"]["empty_cart"] ?></p>
            <a href="presentation.php"><?= $text["panier"]["see_menu"] ?></a>
        <?php } else { ?>
            <section class="rectangle">
                <ul>
                    <?php foreach ($articles as $cle => $article){ ?>
                    <li class="mc-item"
                        data-prix="<?= (int)$article["prix"] ?>"
                        data-cle="<?= htmlspecialchars($cle) ?>">
                        <div class="ligne">
                            <span class="nom"><?= htmlspecialchars($article["nom"]) ?></span>
                            <span class="prix mc-item-subtotal"><?= $article["prix"] * $article["quantite"] ?>€</span>
                        </div>
                        <div class="quantite">
                            <button type="button" class="btn-qte" onclick="modifierQte(this, -1)">-</button>
                            <span class="qte-nb"><?= $article["quantite"] ?></span>
                            <button type="button" class="btn-qte" onclick="modifierQte(this, 1)">+</button>
                            <span class="unitaire"><?= $article["prix"] ?>€ / <?= $text["panier"]["unit"] ?></span>
                            <a class="btn-supp" href="panier.php?action=supprimer&id=<?= urlencode($cle) ?>"><?= $text["panier"]["delete"] ?></a>
                        </div>
                    </li>
                    <?php } ?>
                </ul>
                <div class="container-ts-supp">
                    <a class="btn-ts-supp" href="panier.php?action=tous_supprimer"><?= $text["panier"]["delete_all"] ?></a>
                </div>
            </section>
            <div class="total" id="bloc-total" data-reduc="<?= isset($reduc) ? $reduc : 0 ?>">
                <span><?= $text["panier"]["total"] ?></span>
                <span id="display-total"><?= number_format($total_brut, 2, '.', ',') ?>€</span>
            </div>
            <?php if ($total_brut == $nv_total){ ?>
                <p id="p-pas-reduc"><?= $text["panier"]["no_discount"] ?></p>
            <?php } else { ?>
                <div id="bloc-remise" style="text-align:right">
                    <span><?= $text["panier"]["instant_discount"] ?> </span>
                    <span><?= $reduc * 100 ?>%</span>
                </div>
                <div class="total" id="bloc-total-reduit">
                    <span><?= $text["panier"]["total_after_discount"] ?></span>
                    <span id="display-total-reduit"><?= number_format($nv_total,2, '.', ',') ?>€</span>
                </div>
            <?php } ?>
            <form method="POST" action="paiement.php">
                <div class="form-groupe">
                    <label for="instructions"><?= $text["panier"]["special_instructions"] ?></label>
                    <textarea name="instructions" id="instructions" maxlength="500"
                        placeholder="<?= $text["panier"]["instructions_placeholder"] ?>"><?= htmlspecialchars($_POST["instructions"] ?? "") ?></textarea>
                    <span id="compteur-instructions"
                          style="font-size:11px; color:rgba(255,255,255,0.4); float:right;">0 / 500</span>
                </div>
                <div class="form-groupe">
                    <label><?= $text["panier"]["order_type"] ?></label>
                    <div class="radio-groupe">
                        <label class="radio-label">
                            <input type="radio" name="type_commande" value="sur_place" checked> <?= $text["panier"]["eat_in"] ?>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="type_commande" value="livraison"> <?= $text["panier"]["delivery"] ?>
                        </label>
                    </div>
                </div>
                <div class="form-groupe">
                    <label for="date_livraison"> <?= $text["panier"]["delivery_datetime"] ?>
                        <span class="label-hint"><?= $text["panier"]["delivery_hint"] ?></span>
                    </label>
                    <input type="datetime-local" name="date_livraison" id="date_livraison" min="<?= $minDateTime ?>">
                </div>
                <div class="action">
                    <a href="presentation.php"><?= $text["panier"]["continue_shopping"] ?></a>
                    <button type="submit"><?= $text["panier"]["validate_cart"] ?></button>
                </div>
            </form>
        <?php } ?>
    </main>
    <footer>
        <p><?= $text["index"]["footer_rights"] ?></p>
        <a href="contact.php"><?= $text["index"]["footer_contact"] ?></a><span> |</span>
        <a href="condition_generale.php"><?= $text["index"]["footer_privacy"] ?></a>
    </footer>
</body>
</html>