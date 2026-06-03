<?php

require_once __DIR__."/../api/config.php";
verifier_connexion($role,"Client",false);

$email_client = $_SESSION["email"];
$bdd_client = lire_data("../data/client.json", $email_client);

$derniere_cmd = $bdd_client["dernieres_commandes"][0] ?? null;
$bdd_cmd = $data_commandes;

if (!$derniere_cmd) {
    header("Location: profil_client.php?err=noOrder");
    exit;
}

$derniere_cmd = strtoupper($derniere_cmd);

if (!isset($bdd_cmd[$derniere_cmd])) {
    header("Location: profil_client.php?err=fetchFailed");
    exit;
}

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

    header("Location: remerciement.php");
    exit;
}

$plats = $bdd_cmd[$derniere_cmd]["plats"] ?? [];

if (!is_array($plats) || empty($plats)) {
    header("Location: profil_client.php?err=fetchFailed");
    exit;
}

$plats_liste = [];

foreach ($plats as $plat) {
    if (!is_array($plat)) continue;

    $plats_liste[] = [
        "nom" => $plat["nom"] ?? "",
        "quantite" => $plat["quantite"] ?? 0
    ];
}

$montant = $bdd_cmd[$derniere_cmd]["montant"] ?? 0;
$date_cmd = $bdd_cmd[$derniere_cmd]["date"] ?? date("Y-m-d");
$numero = $bdd_cmd[$derniere_cmd]["numero"] ?? "-";
?>
<!DOCTYPE html>
<html lang="<?php if ($isFrench) echo "fr"; else echo "en"; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $text["notation"]["title"] ?></title>
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
                <h1><?= $text["notation"]["page_title"] ?></h1>

                <div class="recap-commande">
                    <h2><?= $text["notation"]["your_order"] ?></h2>
                    <div class="recap-meta">
                        <span><?= $text["notation"]["order_number"] ?> <?= htmlspecialchars($numero) ?></span>
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
                        <span><?= $text["notation"]["total"] ?></span>
                        <span><?= number_format((float)$montant, 2, ',', ' ') ?> €</span>
                    </div>
                </div>

                <form action="notation.php" method="POST">
                    <div class="ligne">
                        <span class="intitule"><?= $text["notation"]["delivery_rating"] ?></span>
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
                        <span class="intitule"><?= $text["notation"]["products_rating"] ?></span>
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
                        <div class="intitule"><?= $text["notation"]["comments"] ?></div>
                        <textarea name="commentaires" id="commentaires" placeholder="<?= $text["notation"]["comments_placeholder"] ?>" maxlength="500"></textarea>
                        <div>
                            <span id="compteur">0</span> / 500 <?= $text["notation"]["characters"] ?>
                        </div>
                    </div>
                    <div class="button-centre">
                        <input type="submit" name="valider" value="<?= $text["notation"]["submit"] ?>" class="bouton-validation">
                    </div>
                </form>
            </div>
            <footer>
                <p><?= $text["notation"]["footer_rights"] ?></p>
            </footer>
        <?php } elseif ($bdd_cmd[$derniere_cmd]["etat"] === "notee") {
            header("Location: remerciement.php");
            exit;
        } else {
            header("Location: index.php?error=unauthorized");
            exit;
        } ?>
    </main>
</body>
</html>