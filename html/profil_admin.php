<?php
require_once __DIR__."/../api/config.php";

verifier_connexion($role, "admin");

if (isset($_POST["banni"])) {
    $user = $_POST["mail"];
    $raison = $_POST["raison"] ?? "";
    // On détermine si on banni (true) ou débloque (false)
    $estBanni = ($_POST['action_type'] === 'Bloquer'); 
    
    bloquer($user, $raison, $estBanni);
    ecrire_log("L'utilisateur " . $data_client[$user]["prenom"] . " " . $data_client[$user]["nom"] . (($estBanni) ? " est banni" : " est débanni"), "info");
}

if (isset($_POST["nvRole"])){
    $user = $_POST["mail"];     
    $nvRole = $_POST["nvRole"];
    changer_role($user,$nvRole);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/profil_admin.css">
    <link rel="stylesheet" href="style/notification.css">
    <script src="../script.js" defer></script>
    <title>Profil Admin - L'oro di Cicerone</title>
</head>
<body>
    <script>
        function chercherUtilisateur(id){
            if (id.length <= 0) {
                const rows = document.querySelectorAll(".utilisateur");
                rows.forEach(row => row.style.display = "");
                return;
            }
            const rows = document.querySelectorAll(".utilisateur");
            rows.forEach(row => {
                if (row.dataset.mail === id) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        async function get_id() {
            try {
                const reponse = await fetch("../api/get_client.php", {
                    method: "POST",
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await reponse.json();
                return data;
            } catch (e) {
                console.error("Erreur fetch:", e);
                return {}; // Retourne un objet vide en cas d'erreur
            }
        }

        function afficher_dataListe(dataId) {
            if (!dataId || Object.keys(dataId).length === 0) return;
            
            const dataListe = document.getElementById("data-recherche");
            dataListe.innerHTML = "";

            Object.keys(dataId).forEach(id => {
                const option = document.createElement('option');
                option.value = id;
                dataListe.appendChild(option);
            });
        }

        async function getLog(){
            try {
                const response = await fetch("../api/get_log.php", {
                    method: "POST",
                    headers: { 'Content-Type': 'application/json' }
                });

                if (!response.ok) {
                    console.error(`Erreur serveur : Statut ${response.status}`);
                    return null;
                }

                const json = await response.json();
                
                if (json.success === true){    
                    return json.data; // Renvoie directement le tableau de lignes
                }
                return null;
            }
            catch (e){
                console.error("Erreur lors de la récupération des logs : " + e);
                return null;
            }
        }

        function ansiToHtml(input) {
            let text = input
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;");
            text = text.replace(/\x1b\[0m/g, '</span>');

            text = text.replace(/\x1b\[38;5;208m/g, '<span style="color: #ff8700; font-weight: bold;">');

            text = text.replace(/\x1b\[33m/g, '<span style="color: #ffcc00; font-weight: bold;">');

            text = text.replace(/\x1b\[31m/g, '<span style="color: #ff4d4d; font-weight: bold;">');
            return text;
        }

        function creerLog(logs){
            if (logs === null || !Array.isArray(logs)){
                console.error('Impossible de charger les logs ou format invalide');
                return;
            }
            
            const conteneur = document.getElementById("logs-display");
            if (conteneur === null){
                console.error("Erreur : Le conteneur #logs-display n'existe pas dans le DOM");
                return;
            }
            
            conteneur.innerHTML = ""; // On vide le conteneur 
            
            logs.forEach(message => {
                const p = document.createElement("p");
                p.innerHTML = ansiToHtml(message);
                conteneur.appendChild(p);
            });
        }
        
        function afficher(id, button){
            if (id === "") return;
            const userElement = document.getElementById("user-display");
            const logElement = document.getElementById("logs-display");

            const btnUser = document.getElementById("user");
            const btnLogs = document.getElementById("logs");

            if (id === "user-display") { // gestion de l'affichage de la selection
                userElement.style.display = "block";
                logElement.style.display = "none";
            }
            else {
                logElement.style.display = "block";
                userElement.style.display = "none";
            }
            btnUser.classList.remove("check-button");
            btnLogs.classList.remove("check-button");

            // On l'ajoute uniquement sur le bouton qui vient d'être cliqué
            button.classList.add("check-button");
        }

        async function init() {
            var id_client = await get_id();
            afficher_dataListe(id_client);
        }

        async function init_logs(){
            var log = await getLog();
            creerLog(log);
        }

        init();
        init_logs();
        setInterval(init_logs,10000); // on vérifie les nouveaux logs tous les 10s

    </script>
    <header>
        <a href="index.php"><h1>L'oro di Cicerone</h1></a>
        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="deconnexion.php">se déconnecter</a></li>
            </ul>
        </nav>
    </header>
    <div class="notification" id="notification" style="display : none">
        <div class="notification-header">
            <span class="notification-titre">Notification</span>
            <button class="notification-close" onclick="this.closest('.notification').style.display='none'">✕</button>
        </div>
        <p class="notification-body">
            Désolé l'utilisateur saisi n'a pas pu être atteint.
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
    <h1>Page d'administration</h1>
    <div id="button-display">
        <button id="user" onclick="afficher('user-display', this)">Utilisateurs</button>
        <button id="logs" onclick="afficher('logs-display', this)">Logs</button>
    </div>
    <div class="box">
        <section class="table-utilisateur" id="user-display">
            <h2>Utilisateurs</h2>
            <form action="profil_admin.php" method="get">
            <input list="data-recherche" type="text" name="recherche" id="bar-recherche" placeholder="Rechercher un utilisateur" onchange="chercherUtilisateur(this.value)">    
            <datalist id="data-recherche">

            </datalist>
            <button type="button" onclick="document.getElementById('bar-recherche').value = ''; chercherUtilisateur('')">Effacer</button>
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
                foreach ($data_client as $client => $info){
                    $i = $info["id"];
                    $roleActuel = $info["role"];
                    if ($roleActuel == "admin") $ref = "profil_admin.php";
                    if ($roleActuel == "Client") $ref = "profil_client.php";
                    if ($roleActuel == "Cuisinier") $ref = "commandes.php";
                    if ($roleActuel == "livreur") $ref = "livraison.php";
                    if ($info["securite"]["est_banni"] == false) $value = 'Bloquer';
                    else if ($info["securite"]["est_banni"] == true) $value = 'Débloquer';
                    ?>
                        <tr class="utilisateur" data-mail="<?=$client?>">
                            <form method='POST'>
                                <td><input type='hidden' name='nom_utilisateur' value=<?=$client?>><a href=<?=$ref . "?id=" . $client?>><?=$client?></a></td>
                                <td> <?= $i ?><input type='hidden' name='id_utilisateur' value= <?=$i?>></td>
                                <td>   
                                    <select name='role' id="role" onchange="changerRole('<?= $client?>',this)">
                                        <option value='Client' <?= ($roleActuel == 'Client' ? 'selected' : '') ?>>Client</option>
                                        <option value='livreur' <?= ($roleActuel == 'livreur' ? 'selected' : '') ?>>Livreur</option>
                                        <option value='Cuisinier' <?= ($roleActuel == 'Cuisinier' ? 'selected' : '') ?>>Cuisinier</option>
                                        <option value='admin' <?= ($roleActuel == 'admin' ? 'selected' : '') ?>>Administrateur</option>
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