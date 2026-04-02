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

function calculer_points(int $montant_total, string $rang) : int{
    /**Renvoie le nombre de point du client apres achat */
    /**Exemple : si on dépense 200€ en étant membre or on gagne : (200*200*1.5)/1000 = 60pts */
    if (!isset($montant_total) or !isset($rang)) return -1;
    if ($montant_total <= 0) return -1;
    if ($rang === "Amethyste") $coeff = 1;
    if ($rang === "Rubi") $coeff = 1.2;
    if ($rang === "Buisson-or") $coeff = 1.5;
    $K = 1000; // constante arbitraire
    $pts = (($montant_total**2)*$coeff) / $K;
    return $pts;
}

?>
