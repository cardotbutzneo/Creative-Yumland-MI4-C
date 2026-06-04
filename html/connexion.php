<?php
require_once __DIR__."/../api/config.php";

// Durée avant la réinitialisation des tentatives échouées
// Ici : 15 minutes
$delai_reset = 15 * 60;

// Vérifie si l'utilisateur n'est pas connecté mais possède un cookie de connexion automatique.
if (!isset($_SESSION["connecte"]) && isset($_COOKIE["remember_token"])) {
    require_once __DIR__ . "/../serveur.php";
    $bdd = $data_client;
    $token_recu = $_COOKIE["remember_token"];

    // Parcours de tous les utilisateurs pour retrouver celui qui possède le token correspondant.
    foreach ($bdd as $email => $utilisateur) {
        //recupération du token stocké en base s'il existe
        $token_stocke = $utilisateur["securite"]["remember_token"] ?? null;
        //rcupération de la date d'expiration du token
        $token_expiration = $utilisateur["securite"]["remember_token_expiration"] ?? 0;

        // Vérifie plusieurs conditions :
        // - un token existe en base ;
        // - le token reçu correspond au token stocké après hachage ;
        // - le token n'est pas expiré ;
        // - le compte utilisateur n'est pas banni.
        if (
            $token_stocke &&
            hash_equals($token_stocke, hash("sha256", $token_recu)) &&
            $token_expiration > time() &&
            !$utilisateur["securite"]["est_banni"]
        ) {
            // Si le token est valide, l'utilisateur est reconnecté automatiquement.
            // Les informations principales sont enregistrées dans la session.
            $_SESSION["email"] = $email;
            $_SESSION["connecte"] = true;
            $_SESSION["role"] = $bdd[$email]["role"];
            $_SESSION["nom"] = $bdd[$email]["nom"];
            $_SESSION["prenom"] = $bdd[$email]["prenom"];
            $_SESSION["pts-fidelite"] = $bdd[$email]["pts-fidelite"];
            $_SESSION["total-fidelite"] = $bdd[$email]["total-fidelite"];
            $_SESSION["derniers-plats"] = $bdd[$email]["dernieres_commandes"] ?? [];
            $_SESSION["derniere-connexion"] = time();

            // Arrête la boucle dès que l'utilisateur correspondant est trouvé.
            break;
        }
    }
}

