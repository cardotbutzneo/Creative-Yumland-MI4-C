<?php
session_start();

require_once __DIR__."/../serveur.php";

if(!isset($_SESSION["connecte"]) or ($_SESSION["role"] != "Cuisinier" and $_SESSION["role"] != "admin")){
    header("Location: connexion.php?error=unauthorized");
    exit;
}

$_SESSION["derniere-connexion"] = time();

// idée : on peut mettre en place une "alerte" pour avertir le programme quand une nouvelle commande est dans la bdd
// On envoie un message par GET unique
// On récupère un hash unique (mail+montant+date) qui fait office de checksum (numéro de commande)
// Cela permet de savoir si la transaction à été altérer et donc de l'annuler au besoin
// on recoit des infos par une bdd (mail, montant, date au minimum)
// exemple d'url d'entrée : commande.php?alerte=true+nb-commande=0F18AB0F

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    if (isset($_POST) and isset($_POST["commande"])){
        if (isset($_POST["prendre-cmd"])){
            prendre_commande($_POST["commande"]);
            header("Location: ".$_SERVER["PHP_SELF"]);
            exit;
        }
        if (isset($_POST["finir-cmd"])){
            finir_commande($_POST["commande"]);
            header("Location:".$_SERVER["PHP_SELF"]);
            exit;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/commandes.css">
    <title>Commandes - L'oro di Cicerone</title>
</head>
<body>
    <header>
    <a href="index.php"><h1>L'oro di Cicerone</h1></a>
    <nav>
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="deconnexion.php">se déconnecter</a></li>
        </ul>
    </nav>
</header>
    <div>
        <form method="POST" id="barre-recherche">
            <input type="text" name="id_cmd" placeholder="Saisir un id de commande">
            <input type="submit" name="id_cmd">
        </form>
    </div>
    <div id="main-contenent">
        <?php
            $data = lire_data("../data/commandes.json");
            $date_aujourdhui = date_create();
             echo "<details open>
            <summary><h2>Commande à livraison différée (mise en livraison 1h avant)</h2></summary>
            <section class='colonne-commandes'>";
            foreach ($data as $hash => $commande) {
                if ($commande["est-valide"]) {
                    if (isset($commande["date_livraison"])) {
                        $test = date_diff($date_aujourdhui,date_create($commande["date_livraison"]));  
                        if ($test->format("%d") >= 1 or ($test->format("%d") < 1 and $test->format("%h") > 1)){
                            echo "<div class='block'>";
                            echo "<span class='commande'>";
                                echo "<p>identifiant de commande : " . $commande["numero"] . "</p>";
                                echo "<p>Statut : " . $commande["etat"] . "</p>";
                            echo "</span>";
                            echo "<span class='commande'>";
                            echo "<p>Date de livraison : " . date_format(date_create($commande["date_livraison"]),"d/m/Y à H\hi") . "</p>";
                            echo "</span>";

                            echo "<div>";
                                echo "<ul>";
                                    foreach ($commande["plats"] as $p) {
                                        echo "<li>" . htmlspecialchars($p["nom"]) . " x" . $p["quantite"] . "</li>";
                                    }
                                echo "</ul>";
                            echo "</div>";

                            if (isset($commande["instructions"])) echo "<p id='Complement'>".$commande["instructions"]."</p>";
                        echo "</div>";
                        }
                    }
                }
            } 
            echo "</details>";
            //echo "Commandes en cours :";
            echo "</details>";
            echo "<details open>
            <summary><h2>Commandes en préparation </h2></summary>
            <section class='colonne-commandes'>
            ";
            foreach ($data as $hash => $commande) {
                if ($commande["est-valide"]) {
                    // On vérifie si la commande est dans moins d'une heure
                    if (isset($commande["date_livraison"])) {
                        $test = date_diff($date_aujourdhui,date_create($commande["date_livraison"]));  
                        if ($test->format("%d") >= 1 or ($test->format("%d") < 1 and $test->format("%h") > 1)){
                            continue;
                        }
                    }
                    if ($commande["etat"] == "en preparation"){
                        echo "<div class='block'>";
                        echo "<form method='POST'>
                                <input type='hidden' name='commande' value=".$hash.">
                                <button type='submit' name='finir-cmd'>Finir la commande</button>
                            </form>";
                        echo "<span class='commande'>";
                            echo "<p>identifiant de commande : " . $commande["numero"] . "</p>";
                            echo "<p>Statut : " . $commande["etat"] . "</p>";
                        echo "</span>";

                        echo "<div>";
                            echo "<ul>";
                                foreach ($commande["plats"] as $p) {
                                    echo "<li>" . htmlspecialchars($p["nom"]) . " x" . $p["quantite"] . "</li>";
                                }
                            echo "</ul>";
                        echo "</div>";

                        if (isset($commande["instructions"])) echo "<p id='Complement'>".$commande["instructions"]."</p>";
                    echo "</div>";
                    }
                }
            } 
            echo "</details>";
            echo "<details open>
            <summary><h2>Commandes préparées</h2></summary>
            <section class='colonne-commandes'>
            ";
            foreach ($data as $hash => $commande) {
                if ($commande["est-valide"]) {
                    // On vérifie si la commande est dans moins d'une heure
                    if ($commande["etat"] == "preparee") {
                        echo "<div class='block'>";
                            echo "<span class='commande'>";
                                echo "<p>Identifiant : " . htmlspecialchars($commande["numero"]) . "</p>";
                                echo "<p>Statut : " . htmlspecialchars($commande["etat"]) . "</p>";
                            echo "</span>";

                            echo "<div>";
                                echo "<ul>";
                                    foreach ($commande["plats"] as $p) {
                                        echo "<li>" . htmlspecialchars($p["nom"]) . " x" . (int)$p["quantite"] . "</li>";
                                    }
                                echo "</ul>";
                            echo "</div>";

                            if (isset($commande["instructions"])) echo "<p id='Complement'>".$commande["instructions"]."</p>";
                        echo "</div>";
                    }
                }
            }
        ?>
    </div>
</body>
</html>

<?php 

function prendre_commande(string $id_cmd) : void{
    if (!isset($id_cmd)){
        return;
    }
    $data = lire_data("../data/commandes.json");
    if (!isset($data)) return;
    $data[$id_cmd]["etat"] = "en preparation";
    ecrire_data("../data/commandes.json",$data);
}

function finir_commande(string $id_cmd) : void{
    if (!isset($id_cmd)){
        return;
    }
    $data = lire_data("../data/commandes.json");
    if (!isset($data)) return;
    $data[$id_cmd]["etat"] = "preparee";
    ecrire_data("../data/commandes.json",$data);
}

?>