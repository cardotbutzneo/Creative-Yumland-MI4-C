<?php session_start();

require_once __DIR__."/../serveur.php";

$_SESSION["derniers-plats"] = [];
$plats = ["entree" => [], "plats" => [], "dessert" => [], "cafe" => []];
$data = lire_data("plats.json");
foreach ($data as $nom_plat){
    $cat = $nom_plat["categorie"];
    switch ($cat) {
        case 'entrees':
            $plats["entrees"][] = $nom_plat;
            break;
        case 'plats':
            $plats["plats"][] = $nom_plat;
            break;
        case 'desserts':
            $plats["desserts"][] = $nom_plat;
            break;
        case 'cafes':
            $plats["cafe"][] = $nom_plat;
            break;
        default:
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/profil_client.css"> 
    <title>Profil Client - L’oro di Cicerone</title>
</head>
<body>
    <header>
    <a href="index.php"><h1>L’oro di Cicerone</h1></a>
    <nav>
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="restaurant.php">Le Restaurant</a></li>
            <li><a href="chef.php">Le Chef</a></li>
            <li><a href="presentation.php">Menu</a></li>
            <li><a href="connexion.php">Réserver</a></li>
            <li><a href="modifier_profil.php">Modifier le profil</a></li>
        </ul>
    </nav>
</header>
    <section>
        <div class="contenent">
            <h2>Favori</h2>
            <nav>
                <?php
                    if (isset($_SESSION["derniers-plats"]) and !empty($_SESSION["derniers-plats"]) and isset($plats)){
                        echo '<ul class="sugestions">';
                        foreach ($_SESSION["derniers-plats"] as $plat){
                            echo "<li><span>" . htmlspecialchars($plat) . "</span> <span>". htmlspecialchars($plats[$plat]["prix"]) . "€ </span></li>";
                        }
                        echo "</ul>";
                    }
                    else{
                        echo "<p>Pas de plats favoris pour le moment.</p>";
                    }
                ?>
            </nav>
        </div>
        <hr>
        <div class="contenent">
            <h2>Nos suggestions</h2>
            <nav>
                <h2>Entrée</h2>
                <ul class="sugestions">
                    <?php
                        $val = generer_suggestions($plats,"entrees");
                        echo "<li><span>" . $val["plat"] . "</span>" . "<span>" . $val["prix"] . "€ </span></li>";
                        $val = generer_suggestions($plats, "entrees");
                        echo "<li><span>" . $val["plat"] . "</span>" . "<span>" . $val["prix"] . "€ </span></li>";
                    ?>
                </ul>
                <hr>
                <h2>Plats</h2>
                <ul class="sugestions">
                    <?php
                        $val = generer_suggestions($plats,"plats");
                        echo "<li><span>" . $val["plat"] . "</span>" . "<span>" . $val["prix"] . "€ </span></li>";
                        $val = generer_suggestions($plats, "plats");
                        echo "<li><span>" . $val["plat"] . "</span>" . "<span>" . $val["prix"] . "€ </span></li>";
                        $val = generer_suggestions($plats, "plats");
                        echo "<li><span>" . $val["plat"] . "</span>" . "<span>" . $val["prix"] . "€ </span></li>";
                    ?>
                </ul>
                <hr>
                <h2>Desserts</h2>
                <ul class="sugestions">
                    <?php
                        $val = generer_suggestions($plats,"desserts");
                        echo "<li><span>" . $val["plat"] . "</span>" . "<span>" . $val["prix"] . "€ </span></li>";
                        $val = generer_suggestions($plats, "desserts");
                        echo "<li><span>" . $val["plat"] . "</span>" . "<span>" . $val["prix"] . "€ </span></li>";
                    ?>
                </ul>
                <hr>
                <h2>Café</h2>
                <ul class="sugestions">
                    <?php
                        $val = generer_suggestions($plats,"cafe");
                        echo "<li><span>" . $val["plat"] . "</span>" . "<span>" . $val["prix"] . "€ </span></li>";
                        $val = generer_suggestions($plats, "cafe");
                        echo "<li><span>" . $val["plat"] . "</span>" . "<span>" . $val["prix"] . "€ </span></li>";
                    ?>
                </ul>
            </nav>
        </div>
        <hr>
        <div class="contenent">
            <h2>Vos points fidélités</h2>
            <div class="block">
                <p>0 point.s</p>
            </div>
        </div>
    </section>
<footer>
    <p>© 2026 L’oro di Cicerone — Tous droits réservés</p>
    <a href="contact.php">Nous contacter</a>
</footer>
</body>
</html>

<?php

function generer_suggestions(array $plats, string $type) : ?array {
    if (empty($plats[$type])) return null;

    $index = array_rand($plats[$type]);
    $plat = $plats[$type][$index];

    return [
        "plat" => $plat["nom"],
        "prix" => $plat["prix"]
    ];
}

?>