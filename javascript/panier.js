function calculerTout() { // Fonction principale qui calcule tous les montants du panier
    let total = 0; 
    document.querySelectorAll('.item').forEach(function(elt) { // Parcours de tous les articles du panier
        const prix = parseInt(elt.getAttribute("data-prix"));
        const qte = parseInt(elt.querySelector('.qte-nb').innerText);
        const sousTotal = prix * qte;
        total += sousTotal;
        elt.querySelector('.item-soustot').innerText = sousTotal + "€";
    });

    document.getElementById('display-total').innerText = total + "€"; // Mise à jour de l’affichage du total général

    const blocTotal = document.getElementById('bloc-total');
    const reduc = blocTotal ? parseFloat(blocTotal.dataset.reduc) : 0; // Lecture du pourcentage de réduction
    const totalReduit = document.getElementById('display-total-reduit');
    const blocRemise = document.getElementById('bloc-remise');
    const blocTotalR = document.getElementById('bloc-total-reduit');
    const pasReduc = document.getElementById('p-pas-reduc');

    if (reduc > 0 && totalReduit) { // Cas où une réduction existe
        const apresReduc = Math.ceil(total * (1 - reduc)); 
        totalReduit.innerText = apresReduc + "€";
        if (blocRemise) { // Affichage du bloc de remise
            blocRemise.style.display = ''; 
        }  
        if (blocTotalR) {   // Affichage du bloc du total réduit
            blocTotalR.style.display = '';
        }
        if (pasReduc) {
            pasReduc.style.display = 'none';
        }
    } else { // Cas où il n’y a aucune réduction
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

function supprimerLigne(btn) { // Supprime un article du panier
    const item = btn.closest('.item');
    const cle = item.dataset.cle;
    item.remove();
    fetch('panier.php?action=supprimer&id=' + encodeURIComponent(cle)); // Suppression côté serveur
    if (document.querySelectorAll('.item').length === 0) { // Si le panier est vide, affichage du message
        document.getElementById('panier-contenu').style.display = 'none';
        document.getElementById('panier-vide').style.display = '';
    } else {
        calculerTout();
    }
}
 
function modifierQte(btn, delta) { // Fonction permettant de modifier la quantité d'un article
    const span = btn.parentElement.querySelector('.qte-nb');
    const cle = btn.closest('.item').dataset.cle;
    let qte = parseInt(span.innerText) + delta;
    if (qte > 0) { // Si la quantité est supérieure à 0
        span.innerText = qte;
        fetch('panier.php?action=set_qte&id=' + encodeURIComponent(cle) + '&qte=' + qte); // Mise à jour de la quantité côté serveur
    } else { // Si la quantité est de 0 ou moins
        if (confirm("Retirer ce plat du panier ?")) {
            btn.closest('.item').remove();
            fetch('panier.php?action=supprimer&id=' + encodeURIComponent(cle));
            if (document.querySelectorAll('.item').length === 0) { // Si le panier est vide, affichage du message
                document.getElementById('panier-contenu').style.display = 'none';
                document.getElementById('panier-vide').style.display = '';
            }
        }
    }
    calculerTout(); // Recalcul des montants après modification
}


function initCompteurInstructions() { // Fonction permettant d’initialiser le compteur de caractères
    const textarea = document.getElementById("instructions");
    const compteur = document.getElementById("compteur-instructions");
    if (!textarea || !compteur) return; // Si les éléments n’existent pas, arrêt de la fonction
    function mettreAJourCompteur() { // Fonction mettant à jour le compteur
        compteur.textContent = textarea.value.length + " / 500"; // Affichage du nombre de caractères saisis en temps réel
    }
    textarea.addEventListener("input", mettreAJourCompteur);
    mettreAJourCompteur();
}

document.addEventListener("DOMContentLoaded", function() {
    calculerTout();
    initCompteurInstructions();
});