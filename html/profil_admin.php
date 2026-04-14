<?php
session_start();

require_once __DIR__."/../serveur.php";

if(!isset($_SESSION["connecte"]) or $_SESSION["role"] != "admin"){
    header("Location: profil_client.php?error=unauthorized");
    exit;
}

// On attrape le JSON envoyé par Fetch
$json = file_get_contents('php://input');
$datajs = json_decode($json, true);

if ($datajs && isset($datajs['action']) && $datajs['action'] === "bloquer_user") {
    $passwordSaisi = $datajs['password'];
    $mailCible = $datajs['mail'];
    $nouvelEtat = $datajs['nouvelEtat'];

    // On récupère le hash de l'admin (celui qui est connecté)
    $data_client = lire_data("../data/client.json");
    $hashAdmin = $data_client[$_SESSION["email"]]["mot de passe"]; 

    if (password_verify($passwordSaisi, $hashAdmin)) {
        // Le mot de passe est bon, on bloque/débloque
        if (bloquer($mailCible, $nouvelEtat)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erreur écriture JSON']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Mot de passe incorrect']);
    }
    exit; // Crucial : on arrête le script ici pour ne pas envoyer le HTML de la page !
}

$data = lire_data("../data/client.json");


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['role'])) {
    $mail = $_POST['nom_utilisateur'];
    $nouveauRole = $_POST['role'];  

    if (changer_role($mail, $nouveauRole)) {
        // Succès : on rafraîchit la page pour voir les changements
        header("Location: " . $_SERVER['PHP_SELF']); 
        exit();
    }
    
}

if (isset($_POST["bloquer"])){
    $val = explode("|", $_POST["mail"]);
    $mail = $val[0];
    $actionBanir = !($val[1] == "1"); 

    // On récupère le mot de passe envoyé par le JS
    $datajs = json_decode(file_get_contents('php://input'), true);
    $passwordSaisi = $datajs['password'];

    // Ton hash stocké en base de données (exemple)
    $data_client = lire_data("../data/client.json");
    $hashEnBDD = $data_client[$_SESSION["email"]]["mot de passe"]; 

    if (password_verify($passwordSaisi, $hashEnBDD)) {
        echo json_encode(['success' => true]);
        
    } else {
        echo json_encode(['success' => false]);
    }
}
$recherche = $_GET['recherche'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/profil_admin.css">
    <script src="../script.js" defer></script>
    <title>Profil Admin - L'oro di Cicerone</title>
</head>
<body>
    <header>
        <a href="index.php"><h1>L'oro di Cicerone</h1></a>
        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="deconnexion.php">se déconnecter</a></li>
            </ul>
        </nav>
    </header>
    <h1>Page d'administration</h1>
    <div class="grid">
        <section class="graphique">
            <h2>Graphique</h2>
            <p>In progress, Brick by Boring Brick. Awaiting the JS...</p>
        </section>
        <section class="table-utilisateur">
            <h2>Utilisateurs</h2>
            <form action="profil_admin.php" method="get">
            <input type="text" name="recherche" id="bar-recherche" placeholder="Rechercher un utilisateur" value="<?php echo htmlspecialchars($recherche); ?>">    
            <button type="submit">Rechercher</button>
            </form> 
            <table>
                <tr>
                    <th>Nom d'utilisateur</th>
                    <th>Identifiant</th>
                    <th>Statut</th>
                    <th>Dernière date de connexion</th>
                    <th>Bloquer</th>
                </tr>
                <?php 
                $recherche1 = strtolower($recherche);
                foreach ($data as $client => $info){
                    $i = $info["id"];
                    $roleActuel = $info["role"];
                    if ($recherche1 !== '' && strpos(strtolower($client), $recherche1) === false) continue;
                    if ($roleActuel == "admin") $ref = "profil_admin.php";
                    if ($roleActuel == "Client") $ref = "profil_client.php";
                    if ($roleActuel == "Cuisinier") $ref = "commandes.php";
                    if ($roleActuel == "livreur") $ref = "livraison.php";
                    if ($info["securite"]["est_banni"] == false) $value = 'Bloquer';
                    else if ($info["securite"]["est_banni"] == true) $value = 'Débloquer';
                    ?>
                        <tr>
                            <form method='POST'>
                                <td><input type='hidden' name='nom_utilisateur' value=<?=$client?>><a href=<?=$ref?>><?=$client?></a></td>
                                <td> <?= $i ?><input type='hidden' name='id_utilisateur' value= <?=$i?>></td>
                                <td>   
                                    <select name='role'>
                                        <option value='Client' <?= ($roleActuel == 'Client' ? 'selected' : '') ?>>Client</option>
                                        <option value='livreur' <?= ($roleActuel == 'livreur' ? 'selected' : '') ?>>Livreur</option>
                                        <option value='Cuisinier' <?= ($roleActuel == 'Cuisinier' ? 'selected' : '') ?>>Cuisinier</option>
                                        <option value='admin' <?= ($roleActuel == 'admin' ? 'selected' : '') ?>>Administrateur</option>
                                    </select>
                                    <input type='submit' class='bouton-role'>
                                </td>
                                <td> <?= $info["securite"]["derniere_connexion"] ?></td>
                                </form>
                                <td>
                                    <button type='button' class='bloquer'
                                            onclick="demanderValidation('<?php echo $info['contact']['adresse email']; ?>', <?php echo $info['securite']['est_banni'] ? 'true' : 'false'; ?>)">
                                        <?php echo $info["securite"]["est_banni"] ? 'Débloquer' : 'Bloquer'; ?>
                                    </button>
                                </td>
                        </tr>
                    <?php
                }
                ?>
            </table>
        </section>
    </div>
</body>
</html>

<?php


/**
 * Change le rôle d'un utilisateur et sauvegarde dans le fichier JSON.
 * Vérifie également si l'utilisateur actuel a les droits admin.
*/
function changer_role(string $mail_utilisateur, string $nouveau_role) : bool{

    if (empty($mail_utilisateur) or empty($nouveau_role)) return false;
    if (!isset($_SESSION) or $_SESSION["role"] !== "admin") return false;
    $data = lire_data("../data/client.json");
    if (!isset($data[$mail_utilisateur])) return false; // on retourne rien si l'utilisateur n'est pas trouvé

    if (isset($data[$mail_utilisateur]["parametre"]["est_modifiable"]) and $data[$mail_utilisateur]["parametre"]["est_modifiable"] == false) return false; // si le profil n'est pas modifiable (profil de secours) on ne modifie rien
    $data[$mail_utilisateur]["role"] = $nouveau_role; // on change le role de l'utilisateur

    $nouvelle_data = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents("../data/client.json",$nouvelle_data);
    return true;
}

function afficher_info(string $mail_utilisateur) : void{
    $data = lire_data("../data/client.json");
    $utilisateur = $data[$mail_utilisateur];
    if ($utilisateur == null) return;
    echo "donnée lu <br>";
    echo "nom utilisateur : ".$utilisateur["nom"]."<br>";
    //echo "id utilisateur :".$utilisateur["id"]"<br>"; // n'existe pas
    echo "role : ".$utilisateur["role"]."<br>";
}

function bloquer(string $mail, bool $banir = true) : bool {
    if (empty($mail)) return false;
    $path = "../data/client.json";
    $data = lire_data($path);

    if (!$data || !isset($data[$mail])) return false;

    $data[$mail]["securite"]["est_banni"] = $banir;

    return ecrire_data($path, $data);
}
?>