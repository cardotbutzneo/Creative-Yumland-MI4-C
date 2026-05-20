<?php
require_once __DIR__."/../api/config.php";

if(!isset($_SESSION["connecte"])){
    header("Location: connexion.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remerciement - L'oro di Cicerone</title>
    <meta http-equiv="refresh" content="8; URL=index.php"> 
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/remerciement.css">
</head>
<body>
    <header>
    <a href="index.php"><h1>L'oro di Cicerone</h1></a>
    </header>
    <main>
        <div class="texte">
            <h1>Merci d'avoir commandé chez nous !</h1>
            <p>Nous espérons vous revoir très bientôt chez L'oro di Cicerone.</p>
            <p>Vous allez être redirigé vers la page d'acceuil d'ici quelques instants...</p>
            <a href="index.php">Cliquez ici si la redirection ne marche pas</a>
            </div>
    </main>
    <footer>
        <p>© 2026 L'oro di Cicerone — Tous droits réservés</p>
    </footer>
</footer>
</body>
</html>