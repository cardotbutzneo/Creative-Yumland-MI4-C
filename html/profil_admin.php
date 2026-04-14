<?php
session_start();

require_once __DIR__."/../serveur.php";

if(!isset($_SESSION["connecte"]) or $_SESSION["role"] != "admin"){
    header("Location: profil_client.php?error=unauthorized");
    exit;
}

$data = lire_data("../data/client.json");

if (isset($_POST["banni"])) {
    $user = $_POST["mail"];
    $raison = $_POST["raison"] ?? "";
    // On détermine si on banni (true) ou débloque (false)
    $estBanni = ($_POST['action_type'] === 'Bloquer'); 
    
    bloquer($user, $raison, $estBanni);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['role'])) {
    $mail = $_POST['nom_utilisateur'];
    $nouveauRole = $_POST['role'];  

    if (changer_role($mail, $nouveauRole)) {
        // Succès : on rafraîchit la page pour voir les changements
        header("Location: " . $_SERVER['PHP_SELF']); 
        exit();
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
                $data = lire_data("../data/client.json");
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
                                
                                <td>
                                        <input type='button' class='bloquer'
                                            onclick="bannir('<?=$client?>','<?= $value ?>','')" value=<?php echo $info["securite"]["est_banni"] ? 'Débloquer' : 'Bloquer'; ?>>
                                        </input>
                                </td>
                            </form>
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

function bloquer(string $mail, string $raison, bool $banir = true) : bool {
    if (empty($mail)) return false;
    $path = "../data/client.json";
    $data = lire_data($path);

    if (!$data || !isset($data[$mail])) return false;

    $data[$mail]["securite"]["est_banni"] = $banir;

    return ecrire_data($path, $data);
}
?>