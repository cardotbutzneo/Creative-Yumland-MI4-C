function renderCommandes(data) {
	const conteneur = document.getElementById("liste-commandes");
	const aujourdhui = new Date();
	conteneur.innerHTML = "";

	const createSection = (titre) => {
		const det = document.createElement("details");
		det.open = true;
		const sum = document.createElement("summary");
		sum.innerHTML = `<h2>${titre}</h2>`;
		det.appendChild(sum);
		return det;
	};

	// Fonction utilitaire pour créer une section formatée
	function creerConteneur() {
		const sect = document.createElement("section");
		sect.className = "colonne-commandes";
		return sect;
	}

	// Création des catégories avec leurs propres sections internes
	const commandeDifferee = createSection("Commandes différées");
	const zoneDifferee = creerConteneur();
	commandeDifferee.appendChild(zoneDifferee);

	const commandePayee = createSection("Commandes payées");
	const zonePayee = creerConteneur();
	commandePayee.appendChild(zonePayee);

	const commandeEnPreparation = createSection("Commandes en préparation");
	const zoneEnPrep = creerConteneur();
	commandeEnPreparation.appendChild(zoneEnPrep);

	const commandePreparee = createSection("Commandes prêtes");
	const zonePrete = creerConteneur();
	commandePreparee.appendChild(zonePrete);

	Object.keys(data).forEach((hash) => {
		const commande = data[hash];

		if (!commande["est-valide"]) return;

		const dateLivraison = commande["date_livraison"]
			? new Date(commande["date_livraison"])
			: aujourdhui;
		let flag = false;
		if (dateLivraison != aujourdhui) flag = true;
		if (!dateLivraison) return;

		const diffMs = dateLivraison - aujourdhui;
		const diffHeures = diffMs / (1000 * 60 * 60);
		const diffJours = diffHeures / 24;

		const block = document.createElement("div");

		block.className = "block";

		block.innerHTML = `
                <button class='btn-cmd' onclick="finirCommande('${hash}','${commande.etat}')">Finir la commande</button>
                <p style='display : ${flag ? "block" : "none"} '>A livrer avant : ${dateLivraison.toLocaleString("fr-FR", { day: "2-digit", month: "2-digit", hour: "2-digit", minute: "2-digit" })}</p>
                <span class='commande'>
                    <p>ID : ${commande.numero}</p>
                    <p class='statut' data-stat='${commande.etat}' >Statut : ${commande.etat}</p>
                </span>
                <span class='commande'>
                    <p>Date de livraison : ${dateLivraison.toLocaleString("fr-FR", { day: "2-digit", month: "2-digit", hour: "2-digit", minute: "2-digit" })}</p>
                </span>
                <div>
                    <ul>
                        ${commande.plats.map((p) => `<li>${p.nom} x${p.quantite}</li>`).join("")}
                    </ul>
                </div>
                ${commande.instructions ? `<p id='Complement'>${commande.instructions}</p>` : ""}
            `;
		if (commande.etat == "payee") {
			zonePayee.appendChild(block);
			block.querySelector(".btn-cmd").textContent =
				"Accepter la commande";
		} else if (commande.etat == "en preparation") {
			zoneEnPrep.appendChild(block);
		} else if (commande.etat == "preparee") {
			zonePrete.appendChild(block);
			block.querySelector(".btn-cmd").style.display = "none";
		}
		if (flag) zoneDifferee.appendChild(block);
	});

	conteneur.appendChild(commandePayee);
	conteneur.appendChild(commandeDifferee);
	conteneur.appendChild(commandeEnPreparation);
	conteneur.appendChild(commandePreparee);
}

async function finirCommande(hash, etat) {
	if (!hash) return;
	try {
		let nvEtat;
		if (etat == "payee") nvEtat = "en preparation";
		else nvEtat = "preparee"; // par defaut
		console.log(etat + " : " + nvEtat);
		const response = await fetch("../api/update_statut.php", {
			method: "POST",
			headers: { "Content-Type": "application/x-www-form-urlencoded" },
			body: `hash=${hash}&nouvelEtat=` + nvEtat,
		});

		if (response.ok) {
			chargerCommandes();
		}
	} catch (error) {
		console.error("Erreur serveur :", error);
	}
}
