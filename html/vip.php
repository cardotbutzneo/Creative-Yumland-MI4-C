<?php session_start(); 

require_once __DIR__."/../serveur.php";

if (!isset($_SESSION["connecte"]) or $_SESSION["connecte"] !== true or !isset($_SESSION["role"]) or ($_SESSION["role"] !== "Client") and $_SESSION["role"] !== "admin"){
    header("Location: connexion.php?error=unauthorized");
    exit;
}

$aujourdhui = date("Y-m-d");
$demain = date("Y-m-d",strtotime("+1 day"));

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/vip.css">
    <script src="../script.js" defer></script>
    <title>Document</title>
</head>
<body>
    <header>
        <a href="index.php"><h1>L’oro di Cicerone</h1></a>
        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="profil_client.php">Profil</a></li>
            </ul>
        </nav>
    </header>
    <div class="titre">
            <h1>Dépenser mes points</h1>   
            <p>Expériences exclusives réservées à nos membres</p> 
    </div>
    <div class="pts">
        <?="<p>Mes points : " . $_SESSION["pts-fidelite"] . "pts</p>"?>
    </div>
    <section>
        <div class="contenent">
            <h2>Chauffeur privé</h2>
            <p class="expliquation">Voyagez avec élégance. Un chauffeur dédié vous prend en charge pour votre soirée au restaurant et vous raccompagne en toute sérénité.</p>
            <hr>
            <form method="POST"><span class="valeur">500 pts par personne</span><input type="submit" value="Utiliser" name="chauffeur" class="pts-bouton"></input></form>
            <?php if (isset($target) and $target == "chauffeur"){
                echo '<div class="disponibilite">
                    <p>A quelle date voulez-vous venir manger ?</p>
                    <form method="GET">
                        <input type="date" name="sub-date" min='.$demain.' required>
                        <input type="submit" class="pts-bouton">
                    </form>
                </div>';
            }?>
        </div>
        <div class="contenent">
            <h2>Table privative en cuisine</h2>
            <p class="expliquation">Vivez une expérience inédite au cœur de la cuisine. Une table privée dressée pour vous pendant que notre chef officie en direct.</p>
            <hr>
            <form method="POST"><span class="valeur">800 pts par personne</span><input type="submit" value="Utiliser" name="table" class="pts-bouton"></input></form>
            <?php 
            if (isset($target) and $target == "table"){
                echo '<div class="disponibilite">
                    <p>A quelle date voulez-vous venir manger ?</p>
                    <form method="GET">
                        <input type="date" name="sub-date" min='.$demain.' required>
                        <input type="submit" class="pts-bouton">
                    </form>
                </div>';
            }
            
            ?>

        </div>
        <div class="contenent">
            <h2>Le chef à domicile</h2>
            <p class="expliquation">Notre chef se déplace chez vous pour composer un menu sur-mesure et préparer un dîner d'exception dans le confort de votre maison.</p>
            <hr>
           <form method="POST"><span class="valeur">1200 pts par personne</span><input type="submit" value="Utiliser" name="domicile" class="pts-bouton"></input></form>
            <?php if (isset($target) and $target == "domicile"){
                echo '<div class="disponibilite">
                    <p>A quelle date voulez-vous venir manger ?</p>
                    <form method="GET">
                        <input type="date" name="sub-date" min='.$demain.' required>
                        <input type="submit" class="pts-bouton">
                    </form>
                </div>';
            }?>
        </div>
        <div class="contenent">
            <h2>Salon privatif</h2>
            <p class="expliquation">Profitez d'un espace entièrement privatisé, avec service attentionné et ambiance feutrée, idéal pour vos occasions les plus précieuses.</p>
            <hr>
            <form method="POST"><span class="valeur"><?php if ($_SESSION["total-fidelite"] < 2000) echo "1500 pts par personne"; else echo "Gratuit";?></span><input type="submit" value="Utiliser" name="salon" class="pts-bouton"></input></form>
            <?php if (isset($target) and $target == "salon"){
                echo '<div class="disponibilite">
                    <p>A quelle date voulez-vous venir manger ?</p>
                    <form method="GET">
                        <input type="date" name="sub-date" min='.$demain.' required>
                        <input type="submit" class="pts-bouton">
                    </form>
                </div>';
            }?>
        </div>
</section>  
</body>
</html>