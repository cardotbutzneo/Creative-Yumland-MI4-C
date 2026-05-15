<?php 

require_once __DIR__."/../api/config.php";

verifier_connexion($role,"Client");

$verif = false;
$verif_mdp = false;
$suppression = false;

if (isset($_POST["password"])){
    $email = $_SESSION["email"];
    if (password_verify($_POST["password"],$data_client["mot de passe"])){
        if (supprimer_compte($email)){
            $verif = true;
            $verif_mdp = true;
            $suppression = true;
            ?><script>console.log(<?= $suppression ?>)</script><?php
        }
        else {
            echo "<p class='message-erreur'>Mot de passe incorrect.</p>";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if ($suppression) echo '<meta http-equiv="refresh" content="5; URL=index.php">'?>
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/authentification.css">
    <script src="../javascript/formulaire.js" defer></script>
    <script src="../script.js" defer></script>
    <title>Sécurité</title>
</head>
<body>
    <header>
        <a href="index.php"><h1>L'oro di Cicerone</h1></a>
        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <?php if (!$suppression) echo '<li><a href="profil_client.php">Revenir au profil</a></li>';?>
            </ul>
        </nav>
    </header>
    <main class="conteneur-connexion">
        <section class="carte-connexion">
            <h2 class="titre-page">Sécurité</h2>
            <form method="post"> 
                <div id="settings" style="display : <?= (!$suppression) ? "block" : "none"; ?>">
                    <div class="champ-formulaire">
                        <button type="submit" name="modif-info" class="champ">Modifier vos informations
                    </div>
                    <div class="champ-formulaire">
                        <button type="submit" name="modif-mdp" class="champ" >Changer votre mot de passe 
                    </div>
                    <div class="champ-formulaire">
                        <button type="submit" name="supp" class="message-erreur" onclick="toggleSecurite(1)">Supprimer le compte
                    </div>
                </div>
                
                <div id="verif" style="display : none">
                    <div class='message-erreur'>Attention cette action sera définitive et vous perdrez votre compte.</div>
                    <div class='champ-formulaire'><button type='submit' name='confirm' class='champ' onclick="toggleSecurite(2)">Confirmer la suppression</div>
                    <div class='champ-formulaire'><button type='submit' name='abandon' class='champ'>Abandonner la suppression</div>
                </div>
                <div id="verif_password" style="display : none">
                    <div class='message-erreur'>Confirmer votre action en rentrant votre mot de passe.</div>
                    <label for="conf-supp"><span class="obligatoire">* </span>Confirmer vos modifications</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" class="champ" required>
                        <button type="button" class="toggle-eye" onclick='togglePassword("password", "oeil_ouvert", "oeil_ferme")' aria-label="Afficher le mot de passe">
                            <img id="oeil_ouvert" src="style/img/oeil_ouvert.png" alt="Afficher">
                            <img id="oeil_ferme" src="style/img/oeil_ferme.png" alt="Masquer" style="display:none;">
                        </button>
                    </div>
                    <div class="champ-formulaire"><button type="submit" name="confirmation" class="champ" onclick="toggleSecurite(3)">Confirmer</div>
                    <p style="font-size : smaller; color : white;" class="message-erreur">Une <span class="obligatoire">* </span>signifie un champ obligatoire</p>
                </div>
                <div id="delete-account" style="display : <?= $suppression ? "block" : "none"; ?>" >
                    <div style="text-align : center">
                        <p>Votre compte a bien été supprimé.</p>
                        <p>En espérant vous revoir !</p>
                        <p>Vous allez être redirigé dans quelsques instants...</p>
                        <a href='index.php'>Cliquez ici si la redirection ne charge pas.</a></div>
                    </div>

                </div>            
            </form>
        </section>
    </main>

</body>
</html>

<?php 

function supprimer_compte(string $email) : bool{
    if (!isset($email)) return false;
    $data = lire_data("../data/client.json");
    $email = $_SESSION["email"];
    if (!isset($data)) return false;
    unset($data[$email]);
    $success = ecrire_data("../data/client.json",$data);
    if ($success){
        session_destroy();
        return true;
    }
    return false;
}

?>