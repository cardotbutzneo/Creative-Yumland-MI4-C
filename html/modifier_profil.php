<?php session_start();

require_once __DIR__."/../serveur.php";

if (!isset($_SESSION["connecte"]) or $_SESSION["role"] != "Client" or $_SESSION["role"] != "admin"){
    header("Location: profil_client.php?error=unauthorized");
    exit;
}

$afficher_confirmation = false;

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    if (isset($_POST["demande_abandon"])){
        $afficher_confirmation = true;
    }

    if (isset($_POST["confirm_abandon"]) and isset($_POST["checkbox_ok"])){
        header("Location: profil_client.php");
        exit;
    }
    if (isset($_POST["valider_modifs"])){
        modifier_infos();
        header("Location: profil_client.php?flag=success");
        exit;
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
                <?php 
                    if (!$afficher_confirmation){
                        echo "<div class='alerte-abandon'>
                            <input name='confirmation' type='checkbox' id='conf-modifs' required>
                            <label for='conf-modifs'><span class='obligatoire'>* </span>Confirmer vos modifications</label>
                        </div>

                        <button type='submit' name='valider_modifs' class='bouton-validation'>Enregistrer</button>
                        <br>";
                    }
                ?>
                <hr> <?php if ($afficher_confirmation): ?>
                    <div class="alerte-abandon">
                        <p class="message-erreur">Attention vous allez perdre toutes vos modifications</p>
                        <input type="checkbox" name="checkbox_ok" id="cb" required>
                        <label for="cb"><span class="obligatoire">* </span>Confirmer la perte des modifications</label><br>
                        <button type="submit" name="confirm_abandon" class="bouton-validation">Confirmer l'abandon</button>
                    </div>
                <?php else: ?>
                    <div class="liens-secondaires">
                        <button type="submit" name="demande_abandon" class="bouton-validation">Abandonner les modifications</button>
                    </div>
                <?php endif; ?>
            </form>
            <p style="font-size : smaller; color : white;" class="message-erreur">Une <span class="obligatoire">* </span>signifie un champ obligatoire</p>
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

$afficher_confirmation = false;

function modifier_infos() : void {
    /** Modifie les infos de l'utilisateur */
    $toute_la_data = lire_data("../data/client.json");
    $email = $_SESSION["email"];

    if (!isset($toute_la_data[$email])) return;

    foreach (["nom", "prenom", "adresse", "complement_adresse", "tel"] as $var) {
        if (isset($_POST[$var]) && !empty(trim($_POST[$var]))) {
            $toute_la_data[$email][$var] = htmlspecialchars($_POST[$var]);
        }
    }
    ecrire_data("../data/client.json",$toute_la_data);
}

?>