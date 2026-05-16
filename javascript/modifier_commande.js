// Fonction qui recalcule le montant de la commande
function calculerTout() {
    let total = 0;
    let items = document.querySelectorAll('.plat-ligne');
    items.forEach(function(elt) {     // Parcours de chaque plat pour calculer son sous-total
        let prix = parseInt(elt.getAttribute("data-prix")); // Récupération du prix unitaire et de la quantité
        let qte = parseInt(elt.querySelector('.qte-nb').innerText);
        let sousTotal = prix * qte;
        total = total + sousTotal;
        elt.querySelector('.plat-sous-total').innerText = sousTotal + "€"; // Mise à jour de l'affichage du sous-total
    });

    document.getElementById('input-total').value = total; // Mise à jour du total envoyé au formulaire

    let diff = total - TOTAL_BRUT_ORIGINAL;

    let nouveauMontant = MONTANT_PAYE + diff;
    if (nouveauMontant < 0) nouveauMontant = 0;
    document.getElementById('display-total').innerText = nouveauMontant.toFixed(2) + "€"; // Mise à jour de l'affichage du total final

    let diffRow = document.getElementById('diff-row'); // Récupération des éléments d'information visuelle
    let info = document.getElementById('info-perdant');

    if (diff > 0) { // Cas où un supplément doit être payé
        diffRow.style.display = "flex";
        info.style.display = "none";
        document.getElementById('diff-label').innerText = "Supplément à régler";
        document.getElementById('diff-amount').innerText = "+" + diff.toFixed(2) + "€"; 
    } else if (diff < 0) { // Cas où le montant est inférieur ou égale au montant original
        diffRow.style.display = "none";
        info.style.display = "block";
    } else { // Cas où il y a aucune différence
        diffRow.style.display = "none";
        info.style.display = "none";
    }
}

// Fonction permettant de modifier la quantité d’un plat
function modifierQte(btn, delta) {
    let span = btn.parentElement.querySelector('.qte-nb');
    let qte = parseInt(span.innerText);
    qte = qte + delta; // Application de la modification (+1 ou -1)
    if (qte > 0) { // Mis à jour de l'affichage
        span.innerText = qte;
    } else {
        if (confirm("Retirer ce plat de la commande ?")) {
            btn.closest('.plat-ligne').remove();
        }
    }
    calculerTout(); // Recalcul des montants après modification
}

// Fonction permettant de supprimer complètement une ligne de plat
function supprimerLigne(btn) {
    if (confirm("Supprimer cet article ?")) {
        btn.closest('.plat-ligne').remove();
        calculerTout();
    }
}

// Fonction permettant d’ajouter un nouveau plat à la commande
function ajouterPlat() {
    let select = document.getElementById('select-plat');
    let nom = select.value;
    if (nom == "") {
        return;
    }
    let prix = parseInt(select.options[select.selectedIndex].getAttribute("data-prix"));
    let list = document.getElementById('liste-commande');
    let exist = null;
    let items = list.querySelectorAll('.plat-ligne');
    items.forEach(function(elt) {
        if (elt.getAttribute("data-nom") == nom) {
            exist = elt;
        }
    });
    if (exist != null) { // Si le plat existe déjà, on augmente simplement sa quantité
        let span = exist.querySelector('.qte-nb');
        let qte = parseInt(span.innerText);
        span.innerText = qte + 1;
    } else { // Sinon, création complète d’une nouvelle ligne HTML
        let div = document.createElement('div');
        div.className = "plat-ligne";
        div.setAttribute("data-nom", nom);
        div.setAttribute("data-prix", prix);
        div.innerHTML = // Construction du contenu HTML de la ligne
            '<div class="plat-haut">' +
                '<span class="plat-nom">' + nom + '</span>' +
                '<span class="plat-sous-total">' + prix + '€</span>' +
            '</div>' +
            '<div class="plat-bas">' +
                '<div class="groupe-qte">' +
                    '<div class="controles-qte">' +
                        '<button type="button" onclick="modifierQte(this, -1)">-</button>' +
                        '<span class="qte-nb">1</span>' +
                        '<button type="button" onclick="modifierQte(this, 1)">+</button>' +
                    '</div>' +
                    '<span class="hint-unite">' + prix + '€ / unité</span>' +
                '</div>' +
                '<button type="button" class="btn-retirer" onclick="supprimerLigne(this)">Retirer</button>' +
            '</div>';
        list.appendChild(div); // Ajout de la ligne dans la liste
    }
    select.value = "";
    calculerTout();
}

// Fonction appelée avant l’envoi du formulaire
function envoyerFormulaire() {
    let plats = [];
    let items = document.querySelectorAll('.plat-ligne');
    items.forEach(function(elt) { // Construction du tableau des plats
        let nom = elt.getAttribute("data-nom");
        let qte = parseInt(elt.querySelector('.qte-nb').innerText);
        plats.push({
            nom: nom,
            quantite: qte
        });
    });

    document.getElementById('input-json').value = JSON.stringify(plats); // Conversion du tableau en JSON pour l’envoi au serveur
    document.getElementById('form-final').submit();
}

window.addEventListener('DOMContentLoaded', function() { // Exécution automatique au chargement de la page
    calculerTout();
});