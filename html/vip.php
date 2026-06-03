<?php 

require_once __DIR__."/../api/config.php";

verifier_connexion($role,"Client");

$target = "chauffeur"; // par defaut
$cout = 500; // par defaut

if (!empty($_POST)){
    if (isset($_POST["chauffeur"]) && $_SESSION["pts-fidelite"] >= 500){ 
        $target = "chauffeur";
        $cout = 500;
    }
    if (isset($_POST["table"])){
        $target = "table";
        $cout = 800;
    }
    if (isset($_POST["domicile"])){
        $target = "domicile";
        $cout = 1200;
    }
    if (isset($_POST["salon"])){
        $target = "salon";
        if ($_SESSION["total-fidelite"] < 2000){
            $cout = 1500;
        }
        else $cout = 0;
    }
}

if (!empty($_GET["sub-date"])){
    if ($target != null){
        $nv_pts = $_SESSION["pts-fidelite"] - $cout;
        if ($nv_pts >= 0){
            $_SESSION["pts-fidelite"] = $nv_pts;
            $data = lire_data("../data/client.json");
            $data[$_SESSION["email"]]["pts-fidelite"] = $nv_pts;
            ecrire_data("../data/client.json",$data);
        }
    }
}

$_GET["sub-date"] = "";

?>

<!DOCTYPE html>
<html lang="<?php if ($isFrench) echo "fr"; else echo "en"; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/vip.css">
    <script src="../script.js" defer></script>
    <title><?= $text["vip"]["title"] ?></title>
</head>
<body>
    <header>
        <a href="index.php"><h1>L'oro di Cicerone</h1></a>
        <nav>
            <ul>
                <li><a href="index.php"><?php if ($isFrench) echo "Accueil"; else echo "Home page"; ?></a></li>
                <li><a href="profil_client.php"><?= $text["vip"]["nav_profile"] ?></a></li>
            </ul>
        </nav>
    </header>
    <div class="titre">
            <h1><?= $text["vip"]["page_title"] ?></h1>   
            <p><?= $text["vip"]["subtitle"] ?></p> 
    </div>
    <div class="pts">
        <p><?= $text["vip"]["my_points"] ?> <?= $_SESSION["pts-fidelite"] ?>pts</p>
    </div>
    <section>
        <div class="contenent">
            <h2><?= $text["vip"]["private_driver_title"] ?></h2>
            <p class="expliquation"><?= $text["vip"]["private_driver_text"] ?></p>
            <hr>
            <form method="POST">
                <span class="valeur">500 <?= $text["vip"]["points_per_person"] ?></span>
                <input type="submit" value="<?= $text["vip"]["use_button"] ?>" name="chauffeur" class="pts-bouton">
            </form>
            <?php if (isset($target) and $target == "chauffeur"){ ?>
                <div class="disponibilite">
                    <p><?= $text["vip"]["date_question"] ?></p>
                    <form method="GET">
                        <input type="date" name="sub-date" min="<?= $demain ?>" required>
                        <input type="submit" class="pts-bouton">
                    </form>
                </div>
            <?php } ?>
        </div>
        <div class="contenent">
            <h2><?= $text["vip"]["kitchen_table_title"] ?></h2>
            <p class="expliquation"><?= $text["vip"]["kitchen_table_text"] ?></p>
            <hr>
            <form method="POST">
                <span class="valeur">800 <?= $text["vip"]["points_per_person"] ?></span>
                <input type="submit" value="<?= $text["vip"]["use_button"] ?>" name="table" class="pts-bouton">
            </form>
            <?php if (isset($target) and $target == "table"){ ?>
                <div class="disponibilite">
                    <p><?= $text["vip"]["date_question"] ?></p>
                    <form method="GET">
                        <input type="date" name="sub-date" min="<?= $demain ?>" required>
                        <input type="submit" class="pts-bouton">
                    </form>
                </div>
            <?php } ?>
        </div>
        <div class="contenent">
            <h2><?= $text["vip"]["chef_home_title"] ?></h2>
            <p class="expliquation"><?= $text["vip"]["chef_home_text"] ?></p>
            <hr>
           <form method="POST">
                <span class="valeur">1200 <?= $text["vip"]["points_per_person"] ?></span>
                <input type="submit" value="<?= $text["vip"]["use_button"] ?>" name="domicile" class="pts-bouton">
            </form>
            <?php if (isset($target) and $target == "domicile"){ ?>
                <div class="disponibilite">
                    <p><?= $text["vip"]["date_question"] ?></p>
                    <form method="GET">
                        <input type="date" name="sub-date" min="<?= $demain ?>" required>
                        <input type="submit" class="pts-bouton">
                    </form>
                </div>
            <?php } ?>
        </div>
        <div class="contenent">
            <h2><?= $text["vip"]["private_lounge_title"] ?></h2>
            <p class="expliquation"><?= $text["vip"]["private_lounge_text"] ?></p>
            <hr>
            <form method="POST">
                <span class="valeur">
                    <?php if ($_SESSION["total-fidelite"] < 2000) echo "1500 " . $text["vip"]["points_per_person"]; else echo $text["vip"]["free"];?>
                </span>
                <input type="submit" value="<?= $text["vip"]["use_button"] ?>" name="salon" class="pts-bouton">
            </form>
            <?php if (isset($target) and $target == "salon"){ ?>
                <div class="disponibilite">
                    <p><?= $text["vip"]["date_question"] ?></p>
                    <form method="GET">
                        <input type="date" name="sub-date" min="<?= $demain ?>" required>
                        <input type="submit" class="pts-bouton">
                    </form>
                </div>
            <?php } ?>
        </div>
</section>  
</body>
</html>