setInterval(async () => {
	const response = await fetch("../api/est_banni.php");
	const data = await response.json();
	if (data.banned) {
		alert("Vous avez été banni. Raison : " + data.reason);
		window.location.href = "/html/index.php";
	}
}, 10000); // Vérifie toutes les 10 secondes

function bannir(mail, action, msg) {
	// envoie un post à profil_client avec des données
	const formData = new FormData();
	formData.append("banni", true);
	formData.append("raison", msg);
	formData.append("mail", mail);
	formData.append("action_type", action);

	fetch("profil_admin.php", {
		// envoie des données
		method: "POST",
		body: formData, // Pas besoin de "headers" ici, le navigateur s'en occupe
	});
	window.location.href = "/html/profil_admin.php";
}

function changerRole(mail) {
	let nvRole = document.getElementById("role").value;
	const formData = new FormData();
	formData.append("nvRole", nvRole);
	formData.append("mail", mail);
	fetch("profil_admin.php", {
		method: "POST",
		body: formData,
	});
	window.location.href = "/html/profil_admin.php";
}
