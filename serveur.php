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

/**
 * Calcule la différence entre deux dates.
 * Par défaut, $date2 est la date actuelle.
 */
function difference_date(string $date1): bool {
    $debut = new DateTime($date1);
    $fin = new DateTime();
    $intervalle = $debut->diff($fin);
    if ($intervalle->days >= 1) {
        return false;
    } else {
        if ($intervalle->h < 1) {
            return true;
        } else {
            return false;
        }
    }
}

function récupérer_commande(string $numéro) : ?array{
    if (!isset($numéro)) return null;
    $data = lire_data("../data/commandes.json");
    if (!isset($data)) return null;
    if (!isset($data[$numéro]["plats"])) return null;
    return $data[$numéro];
}

function calculer_points(int $montant_total, string $pts) : int{
    /**Renvoie le nombre de point du client apres achat */
    /**Exemple : si on dépense 200€ en étant membre or on gagne : (200*200*1.5)/1000 = 60pts */
    if (!isset($montant_total) or !isset($pts)) return -1;
    if ($montant_total <= 0) return -1;
    if ($pts < 500) {$rang = "Amethyste"; $coeff = 1;}
    else if ($pts >= 500 and $pts < 1200) {$rang = "Rubi"; $coeff = 1.2;}
    else {$rang = "Buisson-or"; $coeff = 1.5;}
    $K = 1000; // constante arbitraire
    $pts = (($montant_total**2)*$coeff) / $K;
    return $pts;
}

function ecrire_log(string $msg, string $type = "warning") : void {
    if (empty($msg)) return;

    $colors = [
        "info"     => "\033[33m",
        "warning"  => "\033[38;5;208m",
        "critical" => "\033[31m",
        "reset"    => "\033[0m"
    ];

    $type = strtolower($type);
    $color = $colors[$type] ?? $colors['reset'];
    
    $date = date("Y-m-d H:i:s");
    $format = $color . strtoupper($type) . $colors['reset'] . " [" . $date . "]: " . $msg . PHP_EOL;

    error_log($format, 3, "../securite.log");
}

function est_banni(){
    $bdd_actuelle = lire_data("../data/client.json");
    if (isset($_POST["est_banni"]) && $_POST["est_banni"] == true){
        $bdd_actuelle[$email]["securite"]["est_en_ligne"] = false;
        $bdd_actuelle[$email]["securite"]["remember_token"] = null;
        $bdd_actuelle[$email]["securite"]["remember_token_expiration"] = null;
        ecrire_data("../data/client.json", $bdd_actuelle);
        session_unset();
        session_destroy();
        header("Location: connexion.php");
        exit;
    }
}

function verifier_connexion(string $role, string $role_autoriser){
    if ($role != $role_autoriser and $role != "admin"){
        header("Location: connexion.php?error=unauthorized");
        exit;
    }
}

?>
