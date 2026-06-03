<?php
require_once __DIR__."/../api/config.php";

?>
<!DOCTYPE html>
<html lang="<?php if ($isFrench) echo "fr"; else echo "en"; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $text["remerciement"]["title"] ?></title>
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
            <h1>
                <?= isset($_GET["res"]) ? $text["remerciement"]["thanks_reserved"] : $text["remerciement"]["thanks_ordered"] ?>
            </h1>
            <p><?= $text["remerciement"]["hope"] ?></p>
            <p><?= $text["remerciement"]["redirect"] ?></p>
            <a href="mailto:<?= $_SESSION["email"] ?? "" ?>"><?= $text["remerciement"]["add_reservation"] ?></a><br>
            <a href="index.php"><?= $text["remerciement"]["redirect_link"] ?></a>
        </div>
    </main>
    <footer>
        <p><?= $text["index"]["footer_rights"] ?></p>
        <a href="contact.php"><?= $text["index"]["footer_contact"] ?></a><span> |</span>
        <a href="condition_generale.php"><?= $text["index"]["footer_privacy"] ?></a>
    </footer>
</body>
</html>