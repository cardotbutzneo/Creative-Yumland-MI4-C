<?php
require_once __DIR__."/../api/config.php";

$est_client = false;

if (isset($_SESSION["connecte"]) && $_SESSION["connecte"] === true && $_SESSION["role"] === "Client") {
    $est_client = true;
}

$categories = [
    "entrees" => "Entrées",
    "plats" => "Plats",
    "desserts" => "Desserts",
    "vins" => "Vins",
    "cafes" => "Cafés",
];

if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $data = $data_plats;
    $categorie = $_GET['categorie'] ?? '';
    $regime = $_GET['regime'] ?? '';
    $allergene = $_GET['allergene'] ?? '';
    $recherche = strtolower($_GET['recherche'] ?? '');
    $tab = [];
    foreach ($data as $cle => $plat) {
        if ($cle === "Allergenes") continue;
        if ($categorie !== '' && $plat['categorie'] !== $categorie) continue;
        if ($regime === 'vege' && empty($plat['est_vegetarien'])) continue;
        if ($regime === 'non-vege' && !empty($plat['est_vegetarien'])) continue;
        if ($allergene !== '' && in_array((int)$allergene, $plat['allergene_id'] ?? [])) continue;
        if ($recherche !== '' && strpos(strtolower($plat['nom']), $recherche) === false) continue;
        $tab[$cle] = $plat;
    }
    echo json_encode($tab, JSON_UNESCAPED_UNICODE);
    exit;
}

$data = $data_plats;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>La Carte - L'oro di Cicerone</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/presentation.css">
    <script>
        const est_client = <?php if ($est_client) { echo 'true'; } else { echo 'false'; } ?>;
    </script>
    <script src="../javascript/presentation.js" defer></script>
</head>
<body>

<header>
    <a href="index.php"><h1>L'oro di Cicerone</h1></a>
    <nav>
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="restaurant.php">Le Restaurant</a></li>
            <li><a href="chef.php">Le Chef</a></li>
            <?php
            if ($est_client) {
                echo "<li><a href='panier.php'>Panier</a></li>";
                echo "<li><a href='connexion.php'>Profil</a></li>";
            } else {
                echo "<li><a href='connexion.php'>Se connecter</a></li>";
            }
            ?>
        </ul>
    </nav>
</header>
<main>
    <div class="barre-filtre">
        <input type="text" id="bar-recherche" placeholder="Rechercher un plat">
        <select id="filtre-carte">
            <option value="">Toute la carte</option>
            <option value="entrees">Entrées</option>
            <option value="plats">Plats</option>
            <option value="desserts">Desserts</option>
            <option value="vins">Vins</option>
            <option value="cafes">Cafés</option>
        </select>
        <select id="filtre-regime">
            <option value="">Tous les régimes</option>
            <option value="vege">Végétarien</option>
            <option value="non-vege">Non végétarien</option>
        </select>
        <select id="filtre-allergenes">
            <option value="">Tous</option>
            <option value="50">Sans gluten</option>
            <option value="51">Sans crustacés</option>
            <option value="52">Sans oeufs</option>
            <option value="53">Sans lactose</option>
            <option value="54">Sans fruits à coque</option>
        </select>
    </div>
    <div id="liste-plats">
    <?php
    foreach ($categories as $id => $label) {
        $platscat = [];
        foreach ($data as $cle => $plat) {
            if ($cle === "Allergenes") continue;
            if ($plat["categorie"] === $id) {
                $platscat[$cle] = $plat;
            }
        }
        if (empty($platscat)) continue;
        echo "<section class='rectangle'>";
        echo "<h2>$label</h2><ul>";
        foreach ($platscat as $cle => $plat) {
            echo "<li>";
            echo "<div class='ligne'>";
            echo "<span class='nom'>{$plat['nom']}</span>";
            echo "<span class='prix'>{$plat['prix']}€</span>";
            echo "</div>";
            echo "<span class='description'>{$plat['description']}</span>";
            if ($est_client) {
                echo "<div><a href='panier.php?action=ajouter&id=" . urlencode($cle) . "' class='btn-ajouter'>+ Ajouter</a></div>";
            }
            echo "</li>";
        }
        echo "</ul></section>";
    }
    ?>
    </div>
</main>
<footer>
    <p>© 2026 L'oro di Cicerone — Tous droits réservés</p>
    <a href="contact.php">Nous contacter</a>
</footer>
</body>
</html>