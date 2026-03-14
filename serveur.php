<?php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = htmlspecialchars($_POST['email'] ?? '');
    $password = htmlspecialchars($_POST['password'] ?? '');
}

$json = file_get_contents("data.json");
$data = json_decode($json, true);

$user = $data[$username];

/*
function add_new_cust(array $data, string $username, string $password): bool
{
    if (empty($username) || empty($password)) {
        return false;
    }

    $new_user = [
        "user_id"  => count($data) + 1,
        "username" => $username,
        "password" => password_hash($password, PASSWORD_DEFAULT),
        "role"     => "cust"
    ];

    $data[] = $new_user;

    $json = json_encode($data, JSON_PRETTY_PRINT);

    if ($json === false) {
        return false;
    }

    file_put_contents("data.json", $json);

    return true;
}
*/

function lire_data(string $chemin, string $nom_utilisateur = "") : array{
    if (!file_exists($chemin)) return [];
    $data = json_decode(file_get_contents($chemin),true);
    if ($data == null) return [];
    if ($nom_utilisateur != ""){
        if (isset($data[$nom_utilisateur])) return $data[$nom_utilisateur];
    }
    return $data;
}

function ecrire_data(string $chemin, array $data) : bool {
    $json_contenu = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if (file_put_contents($chemin, $json_contenu) !== false) {
        return true;
    }
    
    return false;
}

/*
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    if ($user['password'] == $password){
        switch ($user['role']) {
            case 'cust':
                header("Location: /html/profil_client.php");
                exit;
                break;
            case 'seller' :
                header("Location: /html/commandes.php");
                exit;
                break;
            case 'delivery' :
                header("Location: /html/livraison.php");
                exit;
                break;
            default:
                header("Location: /html/connexion.php");
                exit;
                break;
        }
    }

    else{
        header("Location: /html/connexion.php");
        exit;
    }
}

format de la bdd :
{
    adresse mail{
        "id" : ..., (id unique important !!)
        "nom" : ...,
        "prenom" : ...,
        "mot de passe (hashé !)" : ...,
        "contact" : {
            "adresse" : ...,
            "complement adress" : ...,
            "telephone" : ...,
            "adress mail" : ...,
        },
        "role" : ...,
        "parametre" : {
            "taille_police" : "12px",
            "couleur" : "defaut",
            "langue" : "fr"
        },
        "derniers-plats" : {...},
        "securite" : {
            "date_creation" : ...,
            "derniere_connexion" : ...,
            "est_banni" : false,
            "est_en_ligne" : ... (timestemp),
            "tentative_echec" : 0 (max 5)
        }
    }
}
*/
?>
