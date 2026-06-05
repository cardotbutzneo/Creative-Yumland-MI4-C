<?php 

require_once __DIR__."/../api/config.php";

verifier_connexion($role,"Client");

if ($_SESSION["role"] === "admin" && !isset($_GET["id"])){
    header("Location: profil_admin.php?gt=false");
    exit;
}

// Séparation des plats par catégorie pour les suggestions
$plats = ["entree" => [], "plats" => [], "dessert" => [], "cafe" => []];

// On parcourt les plats pour les classer par catégorie
foreach ($data_plats as $nom_plat => $plat){
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

//
if ($_SESSION["role"] == "Client"){
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
}
else if ($_SESSION["role"] == "admin"){
    $pts = $data_client[$_GET["id"]]["total-fidelite"] ?? 0;

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
}

?>

<!DOCTYPE html>
<html lang="<?php if ($isFrench) echo "fr"; else echo "en"; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/profil_client.css"> 
    <link rel="stylesheet" href="style/notification.css">
    <script src="../script.js" defer></script>
    <title><?= $text["profil_client"]["title"] ?></title>
</head>
<body>
    <header>
    <a href="index.php"><h1>L'oro di Cicerone</h1></a>
    <nav>
        <ul>
            <li><a href="index.php"><?php if ($isFrench) echo "Accueil"; else echo "Home page"; ?></a></li>
            <li><a href="presentation.php">Menu</a></li>
            <?php if ($nom_grade == "Buisson-Or" or $nom_grade == "Rubi") echo "<li><a href='vip.php'>VIP</a></li>";?>
            <li><a href="modifier_profil.php"><?= $text["profil_client"]["nav_edit_profile"] ?></a></li>
            <li><a href="securite.php"><?= $text["profil_client"]["nav_security"] ?></a></li>
            <li><a href="deconnexion.php"><?= $text["profil_client"]["nav_logout"] ?></a></li>
        </ul>
    </nav>
</header>
    <div class="notification" id="notification" style="display : none">
        <div class="notification-header">
            <span class="notification-titre">Notification</span>
            <button class="notification-close" onclick="this.closest('.notification').style.display='none'">✕</button>
        </div>
        <p class="notification-body">
            <?= $text["profil_client"]["notification_success"] ?>
        </p>
        <div class="notification-barre">
            <div class="notification-barre-fill"></div>
        </div>
    </div>
    <div class="notification" id="erreur" style="display : none">
        <div class="notification-header">
            <span class="notification-titre">Notification</span>
            <button class="notification-close" onclick="this.closest('.notification').style.display='none'">✕</button>
        </div>
        <p class="notification-body">
            <?= $text["profil_client"]["notification_error"] ?>
        </p>
        <div class="notification-barre">
            <div class="notification-barre-fill"></div>
        </div>
    </div>
    <?php 
    if (isset($_GET["flag"]) && $_GET["flag"] == "success"){?>
        <script>
            document.getElementById('notification').style.display = "block";
        </script>
    <?php }
    if (isset($_GET["err"]) && $_GET["err"] == "fetchFailed"){
        ?>
        <script>
            document.getElementById('erreur').style.display = "block";
        </script>
    <?php } ?>

    <section>
        <?php
        if ($_SESSION["role"] === "Client"){
            $client = $data_client[$_SESSION["email"]];
            $client["programme-fidelite"] = donner_grade($client["total-fidelite"]);
        }
        else if ($_SESSION["role"] === "admin"){
            $client = $data_client[$_GET["id"]];
            $client["programme-fidelite"] = donner_grade($client["total-fidelite"]);
        }?>

        <div class="contenent">
            <p id='nom'><?= $text["profil_client"]["welcome"] ?> <?=$client["nom"] . " " . $client["prenom"]?> </p>
            <div id='fidelite'><span><?= $text["profil_client"]["program"] ?> <?=$client["programme-fidelite"] . "</span><span>" . $text["profil_client"]["points_count"] . " ". $client["pts-fidelite"]?> </span></div>
            <div class="information">
                <p><?= $text["profil_client"]["information"] ?> </p>
                <ul>
                    <li><?= $text["profil_client"]["lastname"] ?> <?= $client["nom"] ?></li>
                    <li><?= $text["profil_client"]["firstname"] ?> <?=$client["prenom"] ?></li>
                    <li><?= $text["profil_client"]["email"] ?> <?=$client["contact"]["adresse email"] ?></li>
                    <li><?= $text["profil_client"]["address"] ?> <?=$client["contact"]["adresse"]?></li>
                    <li><?= $text["profil_client"]["phone"] ?> <?=$client["contact"]["téléphone"]?></li>
                </ul>
            </div>
        </div>

        <?php
        $email_courant   = $_SESSION["email"] ?? '';
        $historique_ids  = $data_client[$email_courant]["dernieres_commandes"] ?? [];
        echo "<div class='contenent'>";
        if (!empty($historique_ids)){
            echo "<h2>" . $text["profil_client"]["orders_history"] . "</h2>";
            echo "<nav>";
                foreach ($historique_ids as $cmd) {
                    $id_upper = strtoupper($cmd);
                    $cmd_complette = $data_commandes[$id_upper] ?? null;
                    if (!$cmd_complette) continue;
                    echo "<div class='cmd-bloc'>";
                        foreach ($cmd_complette["plats"] as $cat) {
                            if (isset($cat)) {
                                if($isFrench) {
                                    echo "<li class='cmd-list'><span>" . htmlspecialchars($cat["nom"]) . " </span>";
                                } else {
                                    echo "<li class='cmd-list'><span>" . htmlspecialchars($cat["nom_eng"] ?? $cat["nom"]) . " </span>";
                                }
                                echo "<span>x" . htmlspecialchars($cat["quantite"]) . "</span></li>";
                            }
                            echo "<hr>";
                        }
                        echo "<div class='cmd-total'>" . $text["profil_client"]["total"] . " <strong>" . htmlspecialchars($cmd_complette["montant"]) . "€</strong></div>";?>
                        <button onclick="envoyerPanier('<?=strtoupper($cmd)?>')" class='btn-suivi'><?= $text["profil_client"]["order_again"] ?></button>
                        <?php if ($cmd_complette["etat"] === "payee" && empty($cmd_complette["deja_modifie"])) { ?>
                            <a href="modifier_commande.php?id=<?= htmlspecialchars($id_upper) ?>">
                                <button class='btn-suivi'><?= $text["profil_client"]["edit_order"] ?></button>
                            </a>
                        <?php } ?>
                    </div>
                <?php }
            echo "</nav>";
            echo "<a href='suivi_commande.php'><button class='btn-suivi'>" . $text["profil_client"]["track_last_order"] . "</button></a>";
        }
        else if (!is_admin()){
            ?>
            <div>
                <p style='padding-bottom : 5px;'><?= $text["profil_client"]["no_order_history"] ?></p>
                <button style='background-color : #0b0b00; color : #c9a24d; border: 1px #c9a24d solid; border-radius:20px; padding : 5px; font-size : 16px' onclick="window.location.href = 'presentation.php'"><?= $text["profil_client"]["order"] ?></button>
            </div>
            <?php
        }
        ?>
        <script>
            function envoyerPanier(id){
                if (id.length == 0) return;
                const url = "panier.php?id_cmd="+id;
                window.location = url;
            }
        </script>
        </div>
        <div class="contenent">
            <h2><?= $text["profil_client"]["suggestions"] ?></h2>
            <nav>
                <h2><?= $text["profil_client"]["starter"] ?></h2>
                <ul class="sugestions">
                    <?php
                        $val = generer_suggestions($plats,"entrees");
                        echo "<li><span>" . $val["plat"] . "</span>" . "<span>" . $val["prix"] . "€ </span></li>";
                        $val = generer_suggestions($plats, "entrees");
                        echo "<li><span>" . $val["plat"] . "</span>" . "<span>" . $val["prix"] . "€ </span></li>";
                    ?>
                </ul>
                <hr>
                <h2><?= $text["profil_client"]["main_courses"] ?></h2>
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
                <h2><?= $text["profil_client"]["desserts"] ?></h2>
                <ul class="sugestions">
                    <?php
                        $val = generer_suggestions($plats,"desserts");
                        echo "<li><span>" . $val["plat"] . "</span>" . "<span>" . $val["prix"] . "€ </span></li>";
                        $val = generer_suggestions($plats, "desserts");
                        echo "<li><span>" . $val["plat"] . "</span>" . "<span>" . $val["prix"] . "€ </span></li>";
                    ?>
                </ul>
                <hr>
                <h2><?= $text["profil_client"]["coffee"] ?></h2>
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
            <h2><?= $text["profil_client"]["loyalty_points"] ?></h2>
            <div class="fidelite-card">
                <progress class="<?= $class ?>" value="<?= $pts ?>" max="<?= $max ?>"></progress>
                <span><?php if ($pts <= $max) {echo "$pts / $max " . $text["profil_client"]["points"];} else echo "$pts " . $text["profil_client"]["points_accumulated"]?></span>
                <?php if ($pts < 50){ ?>
                    <p><?= $text["profil_client"]["next_program"] ?> <strong><?= ($pts < 25) ? "Rubis" : "Or" ?></strong></p>
                <?php } ?>
            </div>
        </div>
    </section>
<footer>
    <p><?= $text["profil_client"]["footer_rights"] ?></p>
    <a href="contact.php"><?= $text["profil_client"]["footer_contact"] ?></a>
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

function donner_grade(int $pts) : ?string{
    if ($pts < 0 ) return null;
    if ($pts < 500) {
        $nom_grade = "Améthyste";
    }
    elseif ($pts >= 500 and $pts < 1200) {
        $nom_grade = "Rubis";
    }
    else {
        $nom_grade = "Buisson-Or";
    }
    return $nom_grade;
}

?>