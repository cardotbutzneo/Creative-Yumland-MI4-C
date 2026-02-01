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

if ($user['password'] == $password){
    switch ($user['role']) {
    case 'cust':
        header("Location: /html/profil_client.html");
        exit;
        break;
    case 'seller' :
        header("Location: /html/commandes.html");
        exit;
        break;
    case 'delivery' :
        header("Location: /html/livraison.html");
        exit;
        break;
    default:
        header("Location: /html/connexion.html");
        exit;
        break;
    }
}

else{
    header("Location: /html/connexion");
    exit;
}


?>
