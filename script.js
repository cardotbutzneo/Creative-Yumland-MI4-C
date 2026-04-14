async function demanderValidation(mail, estBanni) {
	let password = prompt("Confirmez votre mot de passe administrateur :");
	if (!password) return;

	// 1. On vérifie le mot de passe ET on demande l'action de blocage en une fois
	let response = await fetch("profil_admin.php", {
		method: "POST",
		headers: { "Content-Type": "application/json" },
		body: JSON.stringify({
			action: "bloquer_user",
			password: password,
			mail: mail,
			nouvelEtat: !estBanni, // On inverse l'état actuel
		}),
	});

	let result = await response.json();

	if (result.success) {
		alert("Opération réussie !");
		location.reload(); // On rafraîchit pour voir le changement dans le tableau
	} else {
		alert("Échec : " + (result.error || "Mot de passe incorrect"));
	}
}
