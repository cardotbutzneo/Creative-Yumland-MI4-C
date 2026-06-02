<?php
require_once __DIR__."/../api/config.php";

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
            <h1>Merci d'avoir <?= isset($_GET["res"]) ? "réservé" : "commandé" ?> chez nous !</h1>
            <p>Nous espérons vous revoir très bientôt chez L'oro di Cicerone.</p>
            <p>Vous allez être redirigé vers la page d'acceuil d'ici quelques instants...</p>
            <a href="mailto:<?= $_SESSION["email"] ?>">Ajouter ma réservation à mon angenda</a><br>
            <a href="index.php">Cliquez ici si la redirection ne marche pas</a>
            </div>
    </main>
    <footer>
        <p><?= $text["index"]["footer_rights"] ?></p>
        <a href="contact.php"><?= $text["index"]["footer_contact"] ?></a><span> |</span>
        <a href="condition_generale.php"><?= $text["index"]["footer_privacy"] ?></a>
    </footer>
</body>
</html>