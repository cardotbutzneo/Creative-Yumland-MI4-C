<?php
session_start();

require_once __DIR__."/../serveur.php";

if(!isset($_SESSION["connecte"]) or ($_SESSION["role"] != "Client")){
    header("Location: index.php?error=unauthorized");
    exit;
}

$email_client = $_SESSION["email"];
$bdd_client = lire_data("../data/client.json", $email_client);

$derniere_cmd = $bdd_client["dernieres_commandes"][0];
$bdd_cmd = lire_data("../data/commandes.json");

if (isset($_POST["valider"])) {
    $note_livraison = $_POST["note_livraison"];
    $note_produits = $_POST["note_produits"];

    $bdd_cmd[$derniere_cmd]["note_livraison"] = $note_livraison;
    $bdd_cmd[$derniere_cmd]["note_produits"] = $note_produits;
    if(isset($_POST["commentaires"])){
        $commentaires = $_POST["commentaires"];
        $bdd_cmd[$derniere_cmd]["commentaire"] = $commentaires;
    }
    $bdd_cmd[$derniere_cmd]["etat"] = "notee";

    ecrire_data("../data/commandes.json", $bdd_cmd);
}

// Normaliser les plats (gestion des deux formats : tableau ou objet associatif)
$plats = $bdd_cmd[$derniere_cmd]["plats"];
$plats_liste = [];
if (array_is_list($plats)) {
    foreach ($plats as $plat) {
        $plats_liste[] = ["nom" => $plat["nom"], "quantite" => $plat["quantite"]];
    }
} else {
    foreach ($plats as $plat) {
        $plats_liste[] = ["nom" => $plat["nom"], "quantite" => $plat["quantite"]];
    }
}
$montant = $bdd_cmd[$derniere_cmd]["montant"];
$date_cmd = $bdd_cmd[$derniere_cmd]["date"];
$numero = $bdd_cmd[$derniere_cmd]["numero"] ?? "-";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notation - L'oro di Cicerone</title>
    <link href="https://fonts.googleapis.com/css2?family=Monsieur+La+Doulaise&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/notation.css">
    <script src="../javascript/inscription.js" defer></script>
</head>
<body>
    <header>
        <h1>L'oro di Cicerone</h1>
    </header>
    
    <main>
        <?php if($bdd_cmd[$derniere_cmd]["etat"] === "livree") { ?>
            <div class="bulle">
                <h1>Évaluer votre expérience</h1>

                <div class="recap-commande">
                    <h2>Votre commande</h2>
                    <div class="recap-meta">
                        <span>Commande n° <?= htmlspecialchars($numero) ?></span>
                        <span><?= htmlspecialchars(date("d/m/Y", strtotime($date_cmd))) ?></span>
                    </div>
                    <ul class="recap-plats">
                        <?php foreach ($plats_liste as $plat){ ?>
                            <li>
                                <span class="plat-nom"><?= htmlspecialchars($plat["nom"]) ?></span>
                                <span class="plat-qte">x <?= (int)$plat["quantite"] ?></span>
                            </li>
                        <?php } ?>
                    </ul>
                    <div class="recap-total">
                        <span>Total</span>
                        <span><?= number_format((float)$montant, 2, ',', ' ') ?> €</span>
                    </div>
                </div>

                <form action="notation.php" method="POST">
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
                        <textarea name="commentaires" id="commentaires" placeholder="Partagez votre expérience" maxlength="500"></textarea>
                        <div>
                            <span id="compteur">0</span> / 500 caractères
                        </div>
                    </div>
                    <div class="button-centre">
                        <input type="submit" name="valider" value="Envoyer mon avis" class="bouton-validation">
                    </div>
                </form>
            </div>
            <footer>
                <p>© 2026 L'oro di Cicerone — Tous droits réservés</p>
            </footer>
        <?php } elseif ($bdd_cmd[$derniere_cmd]["etat"] === "notee") {
            header("Location: remerciement.php");
        } else {
            header("Location: index.php?error=unauthorized");
        } ?>
    </main>
</body>
</html>