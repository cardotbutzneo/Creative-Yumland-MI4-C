<?php

require_once __DIR__."/../api/config.php";

verifier_connexion($role,"Client");

$email = $_SESSION["email"];
$a = "../data/paniers.json";

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
$paniers = lire_data($a);

if (!isset($paniers[$email])) {
    $paniers[$email] = ["articles" => [], "total" => 0];
}

if (isset($_GET["id_cmd"])) {
    $id_cmd = $_GET["id_cmd"];
    $commande = récupérer_commande($id_cmd);

    if ($commande == null) {
        header("Location: profil_client.php?err=fetchFailed");
        exit;
    }

    $plats_catalogue = lire_data("../data/plats.json");
    foreach ($commande["plats"] as $plat_cmd) {
        $id_plat_cmd = $plat_cmd["nom"];
        if (isset($paniers[$email]["articles"][$id_plat_cmd])) {
            $paniers[$email]["articles"][$id_plat_cmd]["quantite"] += $plat_cmd["quantite"];
        } else {
            $plat = $plats_catalogue[$id_plat_cmd] ?? null;
            if ($plat !== null) {
                $paniers[$email]["articles"][$id_plat_cmd] = [
                    "nom" => $plat["nom"],
                    "prix" => $plat["prix"],
                    "quantite" => $plat_cmd["quantite"]
                ];
            }
        }
    }
    $paniers[$email]["total"] = calcul($paniers[$email]["articles"]);
    sauvegarder($a, $paniers);
    header("Location: panier.php");
    exit;
}

if ($action === "set_qte" && $id_plat !== '' && isset($_GET["qte"])) {
    $qte = (int)$_GET["qte"];
    if (isset($paniers[$email]["articles"][$id_plat])) {
        if ($qte > 0) {
            $paniers[$email]["articles"][$id_plat]["quantite"] = $qte;
        } else {
            unset($paniers[$email]["articles"][$id_plat]);
        }
        $paniers[$email]["total"] = calcul($paniers[$email]["articles"]);
        sauvegarder($a, $paniers);
    }
    exit; 
}

if ($action === "ajouter") {
    if (isset($paniers[$email]["articles"][$id_plat])) {
        $paniers[$email]["articles"][$id_plat]["quantite"]++;
    } else {
        $plats = lire_data("../data/plats.json");
        $plat = $plats[$id_plat] ?? null;
        if ($plat !== null) {
            $paniers[$email]["articles"][$id_plat] = [
                "nom" => $plat["nom"],
                "prix" => $plat["prix"],
                "quantite" => 1
            ];
        }
    }
}

if ($action === "retirer") {
    if (isset($paniers[$email]["articles"][$id_plat])) {
        $paniers[$email]["articles"][$id_plat]["quantite"]--;
        if ($paniers[$email]["articles"][$id_plat]["quantite"] <= 0) {
            unset($paniers[$email]["articles"][$id_plat]);
        }
    }
}

if ($action === "supprimer") {
    unset($paniers[$email]["articles"][$id_plat]);
}

if($action === "tous_supprimer") {
    $paniers[$email]["articles"] = [];
}

if ($action !== '' && $action !== 'set_qte') {
    $pts = $_SESSION["total-fidelite"] ?? 0;
    $total_brut = calcul($paniers[$email]["articles"]);
    $nv_total = $total_brut;

    if ($pts >= 500 && $pts < 1200) {
        $reduc = 0.15;
        $nv_total = ceil($total_brut * (1 - $reduc));
    } elseif ($pts >= 1200) {
        $reduc = 0.30;
        $nv_total = ceil($total_brut * (1 - $reduc));
    }

    $paniers[$email]["total"] = $nv_total;
    sauvegarder($a, $paniers);
    header("Location: panier.php");
    exit;
}

$articles = $paniers[$email]["articles"];
$total_brut = calcul($articles);
$pts = $_SESSION["total-fidelite"] ?? 0;
$nv_total = $total_brut;

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
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon panier - L'oro di Cicerone</title>
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/panier.css">
    <script src="../javascript/panier.js" defer></script>
</head>
<body>
    <header>
        <a href="index.php"><h1>L'oro di Cicerone</h1></a>
        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="presentation.php">Menu</a></li>
                <li><a href="profil_client.php">Profil</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h2>Mon panier</h2>
        <?php if (count($articles) === 0){ ?>
            <p>Votre panier est vide.</p>
            <a href="presentation.php">Voir la carte</a>
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
                            <span class="unitaire"><?= $article["prix"] ?>€ / unité</span>
                            <a class="btn-supp" href="panier.php?action=supprimer&id=<?= urlencode($cle) ?>">Supprimer</a>
                        </div>
                    </li>
                    <?php } ?>
                </ul>
                <div class="container-ts-supp">
                    <a class="btn-ts-supp" href="panier.php?action=tous_supprimer">Tout supprimer</a>
                </div>
            </section>
            <div class="total" id="bloc-total" data-reduc="<?= isset($reduc) ? $reduc : 0 ?>">
                <span>Total</span>
                <span id="display-total"><?= $total_brut ?>€</span>
            </div>
            <?php if ($total_brut == $nv_total){ ?>
                <p id="p-pas-reduc">Pas de réduction disponible</p>
            <?php } else { ?>
                <div id="bloc-remise" style="text-align:right">
                    <span>Remise immédiate : </span>
                    <span><?= $reduc * 100 ?>%</span>
                </div>
                <div class="total" id="bloc-total-reduit">
                    <span>Total après réductions</span>
                    <span id="display-total-reduit"><?= $nv_total ?>€</span>
                </div>
            <?php } ?>
            <form method="POST" action="paiement.php">
                <div class="form-groupe">
                    <label for="instructions">Instructions spéciales :</label>
                    <textarea name="instructions" id="instructions" maxlength="500"
                        placeholder="Ex : pizza sans olives, ..."><?= htmlspecialchars($_POST["instructions"] ?? "") ?></textarea>
                    <span id="compteur-instructions"
                          style="font-size:11px; color:rgba(255,255,255,0.4); float:right;">0 / 500</span>
                </div>
                <div class="form-groupe">
                    <label>Type de commande :</label>
                    <div class="radio-groupe">
                        <label class="radio-label">
                            <input type="radio" name="type_commande" value="sur_place" checked> Sur place
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="type_commande" value="livraison"> Livraison
                        </label>
                    </div>
                </div>
                <div class="form-groupe">
                    <label for="date_livraison"> Date et heure de livraison
                        <span class="label-hint">(laisser vide pour une livraison immédiate)</span>
                    </label>
                    <input type="datetime-local" name="date_livraison" id="date_livraison" min="<?= $minDateTime ?>">
                </div>
                <div class="action">
                    <a href="presentation.php">Continuer mes achats</a>
                    <button type="submit">Valider mon panier</button>
                </div>
            </form>
        <?php } ?>
    </main>
    <footer>
        <p>© 2026 L'oro di Cicerone — Tous droits réservés</p>
        <a href="contact.php">Nous contacter</a>
    </footer>
</body>
</html>