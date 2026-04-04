<?php session_start();

if (!isset($_SESSION["email"]) or ($_SESSION["role"] != "Client" and $_SESSION["role"] != "admin")){
    header("Location: connexion.php?error=unauthorized");
    exit;
}

require_once __DIR__."/../serveur.php";

$plats = ["entree" => [], "plats" => [], "dessert" => [], "cafe" => []];
$data = lire_data("../data/plats.json");
foreach ($data as $nom_plat => $plat){
    if ($nom_plat == "Allergenes") continue;
    $cat = $plat["categorie"];
    switch ($cat) {
        case 'entrees':
            $plats["entrees"][] = $plat;
            break;
        case 'plats':
            $plats["plats"][] = $plat;
            break;
        case 'desserts':
            $plats["desserts"][] = $plat;
            break;
        case 'cafes':
            $plats["cafe"][] = $plat;
            break;
        default:
            break;
    }
}
$pts = $_SESSION["total-fidelite"] ?? 0;

if ($pts < 500) {
    $class = "grade-amethyste";
    $max = 500;
    $nom_grade = "Améthyste";
}
elseif ($pts >= 500 and $pts < 1200) {
    $class = "grade-rubis";
    $max = 1200;
    $nom_grade = "Rubis";
}
else {
    $class = "grade-or";
    $max = 1200;
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
    <title>Profil Client - L'oro di Cicerone</title>
</head>
<body>
    <header>
    <a href="index.php"><h1>L'oro di Cicerone</h1></a>
    <nav>
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="presentation.php">Menu</a></li>
            <?php if ($nom_grade == "Buisson-Or" or $nom_grade == "Rubi") echo "<li><a href='vip.php'>VIP</a></li>";?>
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
                echo '<div class="contenent">';
                echo "<p id='nom'>Bienvenue " . $_SESSION["nom"] . " " . $_SESSION["prenom"] . "</p>";
                echo "<div id='fidelite'><span>Programme " . $_SESSION["programme-fidelite"] . "</span><span>Nombre de points : ". $_SESSION["pts-fidelite"] . "</span></div>";
                echo "</div>";
            }
            ?>
        <?php
            if (!empty($_SESSION["derniers-plats"])){
                echo "<div class='contenent'>";
                echo "<h2>Historique des dernières commandes</h2>";
                echo "<nav>";
                    foreach ($_SESSION["derniers-plats"] as $cmd) {
                    $cmd_complette = récupérer_commande($cmd);
                    echo "<div class='cmd-bloc'>";
                    //echo "<p class='numero_cmd'>Commande : ".ltrim( $cmd_complette["numero"],"0")."</p>";
                        foreach ($cmd_complette["plats"] as $cat) {
                            if (isset($cat)) {
                                echo "<li><span>" . htmlspecialchars($cat["nom"]) . " </span>";
                                echo "<span>x" . htmlspecialchars($cat["quantite"]) . "</span></li>";
                            }
                            echo "<hr>";
                        }
                            echo "<div class='cmd-total'>Total : <strong>" . htmlspecialchars($cmd_complette["montant"]) . "€</strong></div>";
                    echo "</div>";
                    }
                echo "</nav>";
                echo "<a href='suivi_commande.php'><button class='btn-suivi'>Suivre ma dernière commande</button></a>";
            }
        ?>
            
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
                <span><?php if ($pts <= $max) {echo "$pts / $max points";} else echo "$pts points cumulés"?></span>
                <?php if ($pts < 50){ ?>
                    <p>Prochain programme : <strong><?= ($pts < 25) ? "Rubis" : "Or" ?></strong></p>
                <?php } ?>
            </div>
        </div>
    </section>
<footer>
    <p>© 2026 L'oro di Cicerone — Tous droits réservés</p>
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