/**
 * Gestion et affichage des commandes dans l'interface de la cuisine
 */

let bouton_courant;

/** Affiche les commandes dans les différentes catégories
 * @param {Object} data - Les données des commandes
 * @param {number} n - Le nombre de commandes affichées
 */
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
		{ id: "livree", titre: "Livrées", color: "#607D8B" },
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

	// 3. remplissage des conteneurs
	let i = 0;
	Object.keys(data).forEach((hash) => {
		const commande = data[hash];
		if (!commande["est-valide"]) return;

		const block = document.createElement("div");
		block.className = "block";

		const dateLivraison = new Date(commande["date_livraison"]);
		
		let isDifferee = (dateLivraison > aujourdhui);
		let action,
			affichage = "grid",
			contenu = "",
			affichage_date = "none";
		if (commande.etat == "payee") action = "Accepter la commande";
		else if (
			commande.etat == "en preparation" &&
			!isDifferee
		) {
			action = "Prendre la commande";
		} else if (commande.etat == "en preparation" && isDifferee) {
			contenu = new Date(commande.date_livraison).toLocaleString();
			affichage_date = "block";
			affichage = "none"
		} else affichage = "none";

		block.innerHTML = `
            <button class='btn-cmd' onclick="finirCommande('${hash}','${commande.etat}')" style='display: ${affichage}'>${action}</button>
            <span class='commande'><p>ID : ${commande.numero}</p></span>
			<p style='display : ${affichage_date}' class='commande'>Date de livraison : ${contenu}</p>
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
		else if (commande.etat === "livree") zones["livree"].appendChild(block);
		
		if (dateLivraison > aujourdhui) zones["differee"].appendChild(block);
		i += 1;
	});
	if (i > n) {
		// si on detecte une nouvelle commande, on affiche la notification
		document.getElementById("notification").style.display = "block";
	}

	/**
	 * Affiche la catégorie de commandes sélectionnée et met à jour l'apparence des boutons de navigation
	 * @param {string} id - L'identifiant de la catégorie à afficher (payee, preparation, prete, differee)
	 */
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

	afficherCategorie(bouton_courant ?? "payee"); // par défaut, on affiche les commandes payées sinon on affiche la catégorie courante

	categories.forEach((cat) => {
		if (zones[cat.id].innerHTML == "")
			zones[cat.id].innerHTML = "<p>Aucune commande en attente</p>";
	});
}

/** Met à jour le statut d'une commande
 * @param {string} hash - Le hash de la commande à mettre à jour
 * @param {string} etat - L'état actuel de la commande (payee ou en preparation)
 */
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
