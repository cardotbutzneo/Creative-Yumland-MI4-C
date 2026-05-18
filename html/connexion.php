<?php
session_start();

if (!isset($_SESSION["connecte"]) && isset($_COOKIE["remember_token"])) {
    require_once __DIR__ . "/../serveur.php";
    $bdd = lire_data("../data/client.json");
    $token_recu = $_COOKIE["remember_token"];

    foreach ($bdd as $email => $utilisateur) {
        $token_stocke = $utilisateur["securite"]["remember_token"] ?? null;
        $token_expiration = $utilisateur["securite"]["remember_token_expiration"] ?? 0;

        if ($token_stocke && hash_equals($token_stocke, hash("sha256", $token_recu)) && $token_expiration > time() && !$utilisateur["securite"]["est_banni"]) {
            $_SESSION["email"] = $email;
            $_SESSION["connecte"] = true;
            $_SESSION["role"] = $bdd_actuelle[$email]["role"];
            $_SESSION["nom"] = $bdd_actuelle[$email]["nom"];
            $_SESSION["prenom"] = $bdd_actuelle[$email]["prenom"];
            $_SESSION["pts-fidelite"] = $bdd_actuelle[$email]["pts-fidelite"];
            $_SESSION["total-fidelite"] = $bdd_actuelle[$email]["total-fidelite"];
            $_SESSION["derniers-plats"] = $bdd_actuelle[$email]["dernieres_commandes"] ?? [];
            $_SESSION["derniere-connexion"] = time();
            break;
        }
    }
}

if (isset($_SESSION["connecte"]) && $_SESSION["connecte"] === true) {
    if ($_SESSION["role"] == "Client") {
        header("Location: profil_client.php");
        exit;
    } else if ($_SESSION["role"] == "Cuisinier") {
        header("Location: commandes.php");
        exit;
    } else if ($_SESSION["role"] == "admin") {
        header("Location: profil_admin.php");
        exit;
    } else {
        header("Location: livraison.php");
        exit;
    }
}

require_once __DIR__ . "/../serveur.php";

$erreur = "";

if (isset($_POST["connexion"])) {
    $bdd_actuelle = lire_data("../data/client.json");
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
                $_SESSION["nom"] = $bdd_actuelle[$email]["nom"];
                $_SESSION["prenom"] = $bdd_actuelle[$email]["prenom"];
                $_SESSION["pts-fidelite"] = $bdd_actuelle[$email]["pts-fidelite"];
                $_SESSION["total-fidelite"] = $bdd_actuelle[$email]["total-fidelite"];
                $_SESSION["derniers-plats"] = $bdd_actuelle[$email]["dernieres_commandes"] ?? [];
                $_SESSION["derniere-connexion"] = time();

                $bdd_actuelle[$email]["securite"]["derniere_connexion"] = date("Y-m-d H:i:s");
                $bdd_actuelle[$email]["securite"]["est_en_ligne"] = true;
                $bdd_actuelle[$email]["securite"]["tentative_echec"] = 0;

                if (isset($_POST["remember_me"])) {
                    $token = bin2hex(random_bytes(32));
                    $expiration = time() + (24 * 60 * 60);

                    setcookie("remember_token", $token, [
                        "expires" => $expiration,
                        "path" => "/",
                        "httponly" => true,
                        "samesite" => "Strict"
                    ]);

                    $bdd_actuelle[$email]["securite"]["remember_token"] = hash("sha256", $token);
                    $bdd_actuelle[$email]["securite"]["remember_token_expiration"] = $expiration;
                }

                ecrire_data("../data/client.json", $bdd_actuelle);

                if ($_SESSION["role"] == "Client") {
                    header("Location: profil_client.php");
                    exit;
                } elseif ($_SESSION["role"] == "Cuisinier") {
                    header("Location: commandes.php");
                    exit;
                } elseif ($_SESSION["role"] == "livreur") {
                    header("Location: livraison.php");
                    exit;
                } elseif ($_SESSION["role"] == "admin") {
                    header("Location: profil_admin.php");
                    exit;
                }
            } else {
                $bdd_actuelle[$email]["securite"]["tentative_echec"]++;
                ecrire_data("../data/client.json", $bdd_actuelle);
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
    <title>Connexion - L'oro di Cicerone</title>
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
        <h2 class="titre-page">Connexion</h2>

        <?php if (!empty($erreur)) { ?>
            <div class="message-erreur">
                <?php echo $erreur; ?>
            </div>
        <?php } ?>

        <form method="post" action="">
            <div class="champ-formulaire">
                <label class="intitule">Adresse e-mail</label>
                <input type="email" name="email" class="champ" required>
            </div>
            <div class="champ-formulaire">
                <label class="intitule">Mot de passe</label>
                <input type="password" name="password" class="champ" required>
            </div>
            <div class="champ-formulaire">
                <label class="intitule">
                    <input type="checkbox" name="remember_me"> Se souvenir de moi
                </label>
            </div>
            <input type="submit" name="connexion" value="Se connecter" class="bouton-validation">
            <div class="liens-secondaires">
                <a href="inscription.php">Créer un compte</a>
            </div>
        </form>
    </section>
</main>

<script src="../javascript/connexion.js"></script>
<script>
    document.querySelector("form").addEventListener("submit", validerConnexion);
</script>

</body>
</html>