<?php
require_once __DIR__."/../api/config.php";

$est_client = false;

if (isset($_SESSION["connecte"]) && $_SESSION["connecte"] === true && $_SESSION["role"] === "Client") {
    $est_client = true;
}

$categories = [ // Labels des catégories de plats pour l'affichage
    "entrees" => $text["presentation"]["category_starters"],
    "plats" => $text["presentation"]["category_dishes"],
    "desserts" => $text["presentation"]["category_desserts"],
    "vins" => $text["presentation"]["category_wines"],
    "cafes" => $text["presentation"]["category_coffees"],
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
<html lang="<?php if ($isFrench) echo "fr"; else echo "en"; ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $text["presentation"]["title"] ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/presentation.css">
    <script>
        const est_client = <?php if ($est_client) { echo 'true'; } else { echo 'false'; } ?>;
        const is_french = <?= $isFrench ? 'true' : 'false' ?>;
        const category_labels = {
            entrees: "<?= $text['presentation']['category_starters'] ?>",
            plats: "<?= $text['presentation']['category_dishes'] ?>",
            desserts: "<?= $text['presentation']['category_desserts'] ?>",
            vins: "<?= $text['presentation']['category_wines'] ?>",
            cafes: "<?= $text['presentation']['category_coffees'] ?>",
        };    
    </script>
    <script src="../javascript/presentation.js" defer></script>
</head>
<body>

<header>
    <a href="index.php"><h1>L'oro di Cicerone</h1></a>
    <nav>
        <ul>
            <li><a href="index.php"><?php if ($isFrench) echo "Accueil"; else echo "Home page"; ?></a></li>
            <li><a href="restaurant.php"><?php if ($isFrench) echo "Le Restaurant"; else echo "The restaurant"; ?></a></li>
            <li><a href="chef.php"><?php if ($isFrench) echo "Le Chef"; else echo "The chef"; ?></a></li>
            <?php
            if ($est_client) {
                echo "<li><a href='panier.php'>" . $text["presentation"]["nav_cart"] . "</a></li>";
                echo "<li><a href='connexion.php'>" . $text["presentation"]["nav_profile"] . "</a></li>";
            } else {
                echo "<li><a href='connexion.php'>" . $text["presentation"]["nav_login"] . "</a></li>";
            }
            ?>
        </ul>
    </nav>
</header>
<main>
    <div class="barre-filtre">
        <input type="text" id="bar-recherche" placeholder="<?= $text["presentation"]["search_placeholder"] ?>">
        <select id="filtre-carte">
            <option value=""><?= $text["presentation"]["filter_all_menu"] ?></option>
            <option value="entrees"><?= $text["presentation"]["category_starters"] ?></option>
            <option value="plats"><?= $text["presentation"]["category_dishes"] ?></option>
            <option value="desserts"><?= $text["presentation"]["category_desserts"] ?></option>
            <option value="vins"><?= $text["presentation"]["category_wines"] ?></option>
            <option value="cafes"><?= $text["presentation"]["category_coffees"] ?></option>
        </select>
        <select id="filtre-regime">
            <option value=""><?= $text["presentation"]["filter_all_diets"] ?></option>
            <option value="vege"><?= $text["presentation"]["diet_vegetarian"] ?></option>
            <option value="non-vege"><?= $text["presentation"]["diet_non_vegetarian"] ?></option>
        </select>
        <select id="filtre-allergenes">
            <option value=""><?= $text["presentation"]["filter_all_allergens"] ?></option>
            <option value="50"><?= $text["presentation"]["without_gluten"] ?></option>
            <option value="51"><?= $text["presentation"]["without_shellfish"] ?></option>
            <option value="52"><?= $text["presentation"]["without_eggs"] ?></option>
            <option value="53"><?= $text["presentation"]["without_lactose"] ?></option>
            <option value="54"><?= $text["presentation"]["without_nuts"] ?></option>
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
            $desc = (!$isFrench) ? $plat['description_eng'] : $plat['description'];
            echo "<span class='description'>$desc</span>";
            if ($est_client) {
                echo "<div><a href='panier.php?action=ajouter&id=" . urlencode($cle) . "' class='btn-ajouter'>" . $text["presentation"]["add_button"] . "</a></div>";
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