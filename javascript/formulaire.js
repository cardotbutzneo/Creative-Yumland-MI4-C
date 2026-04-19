function togglePassword(inputId, oeil_ouvert_Id, oeil_ferme_Id) {
	const input = document.getElementById(inputId);
	const eyeOpen = document.getElementById(oeil_ouvert_Id);
	const eyeOff = document.getElementById(oeil_ferme_Id);
	if (input.type === "password") {
		input.type = "text";
		eyeOpen.style.display = "none";
		eyeOff.style.display = "block";
	} else {
		input.type = "password";
		eyeOpen.style.display = "block";
		eyeOff.style.display = "none";
	}
}

document.querySelector("form").addEventListener("submit", function (event) {
	const nom = document.querySelector("input[name='nom']").value.trim();
	const prenom = document.querySelector("input[name='prenom']").value.trim();
	const adresse = document
		.querySelector("input[name='adresse']")
		.value.trim();
	const tel = document.querySelector("input[name='tel']").value.trim();
	const mail = document.querySelector("input[name='mail']").value.trim();
	const password = document.querySelector("input[name='password']").value;
	const confirmer_password = document.querySelector(
		"input[name='confirmer_password']",
	).value;

	let erreurs = [];

	if (nom === "") {
		erreurs.push("Le nom est obligatoire.");
	}
	if (prenom === "") {
		erreurs.push("Le prénom est obligatoire.");
	}
	if (adresse === "") {
		erreurs.push("L'adresse est obligatoire.");
	}

	if (tel === "") {
		erreurs.push("Le téléphone est obligatoire.");
	} else if (tel.length !== 10) {
		erreurs.push("Le téléphone doit contenir exactement 10 chiffres.");
	} else {
		let uniquementChiffres = true;

		for (let i = 0; i < tel.length; i++) {
			if (tel[i] < "0" || tel[i] > "9") {
				uniquementChiffres = false;
			}
		}

		if (uniquementChiffres === false) {
			erreurs.push("Le téléphone doit contenir uniquement des chiffres.");
		}
	}

	if (mail === "") {
		erreurs.push("L'adresse e-mail est obligatoire.");
	} else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(mail)) {
		erreurs.push("L'adresse e-mail n'est pas valide.");
	}

	if (password !== confirmer_password) {
		erreurs.push("Les mots de passe ne correspondent pas.");
	}

	if (erreurs.length > 0) {
		event.preventDefault();

		let div = document.getElementById("message-erreur-js");
		if (div === null) {
			div = document.createElement("div");
			div.id = "message-erreur-js";
			div.className = "message-erreur";
			document.querySelector("form").before(div);
		}

		div.innerHTML = erreurs.join("<br>");
	}
});

function verifierTelephone() {
	let inputTel = document.getElementById("telephone");
	let selectPays = document.getElementById("pays");

	let val = inputTel.value.trim();
	let prefix = selectPays.value;

	// On récupère uniquement les chiffres (on enlève préfixe et espaces)
	let numeroSeul = val.split(" ").slice(1).join("");

	// Initialisation du message
	let message = "";

	if (prefix === "+33") {
		// Validation pour la France : 9 ou 10 chiffres selon s'il y a le "0"
		if (numeroSeul.length < 9 || numeroSeul.length > 10) {
			message = "Le numéro français doit comporter 9 ou 10 chiffres.";
		}
	} else if (numeroSeul.length < 5) {
		// Validation générique pour les autres pays
		message = "Le numéro est trop court.";
	}

	// On applique le message (s'il est vide, le champ devient valide)
	inputTel.setCustomValidity(message);

	// Optionnel : Forcer l'affichage de l'erreur si tu ne veux pas attendre le submit
	if (message !== "") {
		inputTel.reportValidity();
	}
}

function changerPrefixTel() {
	let phoneVal = document.getElementById("pays").value;
	let val = document.getElementById("telephone").value.trim();
	let newValBrut = val.split(" ").slice(1).join("");
	let newVal = (newValBrut.match(/.{1,2}/g) || []).join(" ");
	document.getElementById("telephone").value = phoneVal + " " + newVal;
}

function toogleAbandon() {
	if (document.getElementById("abandon").checked == true)
		document.getElementById("lien-abandon").style.display = "block";
	else document.getElementById("lien-abandon").style.display = "none";
}

function toogleSecurite(choix) {
	if (choix == 1) {
		document.getElementById("settings").style.display = "none";
		document.getElementById("verif").style.display = "block";
	} else if (choix == 2) {
		document.getElementById("verif").style.display = "none";
		document.getElementById("verif_password").style.display = "block";
	}
	else{
		document.getElementById("verif_password").style.display = "none";
		document.getElementById("delete-account").style.display = "block";
	}
}
