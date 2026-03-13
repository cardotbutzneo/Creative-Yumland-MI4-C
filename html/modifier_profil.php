<?php session_start();

$afficher_confirmation = false;

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    if (isset($_POST["demande_abandon"])){
        $afficher_confirmation = true;
    }

    if (isset($_POST["confirm_abandon"]) and isset($_POST["checkbox_ok"])){
        header("Location: profil_client.php");
        exit();
    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>modifier le profil</title>
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/authentification.css">
</head>
<body>
    <header>
    <a href="index.php"><h1>L’oro di Cicerone</h1></a>
    <nav>
        <ul>
            <li><a href="index.php">Accueil</a></li>
        </ul>
    </nav>
    </header>

    <main class="conteneur-connexion">
        <section class="carte-connexion">
            <h2 class="titre-page">Modifier vos informations</h2>

            <form method="post">
                <div class="champ-formulaire">
                <label class="intitule">Nom</label>
                <input type="text" name="nom" class="champ">
                </div>
                <div class="champ-formulaire">
                    <label class="intitule">Prénom</label>
                    <input type="text" name="prenom" class="champ">
                </div>
                <div class="champ-formulaire">
                    <label class="intitule">Adresse</label>
                    <input type="text" name="adresse" class="champ">
                </div>
                <div class="champ-formulaire">
                    <label class="intitule">Complément d’adresse</label>
                    <input type="text" name="complement_adresse" class="champ" placeholder="Ex : Code immeuble, étage…">
                </div>
                <div class="champ-formulaire">
                    <label class="intitule">Téléphone</label>
                    <input type="text" name="tel" class="champ">
                </div>
                <div class="alerte-abandon">
                    <input name="confirmation" type="checkbox" id="conf-modifs">
                    <label for="conf-modifs">Confirmer vos modifications</label>
                </div>

                <button type="submit" name="valider_modifs" class="bouton-validation">Enregistrer</button>
                <br>
                <hr> <?php if ($afficher_confirmation): ?>
                    <div class="alerte-abandon">
                        <input type="checkbox" name="checkbox_ok" id="cb">
                        <label for="cb">Confirmer la perte des modifications</label><br>
                        <button type="submit" name="confirm_abandon" class="bouton-validation">Confirmer l'abandon</button>
                    </div>
                <?php else: ?>
                    <div class="liens-secondaires">
                        <button type="submit" name="demande_abandon" class="bouton-validation">Abandonner les modifications</button>
                    </div>
                <?php endif; ?>
            </form>
        </section>
    </main> 
</body>
</html>
<style>
    .bouton-validation{
        margin : 0px;
        margin-bottom : 10px;
    }
    .alerte-abandon{
        padding : 5px;
    }
    .liens-secondaires{
        margin-top : 10px;
    }
</style>

<?php 
require_once __DIR__."/../serveur.php";

$afficher_confirmation = false;

function modifier_infos() : bool{
    /**Modifie les infos de l'utilisateur */
    $data = lire_data()[$_SESSION["mail"]];

    foreach (["nom","prenom","adresse","complement_adresse","tel"] as $var){
        if (isset($_POST[$var])){ // si on change les données on les modifies
            $data[$var] = $_POST[$var];
        }
    }
    file_put_contents("client.json",$nouvelle_data); // on sauvegarde la bdd
}


?>