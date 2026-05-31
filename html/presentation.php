<?php
require_once __DIR__."/../api/config.php";

$est_client = false;

if (isset($_SESSION["connecte"]) && $_SESSION["connecte"] === true && $_SESSION["role"] === "Client") {
    $est_client = true;
}

$categories = [ // Labels des catégories de plats pour l'affichage
    "entrees" => "Entrées",
    "plats" => "Plats",
    "desserts" => "Desserts",
    "vins" => "Vins",
    "cafes" => "Cafés",
];

if (isset($_GET['ajax'])) {  // Traite la requête AJAX pour le filtrage de la carte
    header('Content-Type: application/json');
    $data = $data_plats;
    // Récupération des filtres envoyés par JavaScript
    $categorie = $_GET['categorie'] ?? '';
    $regime = $_GET['regime'] ?? '';
    $allergene = $_GET['allergene'] ?? '';
    $recherche = strtolower($_GET['recherche'] ?? '');
    $tab = [];
    foreach ($data as $cle => $plat) { // Application des filtres : on vérifie pour chaque plat s'il correspond aux critères sélectionnés (catégorie, régime, allergène, recherche textuelle) et on ne l'ajoute au résultat que s'il les respecte tous
        if ($cle === "Allergenes") continue;
        if ($categorie !== '' && $plat['categorie'] !== $categorie) continue;
        if ($regime === 'vege' && empty($plat['est_vegetarien'])) continue;
        if ($regime === 'non-vege' && !empty($plat['est_vegetarien'])) continue;
        if ($allergene !== '' && in_array((int)$allergene, $plat['allergene_id'] ?? [])) continue;
        if ($recherche !== '' && strpos(strtolower($plat['nom']), $recherche) === false) continue;
        $tab[$cle] = $plat;
    }
    echo json_encode($tab, JSON_UNESCAPED_UNICODE); // Retourne les résultats filtrés au format JSON
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
    foreach ($categories as $id => $label) { // Affichage de la carte
        $platscat = [];
        foreach ($data as $cle => $plat) { // Sélectionne uniquement les plats appartenant à la catégorie courante
            if ($cle === "Allergenes") continue;
            if ($plat["categorie"] === $id) {
                $platscat[$cle] = $plat;
            }
        }
        if (empty($platscat)) continue; // Si aucun plat dans cette catégorie, on affiche rien
        echo "<section class='rectangle'>";
        echo "<h2>$label</h2><ul>";
        foreach ($platscat as $cle => $plat) { // Affiche les plats de la catégorie courante
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
    <p><?= $text["index"]["footer_rights"] ?></p>
    <a href="contact.php"><?= $text["index"]["footer_contact"] ?></a><span> |</span>
    <a href="condition_generale.php"><?= $text["index"]["footer_privacy"] ?></a>
</footer>
</body>
</html>