<?php
date_default_timezone_set('Europe/Paris');

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


function ajouter_commandes(array $panier) : bool{ // panier (ex) : {"burrata" : {"prix" : 15, "quantite" : 3, "option" : ""}, "cafe" : {"prix" : 5, "quantite" : 2, "option" : ""}} -> {"plat" : {"prix","quantite","option}}
    if (!isset($panier) or empty($panier)) return false;
    $data = lire_data("commandes.json");
    if (!isset($data)) return false;
    if (empty($data)) $data = [];
    $total = 0;
    $liste_plat = [];
    $options = [];
    foreach ($panier as $plat => $info){
        $total += $info["prix"] * $info["quantite"];
        $liste_plat[] = $plat;
        $options[] = $plat . ":" . $plat["option"];
    };
    $nouvelle_commandes = [
        $_SESSION["mail"] => [
            "date" => date("Y-m-d H:i:s"),
            "total" => $total,
            "detail" => [
                "plats" => $liste_plat,
                "option" =>  $options
            ]
        ]
    ];
    $nouvelle_data = json_encode($nouvelle_commandes,JSON_PRETTY_PRINT);
    file_put_contents("html/commandes.json",$nouvelle_data);
    return true;
}

?>
