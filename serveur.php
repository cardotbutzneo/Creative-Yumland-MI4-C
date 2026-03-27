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
?>
