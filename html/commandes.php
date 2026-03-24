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
$cle_secrete = "0000"; // test
if (isset($_GET["alerte"]) and $_GET["alerte"] == "true"){ // si on a une nouvelle commande, on regarde la bdd
    if (isset($_GET["nb-commande"])){
        $id_commande = $_GET["nb-commande"];
        $bdd = lire_data("commandes.json"); // on lit les données

        if (!isset($bdd[$id_commande])) {
            error_log("[" . date("Y-m-d H:i:s") . "] Commande inexistante : " . $id_commande . "\n", 3, "securite.log");
            return; 
        }
        $data_commande = $bdd[$id_commande];
        $signature = $_GET["nb-commande"]; // on lit le numéro de commandes (le hash)
        $payload = $data_commande["email"] . $data_commande["montant"] . $data_commande["date"];

        $signature_complete = hash_hmac("sha256", $payload, $cle_secrete);

        // On ne prend que les 8 premiers caractères pour le numéro de commande
        $numero_commande = strtoupper(substr($signature_complete, 0, 8));
        //echo $numero_commande;

        if (!isset($signature)) return; // on annule la commande si on a pas le hash
        if (hash_equals($numero_commande,$signature)){
            //$verif_bank = cybank_api($api_key)// on demande à la banque
            $verif_bank = true;
            if ($verif_bank){
                // la transaction est valide, on la traite
                $bdd[$id_commande]["est-valide"] = true; // on met le statut de la commande en valide
                ecrire_data("commandes.json",$bdd);
                //header("Location: confirmation.php?statut=success");
                //exit;
            }
            else {
                header("Location: paiement.php?warning=transaction_failed");
                exit;
            }
        }
        else{
            // sinon, la trasaction à été altérée, on annule la commade
            $message = "[" . date("Y-m-d H:i:s") . "] FRAUDE - Client: " . $email . " - Tentative avec hash invalide.\n";
            error_log($message, 3, "securite.log");
            //header("Location: paiement.php?error=security_breach");
            //exit;
        }
    }
}
//lire_commande();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/commandes.css">
    <title>Commandes - L’oro di Cicerone</title>
</head>
<body>
    <header>
    <a href="index.php"><h1>L’oro di Cicerone</h1></a>
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
        echo "<details open>
            <summary><h2>Commandes En cours</h2></summary>
            <section class='colonne-commandes'>
            ";
        
            $data = lire_data("commandes.json");
            //echo "Commandes en cours :";
            foreach ($data as $hash => $commande) {
                if ($commande["est-valide"]) {
                    if ($commande["statut"] == "en cours"){
                        echo "<div class='block'>";
                        echo "<button>Finir la commande</button>";
                        echo "<span class='commande'>";
                            echo "<p>identifiant de commande : " . $commande["numero"] . "</p>";
                            echo "<p>Statut : " . $commande["statut"] . "</p>";
                        echo "</span>";

                        echo "<div>";
                            echo "<ul>";
                                foreach ($commande["plats"] as $p) {
                                    echo "<li>" . htmlspecialchars($p["nom"]) . " x" . $p["quantite"] . "</li>";
                                }
                            echo "</ul>";
                        echo "</div>";

                        echo "<p id='Complement'>Complément : Burata sans fromage</p>";
                    echo "</div>";
                    }
                }
            }
            echo "</details>";
            echo "<details open>
            <summary><h2>Commandes en livraison </h2></summary>
            <section class='colonne-commandes'>
            ";
            foreach ($data as $hash => $commande) {
                if ($commande["est-valide"]) {
                    if ($commande["statut"] == "en livraison"){
                        echo "<div class='block'>";
                        echo "<button>Finir la commande</button>";
                        echo "<span class='commande'>";
                            echo "<p>identifiant de commande : " . $commande["numero"] . "</p>";
                            echo "<p>Statut : " . $commande["statut"] . "</p>";
                        echo "</span>";

                        echo "<div>";
                            echo "<ul>";
                                foreach ($commande["plats"] as $p) {
                                    echo "<li>" . htmlspecialchars($p["nom"]) . " x" . $p["quantite"] . "</li>";
                                }
                            echo "</ul>";
                        echo "</div>";

                        echo "<p id='Complement'>Complément : Burata sans fromage</p>";
                    echo "</div>";
                    }
                }
            } 
            echo "</details>";
            echo "<details open>
            <summary><h2>Commandes livrées</h2></summary>
            <section class='colonne-commandes'>
            ";
            foreach ($data as $hash => $commande) {
                if ($commande["est-valide"]) {
                    if ($commande["statut"] == "terminée"){
                        echo "<div class='block'>";
                        echo "<form method='POST'>";
                        echo "<input type='hidden' value=".$commande.">"
                        echo "<button name='finir-cmd'>Finir la commande</button>";
                        echo "<span class='commande'>";
                            echo "<p>identifiant de commande : " . $commande["numero"] . "</p>";
                            echo "<p>Statut : " . $commande["statut"] . "</p>";
                        echo "</span>";

                        echo "<div>";
                            echo "<ul>";
                                foreach ($commande["plats"] as $p) {
                                    echo "<li>" . htmlspecialchars($p["nom"]) . " x" . $p["quantite"] . "</li>";
                                }
                            echo "</ul>";
                        echo "</div>";

                        echo "<p id='Complement'>Complément : Burata sans fromage</p>";
                    echo "</div>";
                    }
                }
            } 
            echo "</details>";
        ?>
    </div>
</body>
</html>

<?php 

function finir_commande(){
    if ()
}

?>