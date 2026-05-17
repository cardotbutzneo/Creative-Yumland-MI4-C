function validerConnexion(event) {
    const email = document.querySelector("input[name='email']").value.trim();
    const password = document.querySelector("input[name='password']").value;
 
    let erreurs = [];
 
    //validation e-mail
    if (email === "") {
        erreurs.push("L'adresse e-mail est obligatoire.");
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        erreurs.push("L'adresse e-mail n'est pas valide.");
    }
 
    //validation mot de passe
    if (password === "") {
        erreurs.push("Le mot de passe est obligatoire.");
    }
 
    //affichage des erreurs
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
}