<?php
session_start();

if (isset($_SESSION["connecte"]) && $_SESSION["connecte"] === true) {
    header("Location: profil_client.php?error=unauthorized");
    exit;
}

require_once __DIR__."/../serveur.php";

$erreur = "";

function creer_client(array $donnee) : array {
    $nouveau_nombre = count($donnee) + 1;
    $id = str_pad($nouveau_nombre, 8, "0", STR_PAD_LEFT);
    
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
        "derniers-plats" => [],
        "securite" => [
            "date_creation" => date("Y-m-d"),
            "derniere_connexion" => date("Y-m-d H:i:s"),
            "est_banni" => false,
            "est_en_ligne" => false,
            "est_modifiable" => true,
            "tentative_echec" => 0
        ]
    ];  
}

if (isset($_POST["inscription"])) {
    $email = $_POST["mail"];
    $password = $_POST["password"];
    $confirmer_password = $_POST["confirmer_password"];

    if ($password !== $confirmer_password) {
        $erreur = "Les mots de passe sont différents.";
    } else {
        $bdd_actuelle = lire_data("client.json");
        if (!is_array($bdd_actuelle)) $bdd_actuelle = [];

        if (isset($bdd_actuelle[$email])) {
            $erreur = "Un compte utilisateur existe déjà avec cette adresse mail.";
        } else {
            $nouveau_client = creer_client($bdd_actuelle);
            $bdd_actuelle[$email] = $nouveau_client;
            ecrire_data("client.json", $bdd_actuelle);

            header("Location: index.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/authentification.css">
    <title>Inscription - L’oro di Cicerone</title>
</head>
<body>

<header>
    <a href="index.php"><h1>L’oro di Cicerone</h1></a>
    <nav>
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="restaurant.php">Le Restaurant</a></li>
            <li><a href="chef.php">Le Chef</a></li>
            <li><a href="presentation.php">Menu</a></li>
            <li><a href="connexion.php">Réserver</a></li>
        </ul>
    </nav>
</header>

<main class="conteneur-connexion">
    <section class="carte-connexion">
        <h2 class="titre-page">Créer un compte</h2>

        <?php if (!empty($erreur)): ?>
            <div class="message-erreur">
                <?php echo $erreur; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="champ-formulaire">
                <label class="intitule">Nom</label>
                <input type="text" name="nom" class="champ" required>
            </div>
            <div class="champ-formulaire">
                <label class="intitule">Prénom</label>
                <input type="text" name="prenom" class="champ" required>
            </div>
            <div class="champ-formulaire">
                <label class="intitule">Adresse</label>
                <input type="text" name="adresse" class="champ" required>
            </div>
            <div class="champ-formulaire">
                <label class="intitule">Complément d’adresse</label>
                <input type="text" name="complement_adresse" class="champ" placeholder="Ex : Code immeuble, étage…">
            </div>
            <div class="champ-formulaire">
                <label class="intitule">Téléphone</label>
                <input type="text" name="tel" class="champ" required>
            </div>
            <div class="champ-formulaire">
                <label class="intitule">Adresse e-mail</label>
                <input type="email" name="mail" class="champ" required>
            </div>
            <div class="champ-formulaire">
                <label class="intitule">Mot de passe</label>
                <input type="password" name="password" class="champ" required>
            </div>
            <div class="champ-formulaire">
                <label class="intitule">Confirmer le mot de passe</label>
                <input type="password" name="confirmer_password" class="champ" required>
            </div>
            <input type="submit" name="inscription" value="S'inscrire" class="bouton-validation">

            <div class="liens-secondaires">
                <a href="connexion.php">Déjà un compte ? Se connecter</a>
            </div>
        </form>
    </section>
</main>
</body>
</html>