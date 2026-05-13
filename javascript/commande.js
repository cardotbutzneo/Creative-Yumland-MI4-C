let bouton_courant;

function renderCommandes(data, n) {
	const conteneurPrincipal = document.getElementById("liste-commandes");
	const aujourdhui = new Date();
	conteneurPrincipal.innerHTML = "";

	// 1. Création de la barre de navigation (Boutons)
	const nav = document.createElement("nav");
	nav.className = "tabs-nav";

	const categories = [
		{ id: "payee", titre: "Payées", color: "#4CAF50" },
		{ id: "preparation", titre: "En préparation", color: "#FF9800" },
		{ id: "prete", titre: "Prêtes", color: "#2196F3" },
		{ id: "differee", titre: "Différées", color: "#9C27B0" },
	];

	// 2. Création des conteneurs de colonnes
	const zones = {};

	categories.forEach((cat) => {
		// Création du bouton
		const btn = document.createElement("button");
		btn.textContent = cat.titre;
		btn.className = "btn-selector";
		btn.dataset.statut = cat.id;
		btn.style.backgroundColor = cat.color;
		btn.style.opacity = 0.5;
		btn.onclick = () => afficherCategorie(cat.id);
		nav.appendChild(btn);

		// Création de la zone de contenu
		const zone = document.createElement("section");
		zone.id = `zone-${cat.id}`;
		zone.className = "colonne-commandes";
		zone.style.display = "none"; // Caché par défaut
		zones[cat.id] = zone;
	});

	conteneurPrincipal.appendChild(nav);
	Object.values(zones).forEach((z) => conteneurPrincipal.appendChild(z));

	let i = 0;
	Object.keys(data).forEach((hash) => {
		const commande = data[hash];
		if (!commande["est-valide"]) return;

		const block = document.createElement("div");
		block.className = "block";
		let action,
			affichage = "grid";
		if (commande.etat == "payee") action = "Accepter la commande";
		else if (commande.etat == "en preparation")
			action = "Prendre la commande";
		else affichage = "none";

		block.innerHTML = `
            <button class='btn-cmd' onclick="finirCommande('${hash}','${commande.etat}')" style='display: ${affichage}'>${action}</button>
            <span class='commande'><p>ID : ${commande.numero}</p></span>
            <ul>${Object.values(commande.plats || {})
				.map(
					(details) => `<li>${details.nom} x${details.quantite}</li>`,
				)
				.join("")}</ul>
			${commande.instructions ? `<p id='Complement'>Complement : ${commande.instructions}</p>` : ""}
        `;

		if (commande.etat === "payee") zones["payee"].appendChild(block);
		else if (commande.etat === "en preparation")
			zones["preparation"].appendChild(block);
		else if (commande.etat === "preparee")
			zones["prete"].appendChild(block);
		const dateLivraison = new Date(commande["date_livraison"]);
		if (dateLivraison > aujourdhui) zones["differee"].appendChild(block);
		i += 1;
	});
	if (i > n) {
		document.getElementById("notification").style.display = "block";
	}

	function afficherCategorie(id) {
		Object.values(zones).forEach((z) => (z.style.display = "none"));
		zones[id].style.display = "grid";
		let btns = document.querySelectorAll(".btn-selector");
		Object.values(btns).forEach((v) => {
			if (v.dataset.statut == id) {
				v.style.opacity = 1;
				bouton_courant = v.dataset.statut;
			} else v.style.opacity = 0.5;
		});
	}

	afficherCategorie(bouton_courant ?? "payee");

	categories.forEach((cat) => {
		if (zones[cat.id].innerHTML == "")
			zones[cat.id].innerHTML = "<p>Aucune commande en attente</p>";
	});
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
