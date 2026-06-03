<?php 

require_once __DIR__."/../api/config.php";

verifier_connexion($role,"Client");

$verif = false;
$verif_mdp = false;
$suppression = false;

if (isset($_POST["password"])){
    $email = $_SESSION["email"];
    if (password_verify($_POST["password"],$data_client[$email]["mot de passe"])){
        if (supprimer_compte($email)){
            $verif = true;
            $verif_mdp = true;
            $suppression = true;
            ?><script>console.log(<?= $suppression ?>)</script><?php
        }
        else {
            echo "<p class='message-erreur'>" . $text["securite"]["incorrect_password"] . "</p>";
        }
    }
    else {
        echo "<p class='message-erreur'>" . $text["securite"]["incorrect_password"] . "</p>";
    }
}

?>

<!DOCTYPE html>
<html lang="<?php if ($isFrench) echo "fr"; else echo "en"; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if ($suppression) echo '<meta http-equiv="refresh" content="5; URL=index.php">'?>
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/authentification.css">
    <script src="../javascript/inscription.js" defer></script>
    <script src="../script.js" defer></script>
    <title><?= $text["securite"]["title"] ?></title>
</head>
<body>
    <header>
        <a href="index.php"><h1>L'oro di Cicerone</h1></a>
        <nav>
            <ul>
                <li><a href="index.php"><?php if ($isFrench) echo "Accueil"; else echo "Home page"; ?></a></li>
                <?php if (!$suppression) echo '<li><a href="profil_client.php">' . $text["securite"]["nav_back_profile"] . '</a></li>';?>
            </ul>
        </nav>
    </header>
    <main class="conteneur-connexion">
        <section class="carte-connexion">
            <h2 class="titre-page"><?= $text["securite"]["page_title"] ?></h2>
            <form method="post"> 
                <div id="settings" style="display : <?= (!$suppression) ? "block" : "none"; ?>">
                    <div class="champ-formulaire">
                        <button type="submit" name="modif-info" class="champ"><?= $text["securite"]["edit_info"] ?></button>
                    </div>
                    <div class="champ-formulaire">
                        <button type="submit" name="modif-mdp" class="champ"><?= $text["securite"]["change_password"] ?></button>
                    </div>
                    <div class="champ-formulaire">
                        <button type="submit" name="supp" class="message-erreur" onclick="toggleSecurite(1)"><?= $text["securite"]["delete_account"] ?></button>
                    </div>
                </div>
                
                <div id="verif" style="display : none">
                    <div class='message-erreur'><?= $text["securite"]["warning_delete"] ?></div>
                    <div class='champ-formulaire'>
                        <button type='submit' name='confirm' class='champ' onclick="toggleSecurite(2)"><?= $text["securite"]["confirm_delete"] ?></button>
                    </div>
                    <div class='champ-formulaire'>
                        <button type='submit' name='abandon' class='champ' onclick="window.location = 'profil_client.php'"><?= $text["securite"]["cancel_delete"] ?></button>
                    </div>
                </div>
                <div id="verif_password" style="display : none">
                    <div class='message-erreur'><?= $text["securite"]["confirm_action_password"] ?></div>
                    <label for="conf-supp"><span class="obligatoire">* </span><?= $text["securite"]["confirm_changes"] ?></label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" class="champ" required>
                        <button type="button" class="toggle-eye" onclick='togglePassword("password", "oeil_ouvert", "oeil_ferme")' aria-label="<?= $text["securite"]["show_password"] ?>">
                            <img id="oeil_ouvert" src="style/img/oeil_ouvert.png" alt="<?= $text["securite"]["show"] ?>">
                            <img id="oeil_ferme" src="style/img/oeil_ferme.png" alt="<?= $text["securite"]["hide"] ?>" style="display:none;">
                        </button>
                    </div>
                    <div class="champ-formulaire">
                        <button type="submit" name="confirmation" class="champ" onclick="toggleSecurite(3)"><?= $text["securite"]["confirm"] ?></button>
                    </div>
                    <p style="font-size : smaller; color : white;" class="message-erreur">
                        <?= $text["securite"]["required_prefix"] ?> <span class="obligatoire">* </span><?= $text["securite"]["required_suffix"] ?>
                    </p>
                </div>
                <div id="delete-account" style="display : <?= $suppression ? "block" : "none"; ?>" >
                    <div style="text-align : center">
                        <p><?= $text["securite"]["deleted_success"] ?></p>
                        <p><?= $text["securite"]["goodbye"] ?></p>
                        <p><?= $text["securite"]["redirecting"] ?></p>
                        <a href='index.php'><?= $text["securite"]["redirect_link"] ?></a>
                    </div>
                </div>            
            </form>
        </section>
    </main>

</body>
</html>

<?php 

function supprimer_compte(string $email) : bool{
    if (!isset($email)) return false;
    $data = lire_data("../data/client.json");
    $email = $_SESSION["email"];
    if (!isset($data)) return false;
    unset($data[$email]);
    $success = ecrire_data("../data/client.json",$data);
    if ($success){
        session_destroy();
        return true;
    }
    return false;
}

?>