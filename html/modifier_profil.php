<?php

require_once __DIR__."/../api/config.php";

verifier_connexion($role,"Client");

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
    <script src="../javascript/formulaire.js" defer></script>
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

        <?php if (!empty($erreur)){ ?>
            <div class="message-erreur">
                <?php echo $erreur; ?>
            </div>
        <?php } ?>

        <form method="post" action="">
            <div class="champ-formulaire">
                <label class="intitule">Nom</label>
                <input type="text" name="nom" class="champ" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
            </div>
            <div class="champ-formulaire">
                <label class="intitule">Prénom</label>
                <input type="text" name="prenom" class="champ" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
            </div>
            <div class="champ-formulaire">
                <label class="intitule"></span>Adresse</label>
                <input type="text" name="adresse" class="champ"
                       placeholder="Ex : 19 Rue du Chemin Vert, 75011 Paris" value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>">
            </div>
            <div class="champ-formulaire">
                <label class="intitule">Complément d'adresse</label>
                <input type="text" name="complement_adresse" class="champ"
                       placeholder="Ex : Code immeuble, étage…" value="<?= htmlspecialchars($_POST['complement_adresse'] ?? '') ?>">
            </div>
            <div class="champ-formulaire">
                <label class="intitule">Téléphone</label>
                <input type="text" name="tel" class="champ" required value="<?= htmlspecialchars($_POST['tel'] ?? '') ?>">
            </div>
            <div class="champ-formulaire">
                <label class="intitule">Adresse e-mail</label>
                <input type="email" name="mail" class="champ" required value="<?= htmlspecialchars($_POST['mail'] ?? '') ?>">
            </div>
            <input type="submit" name="inscription" value="" class="bouton-validation">

            <div class="liens-secondaires">
                <a href="connexion.php">Déjà un compte ? Se connecter</a>
            </div>
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

function modifier_infos() : void {
    /** Modifie les infos de l'utilisateur */
    $toute_la_data = lire_data("../data/client.json");
    $email = $_SESSION["email"];

    if (!isset($toute_la_data[$email])) return;

    foreach (["nom", "prenom", "adresse", "complement_adresse", "tel"] as $var) {
        if (isset($_POST[$var]) && !empty(trim($_POST[$var]))) {
            $toute_la_data[$email][$var] = htmlspecialchars($_POST[$var]);
            $_SESSION[$var] = htmlspecialchars($_POST[$var]);
        }
    }
    ecrire_data("../data/client.json",$toute_la_data);
}

?>