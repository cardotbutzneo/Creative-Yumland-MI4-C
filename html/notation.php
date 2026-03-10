<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notation – L'oro di Cicerone</title>
    <link href="https://fonts.googleapis.com/css2?family=Monsieur+La+Doulaise&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/notation.css">
</head>
<body>
    <header>
        <a href="index.html" class="naviagation-bar"><h1>L’oro di Cicerone</h1></a>
        <nav class="naviagation-bar">
        <ul>
            <li><a href="index.html">Accueil</a></li>
            <li><a href="restaurant.html">Le Restaurant</a></li>
            <li><a href="chef.html">Le Chef</a></li>
            <li><a href="presentation.html">Menu</a></li>
            <li><a href="connexion.html">Réserver</a></li>
        </ul>
    </nav>
    </header>
    <main>
        <div class="bulle">
            <h1>Évaluer votre expérience</h1>
            <form action="https://www.cafe-it.fr/cytech/post.php" method="POST">
                <div class="ligne">
                    <span class="intitule">Note de la livraison :</span>
                    <div class="etoiles">
                        <input type="radio" name="note_livraison" value="5" id="l5">
                        <label for="l5">★</label>
                        <input type="radio" name="note_livraison" value="4" id="l4">
                        <label for="l4">★</label>
                        <input type="radio" name="note_livraison" value="3" id="l3">
                        <label for="l3">★</label>
                        <input type="radio" name="note_livraison" value="2" id="l2">
                        <label for="l2">★</label>
                        <input type="radio" name="note_livraison" value="1" id="l1">
                        <label for="l1">★</label>
                    </div>
                </div>
                <div class="ligne">
                    <span class="intitule">Note des produits :</span>
                    <div class="etoiles">
                        <input type="radio" name="note_produits" value="5" id="p5">
                        <label for="p5">★</label>
                        <input type="radio" name="note_produits" value="4" id="p4">
                        <label for="p4">★</label>
                        <input type="radio" name="note_produits" value="3" id="p3">
                        <label for="p3">★</label>
                        <input type="radio" name="note_produits" value="2" id="p2">
                        <label for="p2">★</label>
                        <input type="radio" name="note_produits" value="1" id="p1">
                        <label for="p1">★</label>
                    </div>
                </div>
                <div class="commentaires">
                    <div class="intitule">Commentaires :</div>
                    <textarea name="commentaires" id="commentaires" placeholder="Partagez votre expérience "></textarea>
                </div>
                <div class="button-centre">
                    <button type="submit">Envoyer mon avis</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
