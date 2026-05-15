/**
 * Script JavaScript pour la gestion des commandes et des utilisateurs dans l'interface de la cuisine
 * - Vérifie régulièrement si l'utilisateur est banni et redirige si nécessaire
 * - Permet de bannir ou débannir un utilisateur avec une raison
 * - Permet de changer le rôle d'un utilisateur
 * - Gère les cookies pour la session utilisateur
*/

/**
 * Vérifie régulièrement si l'utilisateur est banni et redirige vers la page de connexion si nécessaire
 * @param {number} interval - L'intervalle de temps en millisecondes pour vérifier le statut de bannissement (par défaut : 10000 ms)
 */
setInterval(async () => {
	const response = await fetch("../api/est_banni.php");
	const data = await response.json();
	if (data.banned) {
		alert("Vous avez été banni. Raison : " + data.reason);
		window.location.href = "/html/index.php";
	}
}, 10000); // Vérifie toutes les 10 secondes

/**
 * Bannit ou débannit un utilisateur
 * @param {string} mail - L'adresse e-mail de l'utilisateur à bannir ou débannir
 * @param {string} action - L'action à effectuer ("Bloquer" ou "Débloquer")
 * @param {HTMLButtonElement} elementBouton - Le bouton qui a déclenché l'action, utilisé pour mettre à jour son texte
 */
async function bannir(mail, action, elementBouton) {
	const raison = prompt("Raison du bannissement : ");
	if (raison === null) return;

	const formData = new FormData();
	formData.append("raison", raison);
	formData.append("banni", "true");
	formData.append("mail", mail);
	formData.append("action_type", action);

	try {
		const response = await fetch("profil_admin.php", {
			method: "POST",
			body: formData,
		});

		if (response.ok) {
			elementBouton.value =
				action === "Bloquer" ? "Débloquer" : "Bloquer";
			console.log("Oppération réussi");
		}
	} catch (error) {
		console.error("Erreur lors du fetch :", error);
	}
}

/**
 * Change le rôle d'un utilisateur
 * @param {string} mail - mail de l'utilisateur dont on veut changer le rôle
 * @param {*} elementRole - valeur du nouveau rôle sélectionné dans le menu déroulant
 */
async function changerRole(mail, elementRole) {
	let nvRole = elementRole.value;
	const formData = new FormData();
	formData.append("nvRole", nvRole);
	formData.append("mail", mail);
	try {
		const response = await fetch("profil_admin.php", {
			method: "POST",
			body: formData,
		});
		if (response.ok) {
			console.log("Changement de role réussi ! nouveau role : " + nvRole);
		}
	} catch (error) {
		console.error("Erreur lors du changement de role");
	}
}

/**
 * Gère les cookies pour la session utilisateur
 * @param {string} name - Le nom du cookie à créer ou mettre à jour
 * @param {string} value - La valeur à stocker dans le cookie
 * @param {number} days - Le nombre de jours avant l'expiration du cookie
 */
function setCookie(name, value, days) {
	let expires = "";
	if (days) {
		let date = new Date();
		date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
		expires = "; expires=" + date.toUTCString();
	}
	// path=/ permet au cookie d'être disponible sur tout ton site
	document.cookie = name + "=" + (value || "") + expires + "; path=/";
}
