setInterval(async () => {
	const response = await fetch("../est_banni.php");
	const data = await response.json();
	console.log(data);
	if (data.banned) {
		alert("Vous avez été banni. Raison : " + data.reason);
		window.location.href = "/html/index.php";
	}
}, 10000); // Vérifie toutes les 10 secondes

function bannir(mail, action, msg) {
	// envoie un post à profil_client avec des données
	console.log(mail);
	console.log(action);
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
