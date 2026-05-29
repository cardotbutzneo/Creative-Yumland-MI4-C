/**
 * Recherche un utilisateur dans la liste et affiche uniquement celui dont l'e-mail correspond à l'identifiant recherché.
 * @param {string} id - L'identifiant (adresse e-mail) à rechercher.
 */

/**
 * To do : 
 * - régler le bug de double affichage des erreurs de logs lors du tri
 */

function chercherUtilisateur(id) {
	// Si le champ de recherche est vide, on affiche tous les utilisateurs
	if (id.length <= 0) {
		const rows = document.querySelectorAll(".utilisateur");
		rows.forEach((row) => (row.style.display = ""));
		return;
	}

	const rows = document.querySelectorAll(".utilisateur");
	rows.forEach((row) => {
		// On compare l'adresse e-mail de chaque ligne avec l'ID recherché
		if (row.dataset.mail === id) {
			row.style.display = ""; // afficher la ligne correspondante
		} else {
			row.style.display = "none"; // masquer les autres lignes
		}
	});
}

/**
 * Récupère les informations clients depuis l'API et renvoie les données JSON.
 * @returns {Promise<Object>} Les données renvoyées par l'API ou un objet vide en cas d'erreur.
 */
async function get_id() {
	try {
		const reponse = await fetch("../api/get_client.php", {
			method: "POST",
			headers: { "Content-Type": "application/json" },
		});

		const data = await reponse.json();
		return data;
	} catch (e) {
		console.error("Erreur fetch:", e);
		return {}; // Retourne un objet vide en cas d'erreur pour éviter une exception
	}
}

/**
 * Affiche la liste des ID de clients dans l'élément <datalist> prévu pour la recherche.
 * @param {Object} dataId - Objet contenant les identifiants des clients.
 */
function afficher_dataListe(dataId) {
	if (!dataId || Object.keys(dataId).length === 0) return;

	const dataListe = document.getElementById("data-recherche");
	dataListe.innerHTML = ""; // On vide le datalist avant de le remplir

	Object.keys(dataId).forEach((id) => {
		const option = document.createElement("option");
		option.value = id; // On ajoute chaque identifiant en tant qu'option
		dataListe.appendChild(option);
	});
}

/**
 * Récupère les logs depuis l'API et renvoie les données de log si l'appel est réussi.
 * @returns {Promise<Array|null>} Tableau de logs ou null si erreur.
 */
async function getLog() {
	try {
		const response = await fetch("../api/get_log.php", {
			method: "POST",
			headers: { "Content-Type": "application/json" },
		});

		// Vérifie le statut HTTP avant de convertir la réponse en JSON
		if (!response.ok) {
			console.error(`Erreur serveur : Statut ${response.status}`);
			return null;
		}

		const json = await response.json();

		if (json.success === true) {
			return json.data; // Renvoie les données de log reçues
		}
		return null;
	} catch (e) {
		console.error("Erreur lors de la récupération des logs : " + e);
		return null;
	}
}

/**
 * Convertit les séquences ANSI en HTML sécurisée pour l'affichage.
 * @param {string} input - Chaîne contenant des séquences ANSI.
 * @returns {string} Chaîne HTML avec formatage coloré.
 */
