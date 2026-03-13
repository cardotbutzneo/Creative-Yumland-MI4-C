<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/authentification.css">
    <title>Réinitialisation du mot de passe - L’oro di Cicerone</title>
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
        <h2 class="titre-page">Mot de passe oublié</h2>
        
        <form method="post" action="../serveur.php">
            <div class="champ-formulaire">
                <label class="intitule">Adresse e-mail</label>
                <input type="email" name="email" class="champ" required>
            </div>
            <input type="submit"
                   value="Réinitialiser le mot de passe"
                   class="bouton-validation">
            <div class="liens-secondaires">
                <a href="connexion.php">Retour à la connexion</a>
            </div>
        </form>
    </section>
</main>
</body>
</html>