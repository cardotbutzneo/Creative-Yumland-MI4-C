<?php session_start();

if (!isset($_SESSION["email"]) or ($_SESSION["role"] != "Client" and $_SESSION["role"] != "admin")){
    header("Location: connexion.php?error=unauthorized");
    exit;
}

require_once __DIR__."/../serveur.php";

$_SESSION["derniers-plats"] = [];
$plats = ["entree" => [], "plats" => [], "dessert" => [], "cafe" => []];
$data = lire_data("../data/plats.json");
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
$pts = $_SESSION["pts-fidelite"] ?? 0;

if ($pts < 25) {
    $class = "grade-amethyste";
    $max = 25;
    $nom_grade = "Améthyste";
}
elseif ($pts < 50) {
    $class = "grade-rubis";
    $max = 50;
    $nom_grade = "Rubis";
}
else {
    $class = "grade-or";
    $max = 100;
    $nom_grade = "Buisson-Or";
}
$_SESSION["programme-fidelite"] = $nom_grade;

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
            <li><a href="presentation.php">Menu</a></li>
            <li><a href="modifier_profil.php">Modifier le profil</a></li>
            <li><a href="securite.php">Sécurité</a></li>
            <li><a href="deconnexion.php">se déconnecter</a></li>
        </ul>
    </nav>
</header>
    <?php 
        if (isset($_GET["flag"]) && $_GET["flag"] === "success") {
            echo '<div class="notification-success">
                    <p>Vos modifications ont bien été prises en compte</p>
                  </div>';
}
    ?>
    <section>
            <?php if ($_SESSION["role"] == "Client"){
                echo '<div class="contenent" style="text-align : left;">';
                echo "<p>Bienvenue " . $_SESSION["nom"] . " " . $_SESSION["prenom"] . "</p>";
                echo "<p>Programme " . $_SESSION["programme-fidelite"];
                echo "</div>";
            }
            ?>
        <div class="contenent">
            <h2>Historique des dernières commandes</h2>
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
                        echo "<p>Pas d'historique pour le moment.</p>";
                    }
                ?>
            </nav>
        </div>
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
        <div class="contenent">
            <h2>Vos points fidélités</h2>
            <div class="fidelite-card">
                <progress class="<?= $class ?>" value="<?= $pts ?>" max="<?= $max ?>"></progress>
                <span><?php if ($pts <= $max) {echo "$pts / $max points";} else echo "$pts points"?></span>
                <?php if ($pts < 50): ?>
                    <p>Prochain programme : <strong><?= ($pts < 25) ? "Rubis" : "Or" ?></strong></p>
                <?php endif; ?>
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