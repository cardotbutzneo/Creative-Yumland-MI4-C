<?php
require_once __DIR__."/../api/config.php";

verifier_connexion($role, "admin");

// détecte un banissement via POST et modifier la base de données
if (isset($_POST["banni"])) {
    $user = $_POST["mail"];
    $raison = $_POST["raison"] ?? "";
    // On détermine si on banni (true) ou débloque (false)
    $estBanni = ($_POST['action_type'] === 'Bloquer'); 
    
    bloquer($user, $raison, $estBanni);
    ecrire_log("L'utilisateur " . $data_client[$user]["prenom"] . " " . $data_client[$user]["nom"] . (($estBanni) ? " est banni" : " est débanni"), "info");
}

//  identifie un changement de rôle via POST et modifier la base de données
if (isset($_POST["nvRole"])){
    $user = $_POST["mail"];     
    $nvRole = $_POST["nvRole"];
    changer_role($user,$nvRole);
}
?>

<!DOCTYPE html>
<html lang=<?= $isFrench ? "fr" : "en" ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/profil_admin.css">
    <link rel="stylesheet" href="style/notification.css">
    <script src="../script.js" defer></script>
    <script src="../javascript/admin.js" defer></script>
    <title><?= $text["admin"]["title"] ?></title>
</head>
<body>
    <header>
        <a href="index.php"><h1>L'oro di Cicerone</h1></a>
        <nav>
            <ul>
                <li><a href="index.php"> <?= $text["admin"]["home_link"] ?> </a></li>
                <li><a href="deconnexion.php"><?= $text["admin"]["logout_link"] ?></a></li>
            </ul>
        </nav>
    </header>
    <div class="notification" id="notification" style="display : none">
        <div class="notification-header">
            <span class="notification-titre">Notification</span>
            <button class="notification-close" onclick="this.closest('.notification').style.display='none'">✕</button>
        </div>
        <p class="notification-body">
            <?= $text["admin"]["notification_placegholder"] ?>
        </p>
        <div class="notification-barre">
            <div class="notification-barre-fill"></div>
        </div>
    </div>
    <?php 
    if (isset($_GET["gt"]) && $_GET["gt"] == "false"){?>
        <script>
            document.getElementById('notification').style.display = "block";
        </script>
    <?php } ?>
    <h1><?= $text["admin"]["subtitle"] ?></h1>
    <div id="button-display">
        <div id="btn">
            <button id="user" onclick="afficher('user-display', this)"><?= $text["admin"]["user-btn"] ?></button>
            <button id="logs" onclick="afficher('logs-display', this)"><?= $text["admin"]["logs-btn"] ?></button>
        </div>
        <div id="log-container" style="display: none;"> 
            <select id="choix-log" class="bar-recherche" placeholder="Trie des logs" onchange="trieLog()">
                <option value="all"><?= $isFrench ? "Tous les logs" : "All logs" ?></option>
                <option value="info"><?= $isFrench ? "Informations (INFO)" : "Informations" ?></option>
                <option value="warning"><?= $isFrench ? "Avertissements (WARNING)" : "Warning" ?></option>
                <option value="critical"><?= $isFrench ? "Critique (CRITICAL)" : "Critical" ?></option>
            </select>
            <button onclick="document.getElementById('choix-log').value = 'all'; trieLog()"><?= $text["admin"]["clear-search-btn"] ?></button>
        </div>
    </div>
    <div class="box">
        <section class="table-utilisateur" id="user-display">
            <h2><?= $text["admin"]["user"] ?></h2>
            <form action="profil_admin.php" method="get">
            <input list="data-recherche" type="text" name="recherche" id="bar-recherche" placeholder='<?= $text["admin"]["placeholder-search"] ?>' onchange="chercherUtilisateur(this.value)">    
            <datalist id="data-recherche">

            </datalist>
            <button type="button" onclick="document.getElementById('bar-recherche').value = ''; chercherUtilisateur('')"><?= $text["admin"]["clear-search-btn"] ?></button>
            </form> 
            <table>
                <tr>
                    <th><?= $text["admin"]["name-placeholder"] ?></th>
                    <th><?= $text["admin"]["ID"] ?></th>
                    <th><?= $text["admin"]["status"] ?></th>
                    <th><?= $text["admin"]["last-login"] ?></th>
                    <th><?= $text["admin"]["actions"]["block"] ?></th>
                </tr>
                <?php 
                foreach ($data_client as $client => $info){
                    $i = $info["id"];
                    $roleActuel = $info["role"];
                    if ($roleActuel == "admin") $ref = "profil_admin.php";
                    if ($roleActuel == "Client") $ref = "profil_client.php";
                    if ($roleActuel == "Cuisinier") $ref = "commandes.php";
                    if ($roleActuel == "livreur") $ref = "livraison.php";
                    if ($info["securite"]["est_banni"] == false) $value = $text["admin"]["actions"]["block"];
                    else if ($info["securite"]["est_banni"] == true) $value = $text["admin"]["actions"]["unblock"];
                    ?>
                        <tr class="utilisateur" data-mail="<?=$client?>">
                            <form method='POST'>
                                <td><input type='hidden' name='nom_utilisateur' value=<?=$client?>><a href=<?=$ref . "?id=" . $client?>><?=$client?></a></td>
                                <td> <?= $i ?><input type='hidden' name='id_utilisateur' value= <?=$i?>></td>
                                <td>   
                                    <select name='role' id="role" onchange="changerRole('<?= $client?>',this)">
                                        <option value='Client' <?= ($roleActuel == 'Client' ? 'selected' : '') ?>><?= $text["admin"]["role"]["Client"] ?></option>
                                        <option value='livreur' <?= ($roleActuel == 'livreur' ? 'selected' : '') ?>><?= $text["admin"]["role"]["delivreur"] ?></option>
                                        <option value='Cuisinier' <?= ($roleActuel == 'Cuisinier' ? 'selected' : '') ?>><?= $text["admin"]["role"]["chef"] ?></option>
                                        <option value='admin' <?= ($roleActuel == 'admin' ? 'selected' : '') ?>><?= $text["admin"]["role"]["Admin"] ?></option>
                                    </select>
                                </td>
                                <td> <?= $info["securite"]["derniere_connexion"] ?></td>
                                
                                <td>
                                        <input type='button' class='bloquer' id="bouton-banir" value=<?= $value ?>
                                            onclick="bannir('<?=$client?>',this.value, this)">
                                        </input>
                                </td>
                            </form>
                        </tr>
                    <?php
                }
                ?>
            </table>
        </section>

        <section id="logs-display" style="display: none;">
            <h2>Logs</h2>
            <div id="log-table">
                <p>Chargement des logs...</p>
            </div>
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
    $data_client = lire_data("../data/client.json");
    if (!isset($data_client[$mail_utilisateur])) return false; // on retourne rien si l'utilisateur n'est pas trouvé

    if (isset($data_client[$mail_utilisateur]["parametre"]["est_modifiable"]) and $data_client[$mail_utilisateur]["parametre"]["est_modifiable"] == false) return false; // si le profil n'est pas modifiable (profil de secours) on ne modifie rien
    ecrire_log("Administration : Changement de rôle de" . $data_client[$mail_utilisateur]["nom"] . " " . $data_client[$mail_utilisateur]["nom"] . " " . "de" .  $data_client[$mail_utilisateur]["role"] . "a" . $nouveau_role);
    $data_client[$mail_utilisateur]["role"] = $nouveau_role; // on change le role de l'utilisateur

    $nouvelle_data = json_encode($data_client, JSON_PRETTY_PRINT);
    
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

/**bloque un utilisateur
 * @param string $mail : l'identifiant de l'utilisateur à bloquer
 * @param string $raison : la raison du blocage (facultatif)
 * @param bool $banir : true pour bloquer, false pour débloquer (
 */
function bloquer(string $mail, string $raison, bool $banir = true) : bool {
    if (empty($mail)) return false;
    $path = "../data/client.json";
    $data = lire_data($path);

    if (!$data || !isset($data[$mail])) return false;

    $data[$mail]["securite"]["est_banni"] = $banir;
    if ($banir) $data[$mail]["securite"]["raison_ban"] = $raison ;
    else unset($data[$mail]["securite"]["raison_ban"]);
    return ecrire_data($path, $data);
}
?>