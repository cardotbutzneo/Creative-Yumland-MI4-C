<?php
session_start();

if (isset($_SESSION["connecte"]) && $_SESSION["connecte"] === true) {
    header("Location: profil_client.php");
    exit;
}

require_once __DIR__."/../serveur.php";

$erreur = ""; 

if (isset($_POST["connexion"])) {
    $bdd_actuelle = lire_data("client.json");
    $email = $_POST["email"];
    $mdp = $_POST["password"];

    if (!isset($bdd_actuelle[$email])) {
        $erreur = "Adresse email ou mot de passe incorrect";
    } else {
        $utilisateur = $bdd_actuelle[$email];

        if ($utilisateur["securite"]["est_banni"]) {
            $erreur = "Votre compte est banni.";
        } elseif ($utilisateur["securite"]["tentative_echec"] >= 10) {
            $erreur = "Trop de tentatives échouées. Compte bloqué.";
        } else {
            $hash = $utilisateur["mot de passe"];
            if (password_verify($mdp, $hash)) {
                $_SESSION["email"] = $email;
                $_SESSION["connecte"] = true;
                $_SESSION["role"] = $bdd_actuelle[$email]["role"];
                
                $bdd_actuelle[$email]["securite"]["derniere_connexion"] = date("Y-m-d H:i:s");
                $bdd_actuelle[$email]["securite"]["est_en_ligne"] = true;
                $bdd_actuelle[$email]["securite"]["tentative_echec"] = 0;
                
                ecrire_data("client.json", $bdd_actuelle);
                if ($_SESSION["role"] == "Client"){
                    header("Location: profil_client.php");
                    exit;
                }
                elseif ($_SESSION["role"] == "cuisinier"){
                    header("Location: commandes.php");
                    exit;
                }
                elseif ($_SESSION["role"] == "livreur"){
                    header("Location: livraison.php");
                    exit;
                }
                elseif ($_SESSION["role"] == "admin"){
                    header("Location: profil_admin.php");
                    exit;
                }
            } else {
                $bdd_actuelle[$email]["securite"]["tentative_echec"]++;
                ecrire_data("client.json", $bdd_actuelle);
                $erreur = "Adresse email ou mot de passe incorrect";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/authentification.css">
    <title>Connexion - L’oro di Cicerone</title>
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
        <h2 class="titre-page">Connexion</h2>
        
        <?php if (!empty($erreur)): ?>
            <div class="message-erreur">
                <?php echo $erreur; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="champ-formulaire">
                <label class="intitule"><span class="obligatoire">* </span>Adresse e-mail</label>
                <input type="email" name="email" class="champ" required>
            </div>
            <div class="champ-formulaire">
                <label class="intitule"><span class="obligatoire">* </span>Mot de passe</label>
                <input type="password" name="password" class="champ" required>
            </div>
            <input type="submit" name="connexion" value="Se connecter" class="bouton-validation">
            <div class="liens-secondaires">
                <a href="inscription.php">Créer un compte</a>
                <a href="reset_password.php">Mot de passe oublié ?</a>
            </div>
        </form>
        <p style="font-size : smaller; color : white" class="message-erreur">Une <span class="obligatoire">* </span>signifie un champ obligatoire</p>
    </section>
</main>
</body>
</html>