<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>La Carte – L'oro di Cicerone</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/presentation.css">
</head>
<body>
<header>
    <a href="index.php"><h1>L'oro di Cicerone</h1></a>
    <nav>
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="restaurant.php">Le Restaurant</a></li>
            <li><a href="chef.php">Le Chef</a></li>
            <li><a href="presentation.php">Menu</a></li>
            <li>
                <?php 
                if (isset($_SESSION) && $_SESSION["connecte"] === true) {
                    echo '<a href="connexion.php">Profil</a>';
                } else {
                    echo '<a href="connexion.php">Se connecter</a>';
                }
                ?>
            </li>
        </ul>
    </nav>
</header>
<main>
<?php
function lire_data(string $chemin, string $nom_utilisateur = ""): array {
    if (!file_exists($chemin)) return [];
    $data = json_decode(file_get_contents($chemin), true);
    if ($data === null) return [];
    if ($nom_utilisateur !== "") {
        return $data[$nom_utilisateur] ?? [];
    }
    return $data;
}

function selection(string $filtre, string $valeur): string {
    return $filtre === $valeur ? "selected" : "";
}

$categorie = $_GET['categorie'] ?? '';
$regime = $_GET['regime'] ?? '';
$allergene = $_GET['allergene'] ?? '';
$recherche = $_GET['recherche'] ?? '';
?>

<form method="GET" action="presentation.php">
    <div class="barre-filtre">
        <input type="text" name="recherche" id="bar-recherche" placeholder="Rechercher un plat" value="<?php echo htmlspecialchars($recherche); ?>">    
        <select name="categorie" id="filtre-carte">
            <option value="">Toute la carte</option>
            <option value="entrees" <?php echo selection($categorie, 'entrees'); ?>>Entrées</option>
            <option value="plats" <?php echo selection($categorie, 'plats'); ?>>Plats</option>
            <option value="desserts" <?php echo selection($categorie, 'desserts'); ?>>Desserts</option>
            <option value="vins" <?php echo selection($categorie, 'vins'); ?>>Vins</option>
            <option value="cafes" <?php echo selection($categorie, 'cafes'); ?>>Cafés</option>
        </select>
        <select name="regime" id="filtre-regime">
            <option value="">Tous les régimes</option>
            <option value="vege" <?php echo selection($regime, 'vege'); ?>>Végétarien</option>
            <option value="non-vege" <?php echo selection($regime, 'non-vege'); ?>>Non végétarien</option>
        </select>
        <select name="allergene" id="filtre-allergenes">
            <option value="">Tous (avec ou sans allergènes)</option>
            <option value="50" <?php echo selection($allergene, '50'); ?>>Sans gluten</option>
            <option value="51" <?php echo selection($allergene, '51'); ?>>Sans crustacés</option>
            <option value="52" <?php echo selection($allergene, '52'); ?>>Sans oeufs</option>
            <option value="53" <?php echo selection($allergene, '53'); ?>>Sans lactose</option>
            <option value="54" <?php echo selection($allergene, '54'); ?>>Sans fruits à coque</option>
        </select>
        <button type="submit">Filtrer</button>
    </div>
</form>

<?php
$data = lire_data("../data/plats.json");
$recherche1 = strtolower($recherche);
$categories = [
    "entrees"  => "Entrées",
    "plats"    => "Plats",
    "desserts" => "Desserts",
    "vins"     => "Vins",
    "cafes"    => "Cafés",
];
foreach ($categories as $id => $intitule) {
    if ($categorie !== '' && $categorie !== $id) continue;
    $plats_categorie = [];
    foreach ($data as $cle => $plat) {
        if ($cle === "Allergenes") continue;
        if ($plat["categorie"] !== $id) continue;
        if ($regime === 'vege' && !$plat["est_vegetarien"]) continue;
        if ($regime === 'non-vege' && $plat["est_vegetarien"]) continue;
        if ($allergene !== '' && in_array($allergene, $plat["allergene_id"])) continue;
        if ($recherche1 !== '' && strpos(strtolower($plat["nom"]), $recherche1) === false) continue;
        $plats_categorie[$cle] = $plat; 
    }
    if (count($plats_categorie) === 0) continue;
    echo "<section class='rectangle'>";
    echo "<h2>" . $intitule . "</h2>";
    echo "<ul>";
    foreach ($plats_categorie as $plat_id => $plat) {
        echo "<li>";
        echo "<div class='ligne'>";
        echo "<span class='nom'>" . $plat["nom"] . "</span>";
        echo "<span class='prix'>" . $plat["prix"] . "€</span>";
        echo "</div>";
        echo "<span class='description'>" . $plat["description"] . "</span>";
        if (isset($_SESSION) && $_SESSION["connecte"] === true && $_SESSION["role"] === "Client") {
        echo "<div><a href='panier.php?action=ajouter&id=" . $plat_id ."' class='btn-ajouter'>+ Ajouter</a></div>";
        }
        echo "</li>";
    }
    echo "</ul>";
    echo "</section>";
}
?>
</main>
<footer>
    <p>© 2026 L'oro di Cicerone — Tous droits réservés</p>
    <a href="contact.php">Nous contacter</a>
</footer>
</body>
</html>