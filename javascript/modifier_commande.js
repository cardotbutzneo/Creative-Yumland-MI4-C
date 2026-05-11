function calculerTout() {
    let total = 0;
    let items = document.querySelectorAll('.mc-item');
    items.forEach(function(elt) {
        let prix = parseInt(elt.getAttribute("data-prix"));
        let qte = parseInt(elt.querySelector('.qte-nb').innerText);
        let sousTotal = prix * qte;
        total = total + sousTotal;
        elt.querySelector('.mc-item-subtotal').innerText = sousTotal + "€"; });
    document.getElementById('display-total').innerText =
        total + "€";
    document.getElementById('input-total').value =
        total;
    let diff = total - total1;
    let diff1 = document.getElementById('diff-row');
    let info = document.getElementById('mc-info-perdant');
    if (diff > 0) {
        diff1.style.display = "flex";
        info.style.display = "none";
        document.getElementById('diff-label').innerText = "Supplément à régler";
        document.getElementById('diff-amount').innerText = "+" + diff + "€";
    }
    else if (diff < 0) {
        diff1.style.display = "none";
        info.style.display = "block";
    }

    else {
        diff1.style.display = "none";
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
            btn.closest('.mc-item').remove();
        }
    }
    calculerTout();
}

function supprimerLigne(btn) {
    if (confirm("Supprimer cet article ?")) {
        btn.closest('.mc-item').remove();
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
    let items = list.querySelectorAll('.mc-item');
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
        div.className = "mc-item";
        div.setAttribute("data-nom", nom);
        div.setAttribute("data-prix", prix);
        div.innerHTML =
            '<div class="mc-item-top">' +
                '<span class="mc-item-name">' +
                    nom +
                '</span>' +
                '<span class="mc-item-subtotal">' +
                    prix + '€' +
                '</span>' +
            '</div>' +
            '<div class="mc-item-bottom">' +
                '<div class="mc-qte-group">' +
                    '<div class="mc-qte-controls">' +
                        '<button type="button" onclick="modifierQte(this, -1)">-</button>' +
                        '<span class="qte-nb">1</span>' +
                        '<button type="button" onclick="modifierQte(this, 1)">+</button>' +
                    '</div>' +
                    '<span class="mc-unit-hint">' +
                        prix + '€ / unité' +
                    '</span>' +
                '</div>' +
                '<button type="button" class="mc-btn-remove" onclick="supprimerLigne(this)">Retirer</button>' +
            '</div>';
        list.appendChild(div);
    }
    select.value = "";
    calculerTout();
}

function envoyerFormulaire() {
    let plats = [];
    let items = document.querySelectorAll('.mc-item');
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