// Si l'utilisateur est déjà connecté, il est redirigé directement vers la page correspondant à son rôle.
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
    $bdd_actuelle = $data_client;
    $email = $_POST["email"];
    $mdp = $_POST["password"];
    //vérifie si l'adresse email existe dans la base.
    if (!isset($bdd_actuelle[$email])) {
        //message volontairement général pour ne pas indiquer si l'email existe ou non
        $erreur = ($isFrench) ? "Adresse email ou mot de passe incorrect" : "Invalid email address or password" ;
    } else {
        //recupération des informations de l'utilisateur correspondant à l'email.
        $utilisateur = $bdd_actuelle[$email];
        //recupération de la dernière tentative de connexion échouée.
        $derniere_tentative = $utilisateur["securite"]["derniere_tentative"] ?? null;
        // Si une dernière tentative existe et que le délai de blocage est dépassé,
        // les tentatives échouées sont remises à zéro.
        if ($derniere_tentative !== null && (time() - strtotime($derniere_tentative)) > $delai_reset) {
            $bdd_actuelle[$email]["securite"]["tentative_echec"] = 0;
            $bdd_actuelle[$email]["securite"]["derniere_tentative"] = null;
            // Mise à jour de la variable locale avec les nouvelles données.
            $utilisateur = $bdd_actuelle[$email];
            // Sauvegarde des modifications dans le fichier JSON.
            ecrire_data("../data/client.json", $bdd_actuelle);
        }

        // Empêche la connexion si le compte est banni.
        if ($utilisateur["securite"]["est_banni"]) {
            $erreur = ($isFrench) ? "Votre compte est banni." : "Your account have been banned";

        // Empêche temporairement la connexion après 5 tentatives échouées.
        } elseif ($utilisateur["securite"]["tentative_echec"] >= 5) {
            $erreur = ($isFrench) ? "Trop de tentatives échouées. Réessayez plus tard." : "Too many failed attempt. Retry later";

        } else {
            // Récupération du mot de passe haché stocké en base.
            $hash = $utilisateur["mot de passe"];
            // Vérifie si le mot de passe saisi correspond au mot de passe haché.
            if (password_verify($mdp, $hash)) {
                // Connexion réussie : enregistrement des informations utilisateur dans la session.
                $_SESSION["email"] = $email;
                $_SESSION["connecte"] = true;
                $_SESSION["role"] = $bdd_actuelle[$email]["role"];
                $_SESSION["nom"] = $bdd_actuelle[$email]["nom"];
                $_SESSION["prenom"] = $bdd_actuelle[$email]["prenom"];
                $_SESSION["pts-fidelite"] = $bdd_actuelle[$email]["pts-fidelite"];
                $_SESSION["total-fidelite"] = $bdd_actuelle[$email]["total-fidelite"];
                $_SESSION["derniers-plats"] = $bdd_actuelle[$email]["dernieres_commandes"] ?? [];
                $_SESSION["derniere-connexion"] = time();
                // Mise à jour des informations de sécurité après une connexion réussie.
                $bdd_actuelle[$email]["securite"]["derniere_connexion"] = date("Y-m-d H:i:s");
                $bdd_actuelle[$email]["securite"]["est_en_ligne"] = true;
                $bdd_actuelle[$email]["securite"]["tentative_echec"] = 0;
                $bdd_actuelle[$email]["securite"]["derniere_tentative"] = null;
                // Si l'utilisateur coche "se souvenir de moi",
                // un token sécurisé est généré et stocké dans un cookie.
                if (isset($_POST["remember_me"])) {
                    // Génère un token aléatoire de 64 caractères hexadécimaux.
                    $token = bin2hex(random_bytes(32));
                    // Définit une expiration de 24 heures.
                    $expiration = time() + (24 * 60 * 60);
                    // Création du cookie contenant le token non haché.
                    // httponly empêche l'accès au cookie via JavaScript.
                    // samesite Strict limite l'envoi du cookie aux requêtes du même site.
                    setcookie("remember_token", $token, [
                        "expires" => $expiration,
                        "path" => "/",
                        "httponly" => true,
                        "samesite" => "Strict"
                    ]);
                    // Le token est haché avant d'être stocké en base.
                    $bdd_actuelle[$email]["securite"]["remember_token"] = hash("sha256", $token);
                    // Sauvegarde de la date d'expiration du token.
                    $bdd_actuelle[$email]["securite"]["remember_token_expiration"] = $expiration;
                }
                // Sauvegarde des modifications de l'utilisateur dans le fichier JSON.
                ecrire_data("../data/client.json", $bdd_actuelle);
                // Redirection de l'utilisateur connecté selon son rôle.
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

                // Mot de passe incorrect :
                // on augmente le compteur de tentatives échouées.
                $bdd_actuelle[$email]["securite"]["tentative_echec"]++;
                // On enregistre la date et l'heure de cette tentative.
                $bdd_actuelle[$email]["securite"]["derniere_tentative"] = date("Y-m-d H:i:s");
                // Sauvegarde des informations de sécurité mises à jour.
                ecrire_data("../data/client.json", $bdd_actuelle);
                // Message d'erreur général pour l'utilisateur.
                $erreur = ($isFrench) ? "Adresse email ou mot de passe incorrect" : "Incorrect email address or password";
                // Écriture d'un log indiquant qu'un mot de passe incorrect a été saisi.
                ecrire_log("Connexion : Mot de passe incorrect de " . $_POST["email"], "info");
                // Si l'utilisateur atteint 5 tentatives échouées,
                // un log d'avertissement est créé.
                if($bdd_actuelle[$email]["securite"]["tentative_echec"] == 5){
                    ecrire_log("Connexion : 5 tentatives échouées de " . $_POST["email"] . ". Compte temporairement bloqué", "warning");
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang=<?= $isFrench ? "fr" : "en" ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/authentification.css">
    <title><?= $text["connexion"]["title"] ?></title>
</head>
<body>

<header>
    <a href="index.php"><h1>L'oro di Cicerone</h1></a>
    <nav>
        <ul>
            <li><a href="index.php"><?php if ($isFrench) echo "Accueil"; else echo "Home page" ?></a></li>
        </ul>
    </nav>
</header>

<main class="conteneur-connexion">
    <section class="carte-connexion">
        <h2 class="titre-page"><?= $text["connexion"]["title-section"] ?></h2>

        <?php if (!empty($erreur)) { ?>
            <div class="message-erreur">
                <?php echo $erreur; ?>
            </div>
        <?php } ?>

        <form method="post" action="">
            <div class="champ-formulaire">
                <label class="intitule" for="email"><?= $text["connexion"]["email-label"] ?></label>
                <input type="email" name="email" id="email" class="champ" required>
            </div>
            <div class="champ-formulaire">
                <label class="intitule" for="password"><?= $text["connexion"]["password-label"] ?></label>
                <input type="password" name="password" id="password" class="champ" required>
            </div>
            <div class="champ-formulaire">
                <label class="intitule">
                    <input type="checkbox" name="remember_me"> <?= $text["connexion"]["remember-me"] ?>
                </label>
            </div>
            <input type="submit" name="connexion" class="bouton-validation" value="<?= $text["connexion"]["submit-button"] ?>">
            <div class="liens-secondaires">
                <a href="inscription.php"><?= $text["connexion"]["create-account-link"] ?></a>
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