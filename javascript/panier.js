function calculerTout() {
    let total = 0;
    document.querySelectorAll('.mc-item').forEach(function(elt) {
        const prix = parseInt(elt.getAttribute("data-prix"));
        const qte = parseInt(elt.querySelector('.qte-nb').innerText);
        const sousTotal = prix * qte;
        total += sousTotal;
        elt.querySelector('.mc-item-subtotal').innerText = sousTotal + "€";
    });

    document.getElementById('display-total').innerText = total + "€";

    const blocTotal = document.getElementById('bloc-total');
    const reduc = blocTotal ? parseFloat(blocTotal.dataset.reduc) : 0;
    const totalReduit = document.getElementById('display-total-reduit');
    const blocRemise = document.getElementById('bloc-remise');
    const blocTotalR = document.getElementById('bloc-total-reduit');
    const pasReduc = document.getElementById('p-pas-reduc');

    if (reduc > 0 && totalReduit) {
        const apresReduc = Math.ceil(total * (1 - reduc));
        totalReduit.innerText = apresReduc + "€";
        if (blocRemise) {
            blocRemise.style.display = '';
        }  
        if (blocTotalR) {  
            blocTotalR.style.display = '';
        }
        if (pasReduc) {
            pasReduc.style.display = 'none';
        }
    } else {
        if (blocRemise) {
            blocRemise.style.display = 'none';
        }
        if (blocTotalR) {
            blocTotalR.style.display = 'none';
        }
        if (pasReduc) {
            pasReduc.style.display = '';
        }
    }
}

function modifierQte(btn, delta) {
    const span = btn.parentElement.querySelector('.qte-nb');
    const cle = btn.closest('.mc-item').dataset.cle;
    let qte = parseInt(span.innerText) + delta;

    if (qte > 0) {
        span.innerText = qte;
        fetch('panier.php?action=set_qte&id=' + encodeURIComponent(cle) + '&qte=' + qte);
    } else {
        if (confirm("Retirer ce plat du panier ?")) {
            btn.closest('.mc-item').remove();
            fetch('panier.php?action=supprimer&id=' + encodeURIComponent(cle));
        }
    }
    calculerTout();
}

function initCompteurInstructions() {
    const textarea = document.getElementById("instructions");
    const compteur = document.getElementById("compteur-instructions");
    if (!textarea || !compteur) return;
    function mettreAJourCompteur() {
        compteur.textContent = textarea.value.length + " / 500";
    }
    textarea.addEventListener("input", mettreAJourCompteur);
    mettreAJourCompteur();
}

document.addEventListener("DOMContentLoaded", function() {
    calculerTout();
    initCompteurInstructions();
});