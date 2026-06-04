<?php
/**
 * Fichier implémentant la fonctionnalité d'accessibilité pour le site web.
 * Permet aux utilisateurs de personnaliser la taille de la police, la langue et le thème
 * - La taille de la police peut être réglée sur petite (12px), moyenne (16px) ou grande (24px), les polices sont sauvegarder dans un cookie.
 * - La langue peut être choisie entre français et anglais, et est mémorisée dans un cookie pour les visites futures.
 * - Un thème spécial "psychedelique" peut être activé pour une expérience visuelle unique.
 */

// On récupère la taille depuis le cookie pour pré-cocher les boutons radio en PHP
$taille_actuelle = $_COOKIE["taille_pref"] ?? "16px";
$lang = $langue ?? "fr";
?>

<style>
    .lang-selector {
        display: flex;
        justify-content: right;
        padding-right: 50px;
        gap: 20px;
        margin-top: 30px;
    }

    .lang-selector a {
        display: inline-block;
        transition: transform 0.2s, opacity 0.2s;
        opacity: 0.5;
    }

    .lang-selector a:hover, 
    .lang-selector a.active {
        opacity: 1;
        transform: scale(1.1);
    }

    .lang-selector img {
        width: 35px;
        height: auto;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }
</style>

<link rel='stylesheet' href='style/graphique.css'>
<script src="../script.js" defer></script>

<button onclick='togleAcc()'>
    <img alt="Logo d'accessibilité" src='style/img/accessibilite.png' class='img-accessibilite'>
</button>

<div class='div-accessibilite' id='div-accessibilite' style='display : none'>
    <script>
        // Applique immédiatement la taille au démarrage pour éviter le clignotement
        document.documentElement.style.setProperty('--global-font-size', '<?= htmlspecialchars($taille_actuelle) ?>'); 

        function trouverTaille(){
            let taille = document.querySelector("input[name='police']:checked").id + "px";
            setCookie("taille_pref", taille, 30); // Assure-toi que setCookie() est dans ton script.js
            document.documentElement.style.setProperty('--global-font-size', taille);
        }
    </script>

    <p class='title-acc'>Accessibilité</p>
    <div class="forms">
        <div class='taille-police'>
            <p>Taille de police</p>
            <form onchange='trouverTaille()'>
                <input type='radio' name='police' id='12' <?= $taille_actuelle === '12px' ? 'checked' : '' ?>>
                <label for='12'>Petite</label>
                
                <input type='radio' name='police' id='16' <?= $taille_actuelle === '16px' ? 'checked' : '' ?>>
                <label for='16'>Moyenne</label>
                
                <input type='radio' name='police' id='24' <?= $taille_actuelle === '24px' ? 'checked' : '' ?>>
                <label for='24'>Grande</label>
            </form>
        </div>

        <div class="taille-police">
            <p>Langue</p>
            <div class="lang-selector">
                <a href="?lang=fr" class="<?= $lang === 'fr' ? 'active' : '' ?>" onclick="changerLangue('fr')">
                    <img src="https://purecatamphetamine.github.io/country-flag-icons/3x2/FR.svg" alt="Français">
                </a>
                <a href="?lang=en" class="<?= $lang === 'en' ? 'active' : '' ?>" onclick="changerLangue('en')">
                    <img src="https://purecatamphetamine.github.io/country-flag-icons/3x2/GB.svg" alt="English">
                </a>
            </div>
        </div>

        <div class="theme">
            <p>Wanna see something special ?</p>
            <button class="btn-accessibilite" onclick="changerTheme()">Click here</button>
        </div>
    </div>
</div>

<script>
    /** Affiche / Masque le menu */
    function togleAcc(){
        let doc = document.getElementById('div-accessibilite');
        doc.style.display = (doc.style.display === 'none') ? 'grid' : 'none';
    }

    /** Change le thème */
    function changerTheme(){
        document.body.classList.toggle('psychedelique');
    }

    /** Enregistre la langue passée en argument AVANT que le lien ne s'exécute */
    function changerLangue(langueVoulue){
        setCookie('langue', langueVoulue, 30);
    }
</script>