<?php 
require_once __DIR__."/../api/config.php";

?>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        background-color: #0f0f0f;
        color: #c9a24d;
        min-height: 100vh;
        display: flex;
        gap: 20px;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    main{
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .card {
        background-color: #161616;
        border: 1px solid #2a2a2a;
        border-radius: 8px;
        padding: 40px 30px;
        width: 100%;
        max-width: 450px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.7);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 25px;
    }

    h2 {
        font-size: 1.8rem;
        font-weight: 400;
        letter-spacing: 1px;
        text-transform: uppercase;
        border-bottom: 1px solid #c9a24d;
        padding-bottom: 10px;
        width: 100%;
        text-align: center;
    }

    .div-btn {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        width: 100%;
        background-color: #1c1c1c;
        padding: 20px;
        border-radius: 6px;
        border: 1px solid #222;
    }

    .div-btn-controls {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 20px;
        width: 100%;
    }

    .div-btn button {
        background-color: transparent;
        color: #c9a24d;
        border: 1px solid #c9a24d;
        font-size: 1.5rem;
        width: 45px;
        height: 45px;
        border-radius: 10%;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .div-btn button:hover {
        background-color: #c9a24d;
        color: #0f0f0f;
        box-shadow: 0 0 10px rgba(201, 162, 77, 0.4);
    }

    .div-btn button:active {
        transform: scale(0.95);
    }

    .div-btn p {
        font-size: 1.1rem;
        letter-spacing: 0.5px;
    }

    .div-btn p:last-of-type {
        margin-top: 10px;
        font-size: 1rem;
        color: #8a8a8a;
        display: flex;
        gap: 5px;
    }

    #n-table {
        color: #c9a24d;
        font-weight: bold;
    }

    .horaire {
        width: 100%;
        text-align: center;
        font-size: 0.9rem;
        color: #8a8a8a;
    }

    #horaire-container {
    width: 100%;
    margin-top: 10px;
}

    #horaire-select {
        width: 100%;
        background-color: #1c1c1c;
        color: #c9a24d;
        border: 1px solid #c9a24d;
        padding: 12px 15px;
        font-size: 1rem;
        border-radius: 6px;
        cursor: pointer;
        outline: none;
        text-align: center;
        transition: border-color 0.2s ease;
    }

    #horaire-select:focus {
        border-color: #ffffff;
        box-shadow: 0 0 5px rgba(255, 255, 255, 0.2);
    }

    #reservation{
        width: 100%; 
        border: none;
        border: 1px #c9a24d solid;
        border-radius: 10px;
        padding: 15px;
        color:#c9a24d;
        background-color: #161616;
    }

    #reservation:hover{
        background-color: #c9a24d;
        color: black;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <title>Réserver une table</title>
</head>
<body>
    <main>
        <div class="card">
            <h2>Réserver une table</h2>
            <div class="div-btn">
                <button onclick="ajouterTable('-')" value="-">-</button>
                <p>Nombre de personne : <span id="nb-personne">0</span></p>
                <button onclick="ajouterTable('+')" value="-">+</button>
            </div>
            <p id="erreur"></p>
            <p>Nombre de table : <span id="n-table">0</span></p>
        </div>
        <div class="card" id="horaire-cont" style="display:none">
            <p>A quelle heure souhaitez vous venir ?</p>
            <div id="horaire-container">
                <select name="" id="horaire-select"></select>
            </div>
            <div id="jour-container">
                <select name="" id="jour-select"></select>
            </div>
        </div>
        <div class="card">
            <button id="reservation" onclick="window.location = 'remerciement.php?res=true'">RESERVER</button>
        </div>
    </main>
</body>
</html>


<script>
    function ajouterTable(valeur) {
        let nb_personne = Number(document.getElementById("nb-personne").textContent);
        let n_table = Number(document.getElementById("n-table").textContent);
        const erreurElement = document.getElementById('erreur');

        if (erreurElement) erreurElement.textContent = "";

        if (nb_personne > 8 || nb_personne < 0) nb_personne = 0;

        if (valeur === "+") {
            nb_personne += 1;
            console.log(nb_personne);
            if (nb_personne >= 8) {
                if (erreurElement) {
                    erreurElement.textContent = "Vous êtes plus de 8 ? ";
                    const link = document.createElement("a");
                    link.textContent = "Appelez-nous pour réserver.";
                    link.href = "contact.php";
                    erreurElement.appendChild(link);
                }
                nb_personne = 8;
            }
        }
        else if (valeur === "-" && nb_personne > 0) {
            nb_personne -= 1;
        }

        let ajout_table = 0;
        if (nb_personne > 0) {
            ajout_table = Math.ceil(nb_personne / 4);
        }
        n_table = ajout_table;

        document.getElementById("nb-personne").textContent = nb_personne;
        document.getElementById("n-table").textContent = n_table;

        if (nb_personne > 0) {
            construireHoraire(); // si il y a des gens on créer les horaires
        }
        else {
            document.getElementById("horaire-cont").style.display = "none";
        }
    }

    function get30MinSlots(heureDebut, heureFin) {
        const slots = [];
        let current = new Date();
        current.setHours(heureDebut, 0, 0, 0);

        let endLimit = new Date();
        endLimit.setHours(heureFin, 0, 0, 0);

        // Tant qu'on n'a pas dépassé l'heure de fermeture du service
        while (current <= endLimit) {
            slots.push(new Date(current));
            current.setMinutes(current.getMinutes() + 30);
        }
        return slots;
    }

    function buildWeek(){
        /**
         * Retourne un tableau avec la liste des jours de la semaine en fonction du pays
         */
        const baseDate = new Date(2026, 5, 1);

        const joursDeLaSemaine = [];

        const formateur = new Intl.DateTimeFormat('en-US', { weekday: 'long' }); // changer cette ligne pour avoir changer la langue

        for (let i = 0; i < 7; i++) {
            const jourCourant = new Date(baseDate);
            jourCourant.setDate(baseDate.getDate() + i);
            
            joursDeLaSemaine.push(formateur.format(jourCourant));
        }

        return joursDeLaSemaine;
    }

    function construireHoraire(){
        const days = buildWeek();
        const plage_horaire = "12:14-19:22" // syntaxe horaire_debut:horaire_fin pour chaque service
        const jour_service = "mon-tue-thu-fri-sat-sun"; // du lundi au dimanche sauf le mercredi
        
        const services = plage_horaire.split("-");
        const jours = jour_service.split("-");

        const selectElement = document.getElementById("horaire-select");
        const jourElement = document.getElementById("jour-select");
        selectElement.innerHTML = "";

        services.forEach(service => {
            const heures = service.split(":");
            const heureDebut = Number(heures[0]);
            const heureFin = Number(heures[1]);
            
            const tranches = get30MinSlots(heureDebut,heureFin);

            tranches.forEach(date =>{
                const heure_formatee = date.toLocaleTimeString('fr-FR', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                const option = document.createElement("option");
                option.value = heure_formatee;
                option.textContent = heure_formatee;
                selectElement.appendChild(option);
            })
        });

        const correspondanceIndex = { "mon": 0, "tue": 1, "wed": 2, "thu": 3, "fri": 4, "sat": 5, "sun": 6 };
        jours.forEach(j =>{
            const option = document.createElement("option");
            option.value = j;
            option.textContent = days[correspondanceIndex[j]];
            if (jourElement) jourElement.appendChild(option);
        })
        document.getElementById("horaire-cont").style.display = "block";
    }

</script>