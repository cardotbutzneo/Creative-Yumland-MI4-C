<?php

require_once __DIR__."/../api/config.php";

verifier_connexion($role,"Client");

$informations = $data_client[$_SESSION["email"]];

$erreur = "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>modifier le profil</title>
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
            <li><a href="index.php">Accueil</a></li>
        </ul>
    </nav>
    </header>

    <main class="conteneur-connexion">
    <section class="carte-connexion">
        <h2 class="titre-page">Modifier les informations</h2>
        <p style="font-size: smaller;">Vos informations ont été préremplis depuis votre page de profil.</p>
        <p style="text-align:center" ><a href="condition_generale.php" target="_blank" class="obligatoire" style="font-size: smaller;">A props de mes données personnelles</a></p>

        <?php if (!empty($erreur)){ ?>
            <div class="message-erreur">
                <?php echo $erreur; ?>
            </div>
        <?php } ?>

        <form method="POST" action="">
            <div class="champ-formulaire">
                <label class="intitule" for="nom">Nom</label>
                <input type="text" id="nom" name="nom" class="champ" value="<?= htmlspecialchars( $informations["nom"] ?? '') ?>">
            </div>
            <div class="champ-formulaire">
                <label class="intitule" for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" class="champ" value="<?= htmlspecialchars($informations["prenom"] ?? '') ?>">
            </div>
            <div class="champ-formulaire">
                <label class="intitule" for="adresse"></span>Adresse</label>
                <input type="text" id="adresse" name="adresse" class="champ"
                       placeholder="Ex : 19 Rue du Chemin Vert, 75011 Paris" value="<?= htmlspecialchars($informations["contact"]["adresse"] ?? '') ?>">
            </div>
            <div class="champ-formulaire">
                <label class="intitule" for="cmp-adresse">Complément d'adresse</label>
                <input type="text" id="cmp-adresse" name="complement_adresse" class="champ"
                       placeholder="Ex : Code immeuble, étage…" value="<?= htmlspecialchars($informations["contact"]["complément d'adresse"] ?? '') ?>">
            </div>
            <div class="champ-formulaire">
                <label class="intitule" for="tel">Téléphone</label>
                <input type="text" id="tel" name="tel" class="champ" required value="<?= htmlspecialchars($informations["contact"]["téléphone"] ?? '') ?>">
            </div>
            <div class="champ-formulaire">
                <label class="intitule" for="email">Adresse e-mail</label>
                <input type="email" id="email" name="mail" class="champ" required value="<?= htmlspecialchars($informations["contact"]["adresse email"] ?? '') ?>">
            </div>
            <input type="submit" name="valider" value="Valider les modifications" class="bouton-validation">
        </form>
        <p style="font-size: smaller; color: white" class="message-erreur">
            Une <span class="obligatoire">* </span>signifie un champ obligatoire
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
    exit; // on rafraichit
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