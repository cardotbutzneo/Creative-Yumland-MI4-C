<?php
session_start();

require_once __DIR__."/../serveur.php";

if(!isset($_SESSION["connecte"]) or $_SESSION["role"] != "admin"){
    header("Location: profil_client.php?error=unauthorized");
    exit;
}

$data = lire_data("client.json");


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['role'])) {
    $mail = $_POST['nom_utilisateur'];
    $nouveauRole = $_POST['role'];  

    if (changer_role($mail, $nouveauRole)) {
        // Succès : on rafraîchit la page pour voir les changements
        header("Location: " . $_SERVER['PHP_SELF']); 
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/profil_admin.css">
    <title>Profil Admin - L’oro di Cicerone</title>
</head>
<body>
    <header>
        <a href="index.php"><h1>L’oro di Cicerone</h1></a>
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
            <input type="text" name="search" placeholder="Saisir un Identifiant">
            <input type="submit" id="search-button">
            <table>
                <tr>
                    <td>Selection</td>
                    <th>Nom d'utilisateur</th>
                    <th>Identifiant</th>
                    <th>Statut</th>
                    <th>Dernière date de connexion</th>
                    <th>Bloquer</th>
                </tr>
                <?php 
                foreach ($data as $client => $info){
                    $i = $info["id"];
                    $roleActuel = $info["role"];
                    if ($roleActuel == "admin") $ref = "profil_admin.php";
                    if ($roleActuel == "Client") $ref = "profil_client.php";
                    if ($roleActuel == "Cuisinier") $ref = "commandes.php";
                    if ($roleActuel == "livreur") $ref = "livraison.php";
                    echo"
                        <tr>
                            <form method='POST'>
                                <td class='check' ><input type='checkbox' id='myCheckbox'><label for='myCheckbox'></label></td>
                                <td><input type='hidden' name='nom_utilisateur' value=" . $client . "><a href=".$ref.">" . $client . "</a></td>
                                <td>" . $i . "<input type='hidden' name='id_utilisateur' value=" . $i . "></td>
                                <td>   
                                    <select name='role'>
                                        <option value='Client' " . ($roleActuel == 'Client' ? 'selected' : '') . ">Client</option>
                                        <option value='livreur' " . ($roleActuel == 'livreur' ? 'selected' : '') . ">Livreur</option>
                                        <option value='Cuisinier' " . ($roleActuel == 'Cuisinier' ? 'selected' : '') . ">Cuisinier</option>
                                        <option value='admin' " . ($roleActuel == 'admin' ? 'selected' : '') . ">Administrateur</option>
                                    </select>
                                    <input type='submit'>
                                </td>
                                <td>" . $info["securite"]["derniere_connexion"] . "</td>
                                </form>
                                <td><form class='bloquer'><input type='submit' value='Bloquer'></form></td>
                        </tr>";
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
    $data = lire_data("client.json");
    if (!isset($data[$mail_utilisateur])) return false; // on retourne rien si l'utilisateur n'est pas trouvé

    if (isset($data[$mail_utilisateur]["parametre"]["est_modifiable"]) and $data[$mail_utilisateur]["parametre"]["est_modifiable"] == false) return false; // si le profil n'est pas modifiable (profil de secours) on ne modifie rien
    $data[$mail_utilisateur]["role"] = $nouveau_role; // on change le role de l'utilisateur

    $nouvelle_data = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents("client.json",$nouvelle_data);
    return true;
}

function afficher_info(string $mail_utilisateur) : void{
    $data = lire_data("client.json");
    $utilisateur = $data[$mail_utilisateur];
    if ($utilisateur == null) return;
    echo "donnée lu <br>";
    echo "nom utilisateur : ".$utilisateur["nom"]."<br>";
    //echo "id utilisateur :".$utilisateur["id"]"<br>"; // n'existe pas
    echo "role : ".$utilisateur["role"]."<br>";
}
/*
function bloquer(string $mail) : void{
    if (!isset($mail)) return

}
*/
?>