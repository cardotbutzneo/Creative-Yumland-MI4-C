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
            console.log("taille " + taille);
            setCookie("taille_pref",taille,30);

            document.documentElement.style.setProperty('--global-font-size', taille);
        }

        getFontSize('<?= htmlspecialchars($_COOKIE["taille_pref"]) ?>');
    </script>
    <p class='title-acc'>Accessibilité</p>
    <div class="forms">
        <ul class='taille-police'>
        <p>Taille de police</p>
        <form onchange='trouverTaille()'>
            <label for='12'>Petite</label>
            <input type='radio' name='police' id='12'>
            <label for='16'>Moyenne</label>
            <input type='radio' name='police' id='16'>
            <label for='24'>Grande</label>
            <input type='radio' name='police' id='24'>
        </form>
        </ul>
        <ul class='theme'>
            <p>Wanna see something special ?</p>
            <button onclick="changerTheme()">Click here</button>
        </ul>
    </div>
</div>

<script>
    function togleAcc(){
        let doc = document.getElementById('div-accessibilite');
        console.log(doc.style.display);
        (doc.style.display == 'none') ? doc.style.display = 'grid' : doc.style.display = 'none';
    }
    function changerTheme(){
        document.body.classList.toggle('psychedelique');
    }
</script>