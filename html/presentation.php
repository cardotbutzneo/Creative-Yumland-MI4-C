<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>La Carte – L'oro di Cicerone</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/presentation.css">
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
<main>
    <div class="barre-filtre">
    <input type="text" id="bar-recherche" placeholder="Rechercher un plat...">
    <select id="filtre-carte">
        <option value="tous">Toute la carte</option>
        <option value="entrees">Entrées</option>
        <option value="plats">Plats</option>
        <option value="desserts">Desserts</option>
        <option value="vins">Vins</option>
        <option value="cafes">Cafés</option>
    </select>
    <select id="filtre-regime">
        <option value="tous-regimes">Tous les régimes</option>
        <option value="vege">Végétarien</option>
        <option value="non-vege">Non végétarien</option>
    </select>
    <select id="filtre-allergenes">
        <option value="tous-allergenes">Tous (avec ou sans allergènes)</option>
        <option value="sans-gluten">Sans gluten</option>
        <option value="sans-lactose">Sans lactose</option>
        <option value="sans-fruits-coquilles">Sans fruits à coque</option>
        <option value="sans-allergenes">Sans allergène</option>
    </select>
    </div>
</main>
<footer>
    <p>© 2026 L’oro di Cicerone — Tous droits réservés</p>
    <a href="contact.php">Nous contacter</a>
</footer>
</body>
</html>
