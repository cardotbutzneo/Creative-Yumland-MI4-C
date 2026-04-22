<?php session_start();

require_once __DIR__."/../serveur.php";

if (!isset($_SESSION["connecte"]) or ($_SESSION["role"] != "Client" and $_SESSION["role"] != "admin")){
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
    <script src="../script.js" defer></script>
    <script src="../javascript/formulaire.js" defer></script>
</head>
<body>
    <header>
    <a href="index.php"><h1>L'oro di Cicerone</h1></a>
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
                    <label class="intitule">Complément d'adresse</label>
                    <input type="text" name="complement_adresse" class="champ" placeholder="Ex : Code immeuble, étage…">
                </div>
                <div class="champ-formulaire">
                    <label class="intitule">Téléphone</label>
                    <select name="pays" id="pays" onchange="changerPrefixTel()">
                            <option value="+33">France (+33)</option>
                            <option value="+32">Belgique (+32)</option>
                            <option value="+49">Alemagne (+49) </option>
                            <option value="+44">Angleterre (+44) </option>
                            <option value="+1">Etats Unis (+1) </option>
                    </select>
                    <input type="text" name="tel" class="champ" id="telephone" value="+33 " onsubmit="verifierTelephone()">
                    <button type='submit' name='valider_modifs' class='bouton-validation' onclick="toogleNotification()">Enregistrer</button>
                </div>

                <div class="">
                    <input type="checkbox" id="abandon" onchange="toggleAbandon()">
                    <label for="abandon" onchange="">Abandonner les modifications</label>
                    <a href="profil_client.php" id="lien-abandon" onclick="alert('Vos modifications vont être effacées')">Revenir au profil</a>
                </div>
            </form>
            <p style="font-size : smaller; color : white;" class="message-erreur">Une <span class="obligatoire">* </span>signifie un champ obligatoire</p>
        </section>
    </main> 
</body>
</html>
<style>
    .bouton-validation{
        margin-top : 20px;
        margin-bottom : 10px;
    }
    .alerte-abandon{
        padding : 5px;
    }
    #lien-abandon{
        text-align : center;
        display : none;
    }
    .liens-secondaires{
        margin-top : 10px;
    }
    #pays{
        width: 20%;
        border : 1px black solid;
        border-radius : 5px;
    }
    @media screen and (max-width: 720px) {
        #pays{
            width: 100%;
        }
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
            $_SESSION[$var] = htmlspecialchars($_POST[$var]);
        }
    }
    ecrire_data("../data/client.json",$toute_la_data);
}

?>