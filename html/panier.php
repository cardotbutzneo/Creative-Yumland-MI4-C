<?php
session_start();
if(!isset($_SESSION["connecte"])){
    header("Location: connexion.php?retour=panier.php");
    exit;
}

require_once __DIR__."/../serveur.php";

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
$id_plat = $_GET["id"]     ?? '';
$paniers = lire_data($a);
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
                "nom"      => $plat["nom"],
                "prix"     => $plat["prix"],
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
    $paniers[$email]["total"] = calcul($paniers[$email]["articles"]);
    sauvegarder($a, $paniers);
    header("Location: panier.php");
    exit;
}

$pts = $_SESSION["total-fidelite"] ?? 0;

$panier = $paniers[$email];
$articles = $panier["articles"];
$total = $panier["total"];
$nb_articles = array_sum(array_column($articles, "quantite"));

$nv_total = $total;
if ($pts >= 500 and $pts < 1200) {
    $reduc = 0.15; // 15 % de reduc
    $nv_total = ceil($total*(1-$reduc));
}
elseif ($pts > 1200) {
    $reduc = 0.3; // 30 % de reduc
    $nv_total = ceil($total*(1-$reduc));
}

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
                echo "<span class='prix'>" . $article["prix"] * $article["quantite"] . "€</span>";
                echo "</div>";
                echo "<div class='quantite'>";
                echo "<a href='panier.php?action=retirer&id=" . $cle . "'>-</a>";
                echo "<span>" . $article["quantite"] . "</span>";
                echo "<a href='panier.php?action=ajouter&id=" . $cle . "'>+</a>";
                echo "<span class='unitaire'>" . $article["prix"] . "€ / unité</span>";
                echo "<a class='btn-supp' href='panier.php?action=supprimer&id=" . $cle . "'>Supprimer</a>"; 
                echo "</div>";
                echo "</li>";
            }
            echo "</ul>";
            echo "</section>";
            echo "<div class='total'>";
            echo "<span>Total</span>";
            echo "<span>" . $total . "€</span>";
            echo "</div>";
            if ($total == $nv_total) echo "<p>Pas de réduction disponible</p>";
            else{
                echo "<div class='total'>";
                echo "<span>Total après réductions</span>";
                echo "<span>" . $nv_total . "€</span>";
                echo "</div>";
            }
            echo "<div class='action'>";
            echo "<a href='presentation.php'>Continuer mes achats</a>";
            echo "<a href='paiement.php'>Valider mon panier</a>";
            echo "</div>";
        }
        ?>
    </main>
    <footer>
        <p>© 2026 L'oro di Cicerone — Tous droits réservés</p>
        <a href="contact.php">Nous contacter</a>
    </footer>
</body>
</html>
</html>