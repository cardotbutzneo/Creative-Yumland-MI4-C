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

if (isset($_GET["id_cmd"])){
    $id_cmd = $_GET["id_cmd"];
    $commande = récupérer_commande($id_cmd);

    if ($commande == null){
        header("Location: profil_client.php?err=fetchFailed");
        exit;
    }
    
    $total_panier = 0;
    foreach ($commande["plats"] as $i => $plat_cmd) {
        $id_plat = $commande["plats"][$i]["nom"];
        $plat = $commande["plats"][$i];
        if (isset($paniers[$email]["articles"][$id_plat])) {
            $paniers[$email]["articles"][$id_plat]["quantite"]++;
        } else {
            $plats = lire_data("../data/plats.json");
            $plat  = $plats[$id_plat] ?? null;
            if ($plat !== null) {
                $paniers[$email]["articles"][$id_plat] = [
                    "nom" => $plat["nom"],
                    "prix" => $plat["prix"],
                    "quantite" => 1
                ];
            }
        }
        $total_panier += $plat["prix"] * $plat_cmd["quantite"];
    }
    $paniers[$email]["total"] = $total_panier;
    sauvegarder($a, $paniers);
}

if (!isset($paniers[$email])) {
    $paniers[$email] = ["articles" => [], "total" => 0];
}

if ($action === "ajouter") {
    if (isset($paniers[$email]["articles"][$id_plat])) {
        $paniers[$email]["articles"][$id_plat]["quantite"]++;
    } else {
        $plats = lire_data("../data/plats.json");
        $plat  = $plats[$id_plat] ?? null;
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

if ($action !== '') {
    $pts = $_SESSION["total-fidelite"] ?? 0;
    $total_brut = calcul($paniers[$email]["articles"]);
    $nv_total = $total_brut;
    
    if ($pts >= 500 && $pts < 1200) {
        $reduc = 0.15;
        $nv_total = ceil($total_brut*(1-$reduc));
    } elseif ($pts >= 1200) {
        $reduc = 0.3;
        $nv_total = ceil($total_brut*(1-$reduc));
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
    $nv_total = ceil($total_brut*(1-$reduc));
} elseif ($pts >= 1200) {
    $reduc = 0.3;
    $nv_total = ceil($total_brut*(1-$reduc));
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
        <?php
        if (count($articles) === 0) {
            echo "<p>Votre panier est vide.</p>";
            echo "<a href='presentation.php'>Voir la carte</a>";
        } else {
            echo "<section class='rectangle'>";
            echo "<ul>";
            foreach ($articles as $cle => $article) {
                echo "<li>";
                echo "<div class='ligne'>";
                echo "<span class='nom'>" . $article["nom"] . "</span>";
                echo "<span class='prix'>" . ($article["prix"] * $article["quantite"]) . "€</span>";
                echo "</div>";
                echo "<div class='quantite'>";
                echo "<a href='panier.php?action=retirer&id=$cle'>-</a>";
                echo "<span>" . $article["quantite"] . "</span>";
                echo "<a href='panier.php?action=ajouter&id=$cle'>+</a>";
                echo "<span class='unitaire'>" . $article["prix"] . "€ / unité</span>";
                echo "<a class='btn-supp' href='panier.php?action=supprimer&id=$cle'>Supprimer</a>";
                echo "</div>";
                echo "</li>";
            }
            echo "</ul>";
            echo "</section>";

            echo "<div class='total'>";
            echo "<span>Total</span>";
            echo "<span>" . $total_brut . "€</span>";
            echo "</div>";

            if ($total_brut == $nv_total) {
                echo "<p>Pas de réduction disponible</p>";
            } else {
                echo "<div style='text-align : right'>";
                echo "<span>Remise immédiate : </span>";
                echo "<span> " . ($reduc*100) . "%</span>";
                echo "</div>";
                echo "<div class='total'>";
                echo "<span>Total après réductions</span>";
                echo "<span>" . $nv_total . "€</span>";
                echo "</div>";
            }
            echo "<form method='POST' action='paiement.php'>";
            echo "<div class='form-groupe'>";
            echo "<label for='instructions'>Instructions spéciales :</label>";
            echo "<textarea name='instructions' id='instructions' placeholder='Ex : pizza sans olives, allergie aux noix...'>" . htmlspecialchars($_POST["instructions"] ?? "") . "</textarea>";
            echo "</div>";
            echo "<div class='form-groupe'>";
            echo "<label>Type de commande :</label>";
            echo "<div class='radio-groupe'>";
            echo "<label class='radio-label'><input type='radio' name='type_commande' value='sur_place' checked> Sur place</label>";
            echo "<label class='radio-label'><input type='radio' name='type_commande' value='livraison'> Livraison</label>";
            echo "</div>";
            echo "</div>";
            echo "<div class='form-groupe'>";
            echo "<label for='date_livraison'>Date et heure de livraison <span class='label-hint'>(laisser vide pour une livraison immédiate)</span></label>";
            echo "<input type='datetime-local' name='date_livraison' id='date_livraison' min='".$minDateTime."'>";            echo "</div>";
            echo "<div class='action'>";
            echo "<a href='presentation.php'>Continuer mes achats</a>";
            echo "<button type='submit'>Valider mon panier</button>";
            echo "</div>";
            echo "</form>";
        }
        ?>
    </main>
    <footer>
        <p>© 2026 L'oro di Cicerone — Tous droits réservés</p>
        <a href="contact.php">Nous contacter</a>
    </footer>
</body>
</html>