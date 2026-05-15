function calculerTout() {
    let total = 0;
    let items = document.querySelectorAll('.plat-ligne');
    items.forEach(function(elt) {
        let prix = parseInt(elt.getAttribute("data-prix"));
        let qte = parseInt(elt.querySelector('.qte-nb').innerText);
        let sousTotal = prix * qte;
        total = total + sousTotal;
        elt.querySelector('.plat-sous-total').innerText = sousTotal + "€";
    });

    document.getElementById('input-total').value = total;

    let diff = total - TOTAL_BRUT_ORIGINAL;

    let nouveauMontant = MONTANT_PAYE + diff;
    if (nouveauMontant < 0) nouveauMontant = 0;
    document.getElementById('display-total').innerText = nouveauMontant.toFixed(2) + "€";

    let diffRow = document.getElementById('diff-row');
    let info = document.getElementById('info-perdant');

    if (diff > 0) {
        diffRow.style.display = "flex";
        info.style.display = "none";
        document.getElementById('diff-label').innerText = "Supplément à régler";
        document.getElementById('diff-amount').innerText = "+" + diff.toFixed(2) + "€";
    }
    else if (diff < 0) {
        diffRow.style.display = "none";
        info.style.display = "block";
    }
    else {
        diffRow.style.display = "none";
        info.style.display = "none";
    }
}

function modifierQte(btn, delta) {
    let span = btn.parentElement.querySelector('.qte-nb');
    let qte = parseInt(span.innerText);
    qte = qte + delta;
    if (qte > 0) {
        span.innerText = qte;
    }
    else {
        if (confirm("Retirer ce plat de la commande ?")) {
            btn.closest('.plat-ligne').remove();
        }
    }
    calculerTout();
}

function supprimerLigne(btn) {
    if (confirm("Supprimer cet article ?")) {
        btn.closest('.plat-ligne').remove();
        calculerTout();
    }
}

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
    if (exist != null) {
        let span = exist.querySelector('.qte-nb');
        let qte = parseInt(span.innerText);
        span.innerText = qte + 1;
    }
    else {
        let div = document.createElement('div');
        div.className = "plat-ligne";
        div.setAttribute("data-nom", nom);
        div.setAttribute("data-prix", prix);
        div.innerHTML =
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
        list.appendChild(div);
    }
    select.value = "";
    calculerTout();
}

function envoyerFormulaire() {
    let plats = [];
    let items = document.querySelectorAll('.plat-ligne');
    items.forEach(function(elt) {
        let nom = elt.getAttribute("data-nom");
        let qte = parseInt(elt.querySelector('.qte-nb').innerText);
        plats.push({
            nom: nom,
            quantite: qte
        });
    });

    document.getElementById('input-json').value = JSON.stringify(plats);
    document.getElementById('form-final').submit();
}

window.addEventListener('DOMContentLoaded', function() {
    calculerTout();
});