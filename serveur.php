<?php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = htmlspecialchars($_POST['email'] ?? '');
    $password = htmlspecialchars($_POST['password'] ?? '');
}

$json = file_get_contents("data.json");
$data = json_decode($json, true);

$user = $data[$username];

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
