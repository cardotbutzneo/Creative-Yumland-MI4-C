<?php

require_once __DIR__."/../api/config.php";

verifier_connexion($role,"Client");

$informations = $data_client[$_SESSION["email"]];

$erreur = "";
?>

<!DOCTYPE html>
<html lang="<?php if ($isFrench) echo "fr"; else echo "en"; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $text["modifier_profil"]["title"] ?></title>
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/authentification.css">
    <script src="../script.js" defer></script>
    <script src="../javascript/inscription.js" defer></script>
</head>
<body>
    <header>
    <a href="index.php"><h1>L'oro di Cicerone</h1></a>
    <nav>
        <ul>
            <li><a href="index.php"><?php if ($isFrench) echo "Accueil"; else echo "Home page"; ?></a></li>
        </ul>
    </nav>
    </header>

    <main class="conteneur-connexion">
    <section class="carte-connexion">
        <h2 class="titre-page"><?= $text["modifier_profil"]["page_title"] ?></h2>
        <p style="font-size: smaller;"><?= $text["modifier_profil"]["prefilled_info"] ?></p>
        <p style="text-align:center">
            <a href="condition_generale.php" target="_blank" class="obligatoire" style="font-size: smaller;">
                <?= $text["modifier_profil"]["personal_data_link"] ?>
            </a>
        </p>

        <?php if (!empty($erreur)){ ?>
            <div class="message-erreur">
                <?php echo $erreur; ?>
            </div>
        <?php } ?>

        <form method="POST" action="">
            <div class="champ-formulaire">
                <label class="intitule" for="nom"><?= $text["modifier_profil"]["label_lastname"] ?></label>
                <input type="text" id="nom" name="nom" class="champ" value="<?= htmlspecialchars($informations["nom"] ?? '') ?>">
            </div>
            <div class="champ-formulaire">
                <label class="intitule" for="prenom"><?= $text["modifier_profil"]["label_firstname"] ?></label>
                <input type="text" id="prenom" name="prenom" class="champ" value="<?= htmlspecialchars($informations["prenom"] ?? '') ?>">
            </div>
            <div class="champ-formulaire">
                <label class="intitule" for="adresse"><?= $text["modifier_profil"]["label_address"] ?></label>
                <input type="text" id="adresse" name="adresse" class="champ"
                       placeholder="<?= $text["modifier_profil"]["address_placeholder"] ?>" value="<?= htmlspecialchars($informations["contact"]["adresse"] ?? '') ?>">
            </div>
            <div class="champ-formulaire">
                <label class="intitule" for="cmp-adresse"><?= $text["modifier_profil"]["label_address_complement"] ?></label>
                <input type="text" id="cmp-adresse" name="complement_adresse" class="champ"
                       placeholder="<?= $text["modifier_profil"]["address_complement_placeholder"] ?>" value="<?= htmlspecialchars($informations["contact"]["complément d'adresse"] ?? '') ?>">
            </div>
            <div class="champ-formulaire">
                <label class="intitule" for="tel"><?= $text["modifier_profil"]["label_phone"] ?></label>
                <input type="text" id="tel" name="tel" class="champ" required value="<?= htmlspecialchars($informations["contact"]["téléphone"] ?? '') ?>">
            </div>
            <div class="champ-formulaire">
                <label class="intitule" for="email"><?= $text["modifier_profil"]["label_email"] ?></label>
                <input type="email" id="email" name="mail" class="champ" required value="<?= htmlspecialchars($informations["contact"]["adresse email"] ?? '') ?>">
            </div>
            <input type="submit" name="valider" value="<?= $text["modifier_profil"]["submit_button"] ?>" class="bouton-validation">
        </form>
        <p style="font-size: smaller; color: white" class="message-erreur">
            <?= $text["modifier_profil"]["required_prefix"] ?> <span class="obligatoire">* </span><?= $text["modifier_profil"]["required_suffix"] ?>
        </p>
    </section>

</body>
</html>
<style>
    .bouton-validation{
        margin-top : 20px;
        margin-bottom : 10px;
    }
    .alerte-abandon{
        padding : 5px;
    }
    #lien-abandon{
        text-align : center;
        display : none;
    }
    .liens-secondaires{
        margin-top : 10px;
    }
    #pays{
        width: 20%;
        border : 1px black solid;
        border-radius : 5px;
    }
    @media screen and (max-width: 720px) {
        #pays{
            width: 100%;
        }
    }
</style>

<?php 

$afficher_confirmation = false;

if (isset($_POST["valider"]) && !empty($_POST["valider"])){
    modifier_infos();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

/** Modifie les infos de l'utilisateur */
function modifier_infos() : void {
    $data_client = lire_data("../data/client.json");
    $toute_la_data = $data_client;
    $email = $_SESSION["email"];

    if (!isset($toute_la_data[$email])) return;

    foreach (["nom", "prenom", "adresse", "complement_adresse", "tel"] as $var) {
        if (isset($_POST[$var]) && !empty(trim($_POST[$var]))) {
            if (in_array($var, ["complement_adresse", "tel", "adresse"])) 
                $toute_la_data[$email][$var] = htmlspecialchars($_POST[$var]);     
            else 
                $toute_la_data[$email][$var] = htmlspecialchars($_POST[$var]);
            $_SESSION[$var] = htmlspecialchars($_POST[$var]);
        }
    }
    ecrire_data("../data/client.json",$toute_la_data);
}

?>