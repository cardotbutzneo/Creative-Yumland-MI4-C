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
    <a href="index.html"><h1>L’oro di Cicerone</h1></a>
    <nav>
        <ul>
            <li><a href="index.html">Accueil</a></li>
            <li><a href="restaurant.html">Le Restaurant</a></li>
            <li><a href="chef.html">Le Chef</a></li>
            <li><a href="presentation.html">Menu</a></li>
            <li><a href="connexion.html">Réserver</a></li>
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
                <a href="connexion.html">Déjà un compte ? Se connecter</a>
            </div>
        </form>
    </section>
</main>
</body>
</html>

<?php

if(isset($_POST["inscription"]) && $_POST["password"] == $_POST["confirmer_password"]){

$data = array(
    "Nom" => $_POST["nom"],
    "Prénom" => $_POST["prenom"],
    "Adresse" => $_POST["adresse"],
    "Complément d'adresse" => $_POST["complement_adresse"],
    "Téléphone" => $_POST["tel"],
    "Adresse email" => $_POST["mail"],
    "Rôle" => "Client",
    "Dernières" => null
);

file_put_contents("client.json", json_encode($data, JSON_PRETTY_PRINT));

}
?>