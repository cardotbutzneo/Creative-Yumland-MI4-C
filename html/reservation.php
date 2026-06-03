<?php 
require_once __DIR__."/../api/config.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/reservation.css">
    <script src="../script.js" defer></script>
    <title>Réserver une table</title>
</head>
<body>
    <header>
        <a href="index.php"><h1>L'oro di Cicerone</h1></a>
        <nav>
            <ul>
                <li><a href="index.php"><?php if ($isFrench) echo "Accueil"; else echo "Home page" ?></a></li>
                <?php require_once "../api/get_accessibilite.php" ?>
            </ul>
        </nav>
    </header>
    <main>
        <div class="card">
            <h2><?= $isFrench ? "Réserver une table" : "Book a table" ?></h2>
            <div class="div-btn">
                <button onclick="ajouterTable('-')" value="-">-</button>
                <p> <?= $isFrench ? "Nombre de personnes" : "Number of people" ?>  : <span id="nb-personne">0</span></p>
                <button onclick="ajouterTable('+')" value="-">+</button>
            </div>
            <p id="erreur"></p>
            <p><?= $isFrench ? "Nombre de tables : " : "Number of table : " ?><span id="n-table">0</span></p>
        </div>
        <div class="card" id="horaire-cont" style="display:none">
            <p><?= $isFrench ? "A quelle heure souhaitez vous venir ?" : "What time do you want for a reservation ?" ?></p>
            <div id="jour-container" class="horaire-container">
                <select name="" id="jour-select" class="horaire-select"></select>
            </div>
            <div id="horaire-container" class="horaire-container">
                <select name="" id="horaire-select" class="horaire-select"></select>
            </div>
        </div>
        <div class="card">
            <button id="reservation" onclick="window.location = 'remerciement.php?res=true'"><?= $isFrench ? "RESERVER" : "Book" ?></button>
        </div>
    </main>
    <footer>
        <p><?= $text["index"]["footer_rights"] ?></p>
        <a href="contact.php"><?= $text["index"]["footer_contact"] ?></a><span> |</span>
        <a href="condition_generale.php"><?= $text["index"]["footer_privacy"] ?></a>
    </footer>
</body>
</html>


<script>
    const is_french = <?= $isFrench ? 'true' : 'false' ?>;
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
                    if (is_french) {
                        erreurElement.textContent = "Vous êtes plus de 8 ? ";
                        const link = document.createElement("a");
                        link.textContent = "Appelez-nous pour réserver.";
                        link.href = "contact.php";
                        erreurElement.appendChild(link);
                    } else {
                        erreurElement.textContent = "More than 8 people ? ";
                        const link = document.createElement("a");
                        link.textContent = "Call us to book.";
                        link.href = "contact.php";
                        erreurElement.appendChild(link);
                    }
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

        const parametresURL = new URLSearchParams(window.location.search);
        const langue_autorisée = ['fr', 'fr-FR', 'en', 'en-US', 'en-GB', 'es', 'es-ES'];
        let langue = parametresURL.get('lg'); // langue récupérer depuis l'URL
        if (!langue_autorisée.includes(langue)) langue = 'en-US'; // en-US par défaut.
        const formateur = new Intl.DateTimeFormat( langue, { weekday: 'long' }); 

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