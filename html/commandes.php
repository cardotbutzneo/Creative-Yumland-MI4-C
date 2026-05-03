<?php
session_start();

require_once __DIR__."/../serveur.php";

if(!isset($_SESSION["connecte"]) or ($_SESSION["role"] != "Cuisinier" and $_SESSION["role"] != "admin")){
    header("Location: connexion.php?error=unauthorized");
    exit;
}

$_SESSION["derniere-connexion"] = time();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/commandes.css">
    <script src="../script.js" defer></script>
    <script src="../javascript/commande.js" defer></script>
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

    <div id="liste-commandes">
        <p>Chargement des commandes...</p>
    </div>

    <script>
        async function chargerCommandes() {
            try{
                const reponse = await fetch("../api/get_new_commande.php");
                const data = await reponse.json();
                renderCommandes(data);
            }
            catch (e){
                console.log("Erreur :" + e);
            }
        }

        // chargement des commandes au démarages
        chargerCommandes();
        setInterval(chargerCommandes(), 30000); // on recharge toutes les 30s
    </script>
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

<script>
let lastCount = null;
setInterval(async () => {
	const response = await fetch("../api/get_new_commande.php");
	const data = await response.json();
    if (lastCount == null) lastcount = data.nbCommande;
    
}, 10000); // Vérifie toutes les 10 secondes
</script>

<style>
    p.statut:not([data-stat="en preparation"]) button{
        display : "none";
    }
</style>