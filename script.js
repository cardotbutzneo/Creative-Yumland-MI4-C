setInterval(async () => {
	const response = await fetch("../api/est_banni.php");
	const data = await response.json();
	if (data.banned) {
		alert("Vous avez été banni. Raison : " + data.reason);
		window.location.href = "/html/index.php";
	}
}, 10000); // Vérifie toutes les 10 secondes

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
