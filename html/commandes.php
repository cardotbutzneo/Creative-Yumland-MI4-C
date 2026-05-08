<?php
session_start();

require_once __DIR__."/../api/config.php";

verifier_connexion($role,"Cuisinier");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/commandes.css">
    <link rel="stylesheet" href="style/notification.css">
    <script src="../script.js" defer></script>
    <script src="../javascript/commande.js" defer></script>
    <title>Commandes - L'oro di Cicerone</title>
</head>
<body>
    <input type="hidden" id="nb-cmd" data-nb_cmd="<?= count(lire_data("../data/commandes.json")) ?>">
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
    <div class="notification" id="notification" style="display : none">
        <div class="notification-header">
            <span class="notification-titre">Notification</span>
            <button class="notification-close" onclick="this.closest('.notification').style.display='none'">✕</button>
        </div>
        <p class="notification-body">
            Nouvelle commande disponible.
        </p>
        <div class="notification-barre">
            <div class="notification-barre-fill"></div>
        </div>
    </div>

    <div id="liste-commandes">
        <p>Chargement des commandes...</p>
    </div>

    <script>
        async function chargerCommandes() {
            let nCmd = document.querySelector("#nb-cmd");
            let n = nCmd.dataset.nb_cmd;
            try{
                const reponse = await fetch("../api/get_new_commande.php");
                const data =  await reponse.json();
                console.log("nombre de commande : " + n);
                renderCommandes(data, n);
            }
            catch (e){
                console.log("Erreur :" + e);
            }
        }

        // chargement des commandes au démarages
        chargerCommandes();
        setInterval(chargerCommandes,10000);
    </script>
</body>
</html>