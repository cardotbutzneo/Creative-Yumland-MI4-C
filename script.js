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
	let raison = "";
	if (action === "Bloquer") {
		raison = prompt("Veuillez entrer la raison du bannissement :");
	}
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

async function verfierURL(url) {
	if (url === "") return;
	try {
		const response = await fetch("api/serveur_errors.php", {
			headers: { "Content-Type": "application/json" },
			method: "POST",
			body: JSON.stringify({ url: url }),
		});
		if (response.ok) {
			const message = response.message;
			console.log("[URL " + url + "]" + message);
		}
	} catch (error) {
		console.error("Erreur : " + error);
	}
}

/**
 * Retourne les 3 plats les plus commandés et le nombre total de commandes pour les entrées et les plats
 * @param {Array} plat Tableau d'objets représentant les plats, chaque objet doit contenir les propriétés "categorie" (string) et "nb_commandee" (number)
 * @param {string} categorie (optionnel) La catégorie de plats à filtrer ("entree", "plat", "dessert", "vin", "cafe" ou "all" pour tous les plats, par défaut : "all")
 * @returns Objet contenant les 3 plats les plus commandés et le nombre total de commandes pour les entrées et les plats
 */
function MeilleurCommandes(liste_plats, categorie = "all") {
    let platsFiltres = liste_plats;
    
    if (categorie !== "all") {
        platsFiltres = liste_plats.filter(p => p.categorie === categorie);
    }

    let total = 0;
    platsFiltres.forEach(p => {
        total += p.nb_commandee ?? 0;
    });

    let podium = [...platsFiltres]
        .sort((a, b) => b.nb_commandee - a.nb_commandee)
        .slice(0, 3);

    // 4. On renvoie le résultat
    return {
        plat: podium,
        total: total
    };
}

/**
 * Revoit la valeur d'un cookie à partir de son nom
 * @param {string} nom nom du cookie à lire 
 * @returns string la valeur du cookie ou null si le cookie n'existe pas
 */
function lireCookie(nom) {
    // On remplace les "; " par des "&"
    const cookiesFormates = document.cookie.replace(/;\s*/g, "&");
    const dechiffreur = new URLSearchParams(cookiesFormates);
    
    // On récupère la valeur directement
    return dechiffreur.get(nom);
}