function ansiToHtml(input) {
	// Échappe les caractères spéciaux HTML pour éviter les problèmes d'affichage.
	let text = input
		.replace(/&/g, "&amp;")
		.replace(/</g, "&lt;")
		.replace(/>/g, "&gt;");

	// Ferme les balises <span> précédemment ouvertes par les séquences ANSI.
	text = text.replace(/\x1b\[0m/g, "</span>");

	// Transforme les niveaux de log en éléments HTML stylés.
	const warningPad = "WARNING".padEnd(8, " ").replace(/ /g, "&nbsp;");
	text = text.replace(
		/\x1b\[38;5;208mWARNING/g,
		`<span style="color: orangered; font-weight: bold;">${warningPad}`,
	);

	const errorPad = "CRITICAL".padEnd(8, " ").replace(/ /g, "&nbsp;");
	text = text.replace(
		/\x1b\[31mCRITICAL/g,
		`<span style="color: red; font-weight: bold;">${errorPad}`,
	);

	const infoPad = "INFO".padEnd(8, " ").replace(/ /g, "&nbsp;");
	text = text.replace(
		/\x1b\[33mINFO/g,
		`<span style="color: yellow; font-weight: bold;">${infoPad}`,
	);

	return text;
}

/**
 * Crée et affiche les éléments HTML des logs dans la page.
 * @param {Array|null} logs - Tableau de chaînes de logs.
 */
function creerLog(logs) {
	if (logs === null || !Array.isArray(logs)) {
		console.error("Impossible de charger les logs ou format invalide");
		return;
	}

	const conteneur = document.getElementById("logs-display");
	if (conteneur === null) {
		console.error(
			"Erreur : Le conteneur #logs-display n'existe pas dans le DOM",
		);
		return;
	}

	conteneur.innerHTML = ""; // On vide le conteneur avant d'ajouter les nouveaux logs

	logs.forEach((message) => {
		const p = document.createElement("p");
		// On récupère la date et le type de log à partir du message
		p.dataset.date = message.match(/\[(\d{4}-\d{2}-\d{2})/)[1];
		let type = message.match(/([A-Z]+)\s*\x1b\[0m/)[1];
		p.dataset.type = type.toLowerCase();
		p.innerHTML = ansiToHtml(message); // On convertit les séquences ANSI en HTML
		conteneur.appendChild(p); // On ajoute chaque message au DOM
	});
}

/**
 * Bascule l'affichage entre la vue utilisateur et la vue logs.
 * @param {string} id - ID du conteneur à afficher.
 * @param {HTMLElement} button - Bouton cliqué pour changer de vue.
 */
function afficher(id, button) {
	if (id === "") return;

	const userElement = document.getElementById("user-display");
	const logElement = document.getElementById("logs-display");
	const btnUser = document.getElementById("user");
	const btnLogs = document.getElementById("logs");

	if (id === "user-display") {
		// Affiche la vue utilisateur et masque la vue logs
		userElement.style.display = "block";
		logElement.style.display = "none";
		document.getElementById("log-container").style.display = "none";
	} else {
		// Affiche la vue logs et masque la vue utilisateur
		logElement.style.display = "block";
		document.querySelector("#erreur")?.remove(); // Supprime le message d'erreur s'il existe
		userElement.style.display = "none";
		document.getElementById("log-container").style.display = "block";
	}

	btnUser.classList.remove("check-button");
	btnLogs.classList.remove("check-button");
	button.classList.add("check-button"); // Met en surbrillance le bouton actif
}

/**
 * Initialise les données du client au chargement de la page.
 */
async function init() {
	var id_client = await get_id();
	afficher_dataListe(id_client);
}

/**
 * Initialise les logs et lance le tri initial.
 */
async function init_logs() {
	var log = await getLog();
	creerLog(log);
	trieLog();
}

/**
 * Trie et filtre les logs affichés selon le type sélectionné.
 */
function trieLog() {
	const choix = document.getElementById("choix-log").value; // Type de logs choisi
	const tousLesLogs = document.querySelectorAll("#logs-display p");
	document.querySelector("#erreur")?.remove(); // supprime le message d'erreur s'il existe
	let flag = true; // Indique si aucun log n'a été trouvé pour le filtre

	tousLesLogs.forEach((log) => {
		const typeDuLog = log.getAttribute("data-type");

		if (choix === "all" || typeDuLog === choix) {
			log.style.display = "block"; // On affiche les logs correspondant au filtre
			flag = false;
		} else {
			log.style.display = "none"; // On cache les logs qui ne correspondent pas
		}
	});

	if (flag) {
		// Si aucun log n'a été trouvé pour le filtre, on affiche un message d'erreur
		const e = document.createElement("p");
		e.id = "erreur";
		e.textContent = "Pas de log de ce type trouvée";
		document.getElementById("logs-display").appendChild(e);
	}
}

// Initialisation du module et actualisation régulière des logs.
init();
init_logs();
setInterval(init_logs, 10000); // On vérifie les nouveaux logs toutes les 10 secondes
