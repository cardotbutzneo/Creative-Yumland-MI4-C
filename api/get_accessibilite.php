<?php 

/**
 * Fichier implémentant la fonctionnalité d'accessibilité pour le site web.
 * Permet aux utilisateurs de personnaliser la taille de la police, la langue et le thème
 * - La taille de la police peut être réglée sur petite (12px), moyenne (16px) ou grande (24px), les polices sont sauvegarder dans un cookie.
 * - La langue peut être choisie entre français et anglais, et est mémorisée dans un cookie pour les visites futures.
 * - Un thème spécial "psychedelique" peut être activé pour une expérience visuelle unique.
 */

?>

<link rel='stylesheet' href='style/graphique.css'>
<script src="../script.js" defer></script>
<button><img alt="Logo d'accessibilité" src='style/img/accessibilite.png' class='img-accessibilite' onclick='togleAcc()'></button>

<div class='div-accessibilite' id='div-accessibilite' style='display : none'>
    <script>
        function getFontSize(value){ // lancement à chaque démarage pour récupérer le cookie
            document.documentElement.style.setProperty('--global-font-size', value); 
        }

        function trouverTaille(){
            let taille = document.querySelector("input[name='police']:checked").id + "px";
            setCookie("taille_pref",taille,30);

            document.documentElement.style.setProperty('--global-font-size', taille);
        }

        getFontSize('<?= htmlspecialchars($_COOKIE["taille_pref"] ?? "16px") ?>');
    </script>
    <p class='title-acc'>Accessibilité</p>
    <div class="forms">
        <ul class='taille-police'>
            <p>Taille de police</p>
            <form onchange='trouverTaille()'>
                <input type='radio' name='police' id='12'>
                <label for='12'>Petite</label>
                <input type='radio' name='police' id='16'>
                <label for='16'>Moyenne</label>
                <input type='radio' name='police' id='24'>
                <label for='24'>Grande</label>
            </form>
        </ul>
        <ul class="taille-police">
            <p>Langue</p>
            <form onchange='changerLangue()'>
                <input type='radio' name='langue' id='fr' <?php if ($_COOKIE["langue"] == "fr") echo "checked" ?>>
                <label for='fr'>Français</label>
                <input type='radio' name='langue' id='en' <?php if ($_COOKIE["langue"] == "en") echo "checked" ?>>
                <label for='en'>English</label>
            </form>
        </ul>
        <ul class="theme">
            <p>Wanna see something special ?</p>
            <button class="btn-accessibilite" onclick="changerTheme()">Click here</button>
        </ul>
    </div>
</div>

<script>
    /**Affiche le menu */
    function togleAcc(){
        let doc = document.getElementById('div-accessibilite');
        (doc.style.display == 'none') ? doc.style.display = 'grid' : doc.style.display = 'none';
    }
    /**Change le theme */
    function changerTheme(){
        document.body.classList.toggle('psychedelique');
    }
    /**Change la langue */
    function changerLangue(){
        let langue = document.querySelector("input[name='langue']:checked").id;
        setCookie("langue",langue,30);
        window.location = window.location.origin + window.location.pathname + "?lg=" + langue;
    }
</script>