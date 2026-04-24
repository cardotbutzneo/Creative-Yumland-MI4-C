const categories = [
    { id: "entrees", label: "Entrées" },
    { id: "plats", label: "Plats" },
    { id: "desserts", label: "Desserts" },
    { id: "vins", label: "Vins" },
    { id: "cafes", label: "Cafés" },
];

function chargerPlats() {
    const categorie = document.getElementById("filtre-carte").value;
    const regime = document.getElementById("filtre-regime").value;
    const allergene = document.getElementById("filtre-allergenes").value;
    const recherche = document.getElementById("bar-recherche").value;
    const url = `presentation.php?ajax=1&categorie=${categorie}&regime=${regime}&allergene=${allergene}&recherche=${encodeURIComponent(recherche)}`;
    fetch(url)
        .then(reponse => reponse.json())
        .then(plats => afficherPlats(plats))
        .catch(erreur => console.error(erreur));
}

function afficherPlats(plats) {
    const container = document.getElementById("liste-plats");
    container.innerHTML = "";
    let html = "";
    categories.forEach(cat => {
        const platscat = Object.entries(plats || {}).filter(([a, p]) => p.categorie === cat.id);
        if(platscat.length === 0) return;
        html += "<section class='rectangle'>";
        html += "<h2>" + cat.label + "</h2><ul>";
        platscat.forEach(([cle, plat]) => {
            html += "<li>";
            html += "<div class='ligne'>";
            html += "<span class='nom'>" + plat.nom + "</span>";
            html += "<span class='prix'>" + plat.prix + "€</span>";
            html += "</div>";
            html += "<span class='description'>" + plat.description + "</span>";
            if(est_client) {
                html += "<div><a href='panier.php?action=ajouter&id=" + encodeURIComponent(cle) + "' class='btn-ajouter'>+ Ajouter</a></div>";
            }
            html += "</li>";
        });
        html += "</ul></section>";
    });
    container.innerHTML = html || "<p>Aucun plat ne correspond.</p>";
}

document.getElementById("filtre-carte").addEventListener("change", chargerPlats);
document.getElementById("filtre-regime").addEventListener("change", chargerPlats);
document.getElementById("filtre-allergenes").addEventListener("change", chargerPlats);
document.getElementById("bar-recherche").addEventListener("input", chargerPlats);