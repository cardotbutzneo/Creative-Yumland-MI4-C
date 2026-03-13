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

        <form method="post">
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

<?php

require_once __DIR__."/../serveur.php";

function creer_client(array $donnee) : array{
    /** Créer le tableau d'un nouvel utilisateur*/
    $nombre_utilisateur = $donnee;
    $nouveau_nombre = count($nombre_utilisateur) + 1;
    $id = str_pad($nouveau_nombre,8,"0",STR_PAD_LEFT);
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
            "date_creation"=> date("Y-m-d"),
            "derniere_connexion" => date("Y-m-d-H:i:s"),
            "est_banni" => false,
            "est_en_ligne" => false,
            "tentative_echec" => 0
        ]
    ];  
}

if(isset($_POST["inscription"]) && $_POST["password"] == $_POST["confirmer_password"]){

    $bdd_actuelle = lire_data("client.json");
    if (!is_array($bdd_actuelle)) $bdd_actuelle = [];
    $nouveau_client = creer_client($bdd_actuelle);
    if (is_array($nouveau_client)){
        $email = $_POST["mail"];
        if (isset($email)){
            $bdd_actuelle[$email] = $nouveau_client;
            file_put_contents("client.json", json_encode($bdd_actuelle, JSON_PRETTY_PRINT));
        }
    }
}
?>