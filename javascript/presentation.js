const categories = [   // Tableau contenant les différentes catégories
    { id: "entrees", label: "Entrées" },
    { id: "plats", label: "Plats" },
    { id: "desserts", label: "Desserts" },
    { id: "vins", label: "Vins" },
    { id: "cafes", label: "Cafés" },
];

function chargerPlats() { // Fonction appelée à chaque modification des filtres ou de la barre de recherche
    const categorie = document.getElementById("filtre-carte").value; 
    const regime = document.getElementById("filtre-regime").value;
    const allergene = document.getElementById("filtre-allergenes").value;
    const recherche = document.getElementById("bar-recherche").value;
    const url = `presentation.php?ajax=1&categorie=${categorie}&regime=${regime}&allergene=${allergene}&recherche=${encodeURIComponent(recherche)}`; // Construction de l’URL envoyée au serveur avec les filtres
    fetch(url) // Requête AJAX pour récupérer les plats filtrés
        .then(reponse => reponse.json()) // Conversion de la réponse en JSON
        .then(plats => afficherPlats(plats)) // Appel de la fonction d’affichage des plats
        .catch(erreur => console.error(erreur)); // Gestion des erreurs 
}

function afficherPlats(plats) { // Fonction qui affiche les plats dans la page HTML
    const container = document.getElementById("liste-plats");
    container.innerHTML = "";
    let html = ""; // Variable qui contiendra tout le HTML généré
    categories.forEach(cat => {
        const platscat = Object.entries(plats || {}).filter(([a, p]) => p.categorie === cat.id); // Filtrage des plats appartenant à la catégorie actuelle
        if(platscat.length === 0) return;
        html += "<section class='rectangle'>"; // Création de la section HTML de la catégorie
        html += "<h2>" + cat.label + "</h2><ul>";
        platscat.forEach(([cle, plat]) => { // Parcours de tous les plats de cette catégorie
            html += "<li>";
            html += "<div class='ligne'>";
            html += "<span class='nom'>" + plat.nom + "</span>";
            html += "<span class='prix'>" + plat.prix + "€</span>";
            html += "</div>";
            html += "<span class='description'>" + plat.description + "</span>";
            if(est_client) { // Si l’utilisateur est un client, affichage du bouton Ajouter
                html += "<div><a href='panier.php?action=ajouter&id=" + encodeURIComponent(cle) + "' class='btn-ajouter'>+ Ajouter</a></div>";
            }
            html += "</li>";
        });
        html += "</ul></section>";
    });
    container.innerHTML = html || "<p>Aucun plat ne correspond.</p>"; // Affichage du HTML généré ou d’un message si aucun plat ne correspond
}

document.getElementById("filtre-carte").addEventListener("change", chargerPlats);
document.getElementById("filtre-regime").addEventListener("change", chargerPlats);
document.getElementById("filtre-allergenes").addEventListener("change", chargerPlats);
document.getElementById("bar-recherche").addEventListener("input", chargerPlats);