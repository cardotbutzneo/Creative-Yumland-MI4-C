<?php

require_once __DIR__."/../api/config.php";

$erreur = "";

/*
 * Crée un nouveau client à partir des données déjà existantes.
 *
 * @param array $donnee Tableau contenant les clients déjà enregistrés.
 * @return array Tableau représentant le nouveau client.
 */
function creer_client(array $donnee) : array {
    //on crée un nouvel identifiant en fonction du nombre de clients existants
    $nouveau_nombre = count($donnee) + 1;

    //transforme ce nombre en identifiant à 8 chiffres
    $id = str_pad($nouveau_nombre, 8, "0", STR_PAD_LEFT);
    
    //retourne toutes les informations du nouveau client sous forme de tableau
    return [
        "id" => $id,
        "nom" => $_POST["nom"],
        "prenom" => $_POST["prenom"],

        "mot de passe" => password_hash($_POST["password"], PASSWORD_BCRYPT),
        "contact" => [
            "adresse" => $_POST["adresse"],
            "complément d'adresse" => $_POST["complement_adresse"],
            "téléphone" => $_POST["tel"],
            "adresse email" => $_POST["mail"]
        ],   
        "role" => "Client",
        "dernieres_commandes" => [],
        "securite" => [
            "date_creation" => date("Y-m-d"),
            "derniere_connexion" => date("Y-m-d H:i:s"),
            "est_banni" => false,
            "est_en_ligne" => false,
            "est_modifiable" => true,
            "tentative_echec" => 0,
            "derniere_tentative" => null
        ],
        "pts-fidelite" => 0,
        "total-fidelite" => 0,
        "livraison" => false
    ];  
}

//verifie si le formulaire d'inscription a été envoyé
if (isset($_POST["inscription"])) {

    // recupération des champs nécessaires à l'inscription
    $email = $_POST["mail"];
    $password = $_POST["password"];
    $confirmer_password = $_POST["confirmer_password"];

    if ($password !== $confirmer_password) {
        $erreur = $text["inscription"]["error_passwords_different"];
    } else {

        //recupération de la base actuelle des clients.
        $bdd_actuelle = $data_client;

        //si les données récupérées ne sont pas un tableau, on initialise un tableau vide
        if (!is_array($bdd_actuelle)) $bdd_actuelle = [];

        //verifie si un compte existe déjà avec cette adresse email.
        if (isset($bdd_actuelle[$email])) {
            $erreur = $text["inscription"]["error_account_exists"];
        } else {
            //creation du nouveau client.
            $nouveau_client = creer_client($bdd_actuelle);
            //ajout du nouveau client dans la base avec son email comme clé
            $bdd_actuelle[$email] = $nouveau_client;
            //ecriture des nouvelles données dans le fichier JSON des clients.
            ecrire_data("../data/client.json", $bdd_actuelle);
            header("Location: index.php?flag=success");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?php if ($isFrench) echo "fr"; else echo "en"; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/authentification.css">
    <link rel="stylesheet" href="style/notification.css">
    <script src="../javascript/inscription.js" defer></script>
    <title><?= $text["inscription"]["title"] ?></title>
</head>
<body>

<header>
    <a href="index.php"><h1>L'oro di Cicerone</h1></a>

    <nav>
        <ul>
            <li><a href="index.php"><?php if ($isFrench) echo "Accueil"; else echo "Home page"; ?></a></li>
            <li><a href="restaurant.php"><?php if ($isFrench) echo "Le Restaurant"; else echo "The Restaurant"; ?></a></li>
            <li><a href="chef.php"><?php if ($isFrench) echo "Le Chef"; else echo "The Chef"; ?></a></li>
            <li><a href="presentation.php">Menu</a></li>
            <li><a href="connexion.php"><?= $text["inscription"]["nav_booking"] ?></a></li>
        </ul>
    </nav>
</header>

<main class="conteneur-connexion">
    <section class="carte-connexion">
        <h2 class="titre-page"><?= $text["inscription"]["page_title"] ?></h2>

        <?php if (!empty($erreur)){ ?>
            <div class="message-erreur">
                <?php echo $erreur; ?>
            </div>
        <?php } ?>

        <form method="post" action="">
            <div class="champ-formulaire">
                <label class="intitule">
                    <span class="obligatoire">* </span><?= $text["inscription"]["lastname"] ?>
                </label>
                <input type="text" name="nom" class="champ" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
            </div>
            <div class="champ-formulaire">
                <label class="intitule">
                    <span class="obligatoire">* </span><?= $text["inscription"]["firstname"] ?>
                </label>
                <input type="text" name="prenom" class="champ" required value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
            </div>
            <div class="champ-formulaire">
                <label class="intitule">
                    <span class="obligatoire">* </span><?= $text["inscription"]["address"] ?>
                </label>
                <input type="text" name="adresse" class="champ"
                       placeholder="<?= $text["inscription"]["address_placeholder"] ?>" required value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>">
            </div>
            <div class="champ-formulaire">
                <label class="intitule"><?= $text["inscription"]["address_complement"] ?></label>
                <input type="text" name="complement_adresse" class="champ"
                       placeholder="<?= $text["inscription"]["address_complement_placeholder"] ?>" value="<?= htmlspecialchars($_POST['complement_adresse'] ?? '') ?>">
            </div>
            <div class="champ-formulaire">
                <label class="intitule">
                    <span class="obligatoire">* </span><?= $text["inscription"]["phone"] ?>
                </label>
                <input type="text" name="tel" class="champ" required value="<?= htmlspecialchars($_POST['tel'] ?? '') ?>">
            </div>
            <div class="champ-formulaire">
                <label class="intitule">
                    <span class="obligatoire">* </span><?= $text["inscription"]["email"] ?>
                </label>
                <input type="email" name="mail" class="champ" required value="<?= htmlspecialchars($_POST['mail'] ?? '') ?>">
            </div>
            <div class="champ-formulaire">
                <label class="intitule">
                    <span class="obligatoire">* </span><?= $text["inscription"]["password"] ?>
                </label>

                <div class="password-wrapper">
                    <input type="password" name="password" id="password" class="champ" required>
                    <button type="button" class="toggle-eye" onclick="togglePassword('password', 'oeil_ouvert', 'oeil_ferme')" aria-label="<?= $text["inscription"]["show_password"] ?>">
                        <img id="oeil_ouvert" src="style/img/oeil_ouvert.png" alt="<?= $text["inscription"]["show"] ?>">
                        <img id="oeil_ferme" src="style/img/oeil_ferme.png" alt="<?= $text["inscription"]["hide"] ?>" style="display:none;">
                    </button>
                </div>
            </div>
            <div class="champ-formulaire">
                <label class="intitule">
                    <span class="obligatoire">* </span><?= $text["inscription"]["confirm_password"] ?>
                </label>

                <div class="password-wrapper">
                    <input type="password" name="confirmer_password" id="confirmer_password" class="champ" required>
                </div>
            </div>
            <input type="submit" name="inscription" value="<?= $text["inscription"]["submit"] ?>" class="bouton-validation">

            <div class="liens-secondaires">
                <a href="connexion.php"><?= $text["inscription"]["already_account"] ?></a>
            </div>
        </form>
        <p style="font-size: smaller; color: white" class="message-erreur">
            <?= $text["inscription"]["required_prefix"] ?> 
            <span class="obligatoire">* </span>
            <?= $text["inscription"]["required_suffix"] ?>
        </p>
    </section>
</main>
</body>
</html>