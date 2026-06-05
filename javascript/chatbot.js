/*
Fonctionnement général :
Un arbre de décision (FLOW) définit le parcours de l'utilisateur : chaque nœud
contient une question, des options cliquables, et éventuellement une fonction
resolve() qui calcule la recommandation finale à partir des choix accumulés.
Les choix de l'utilisateur sont concaténés lettre par lettre dans la variable
"answers" (ex. "bac"), qui sert de clé dans les tables de correspondance des
nœuds resolve() pour retourner le plat ou la boisson recommandé.
L'interface est injectée dynamiquement dans le DOM via injectHTML(), puis pilotée
par des fonctions qui créent et insèrent des bulles de chat (bot et utilisateur),
simulent une frappe (showTyping), et affichent une carte résultat finale.
Le bouton flottant permet d'ouvrir/fermer la fenêtre de chat, et un bouton
« Choisir autre chose » réinitialise entièrement la conversation.
 */
(function () {
	const TXT = window.CHATBOT_TEXT || {};
	const PLATS = window.PLATS_TEXT || {};
	const IS_FRENCH = window.IS_FRENCH !== false;

	function t(key, fallback) {
		return TXT[key] || fallback;
	}

	function nomPlat(nom) {
		if (!PLATS[nom]) return nom;
		return IS_FRENCH
			? PLATS[nom].nom
			: PLATS[nom].nom_eng || PLATS[nom].nom;
	}

	//arbre de décision
	const FLOW = {
		start: {
			q: t(
				"start_q",
				"Bonsoir ! Permettez-moi de vous guider dans votre choix ce soir. Que souhaitez-vous ?",
			),
			opts: [
				{ label: t("starter", "Entrée"), next: "entree_q1" },
				{ label: t("main", "Plat"), next: "plat_q1" },
				{ label: t("dessert", "Dessert"), next: "dessert_q1" },
				{ label: t("drink", "Boisson"), next: "boisson_q1" },
			],
		},

		//Entrées
		entree_q1: {
			q: t("starter_q1", "Préférez-vous une entrée végétarienne ?"),
			opts: [
				{ label: t("yes", "Oui"), val: "a", next: "entree_q2" },
				{ label: t("no", "Non"), val: "b", next: "entree_q2" },
				{
					label: t("no_preference", "Sans préférence"),
					val: "c",
					next: "entree_q2",
				},
			],
		},
		entree_q2: {
			q: t(
				"starter_q2",
				"Vous avez envie de quelque chose de léger ou de plus consistant ?",
			),
			opts: [
				{ label: t("light", "Léger"), val: "a" },
				{ label: t("more_filling", "Plus consistant"), val: "b" },
				{ label: t("no_preference", "Sans préférence"), val: "c" },
			],
			// ans = concaténation des val choisies à chaque étape (ex: "ab" = végétarien + consistant)
			// la map associe chaque combinaison de lettres à un plat recommandé
			resolve: function (ans) {
				let map = {
					aa: "Burrata",
					ab: "Focaccia",
					ac: "Burrata",
					ba: "Carpaccio",
					bb: "Focaccia",
					bc: "Carpaccio",
					ca: "Carpaccio",
					cb: "Focaccia",
					cc: "Burrata",
				};
				return { dish: map[ans], cat: t("cat_starter", "Entrée") };
				// retourne un objet avec le nom du plat et la catégorie pour l'affichage de la carte résultat
			},
		},

		//Plats
		plat_q1: {
			q: t("main_q1", "Préférez-vous un plat végétarien ?"),
			opts: [
				{ label: t("yes", "Oui"), val: "a", next: "plat_q2" },
				{ label: t("no", "Non"), val: "b", next: "plat_q2" },
				{
					label: t("no_preference", "Sans préférence"),
					val: "c",
					next: "plat_q2",
				},
			],
		},
		plat_q2: {
			q: t("main_q2", "Quel type de plat vous fait envie ?"),
			opts: [
				{
					label: t("pasta_rice", "Pâtes / riz"),
					val: "a",
					next: "plat_q3",
				},
				{ label: t("pizza", "Pizza"), val: "b", next: "plat_q3" },
				{
					label: t("no_preference", "Sans préférence"),
					val: "c",
					next: "plat_q3",
				},
			],
		},
		plat_q3: {
			q: t("main_q3", "Vous cherchez quelque chose de…"),
			opts: [
				{ label: t("simple", "Simple / classique"), val: "a" },
				{ label: t("premium", "Raffiné / premium"), val: "b" },
				{ label: t("no_preference", "Sans préférence"), val: "c" },
			],
			// ans = clé à 3 lettres
			// la map couvre toutes les combinaisons possibles
			resolve: function (ans) {
				let map = {
					aaa: "Pâtes au pesto",
					aab: "Risotto",
					aac: "Pâtes au pesto",
					aba: "Pizza Burrata",
					abb: "Pizza Burrata",
					abc: "Pizza Burrata",
					aca: "Pâtes au pesto",
					acb: "Risotto",
					acc: "Risotto",
					baa: "Lasagne dorée",
					bab: "Pâtes aux gambas",
					bac: "Lasagne dorée",
					bba: "Pizza Truffe",
					bbb: "Pizza Cicerone",
					bbc: "Pizza Truffe",
					bca: "Lasagne dorée",
					bcb: "Pizza Cicerone",
					bcc: "Pizza Truffe",
					caa: "Pâtes au pesto",
					cab: "Pâtes aux gambas",
					cac: "Lasagne dorée",
					cba: "Pizza Burrata",
					cbb: "Pizza Cicerone",
					cbc: "Pizza Truffe",
					cca: "Lasagne dorée",
					ccb: "Pizza Cicerone",
					ccc: "Risotto",
				};
				return { dish: map[ans], cat: t("cat_main", "Plat") };
			},
		},

		//Desserts
		dessert_q1: {
			q: t("dessert_q1", "Préférez-vous un dessert accompagné de café ?"),
			opts: [
				{ label: t("yes", "Oui"), val: "a", next: "dessert_q2" },
				{ label: t("no", "Non"), val: "b", next: "dessert_q2" },
				{
					label: t("no_preference", "Sans préférence"),
					val: "c",
					next: "dessert_q2",
				},
			],
		},
		dessert_q2: {
			q: t("dessert_q2", "Vous avez plutôt envie de quelque chose de…"),
			opts: [
				{ label: t("fresh", "Léger / frais"), val: "a" },
				{ label: t("creamy", "Crémeux / gourmand"), val: "b" },
				{ label: t("no_preference", "Sans préférence"), val: "c" },
			],
			resolve: function (ans) {
				let map = {
					aa: "Affogato",
					ab: "Tiramisu",
					ac: "Tiramisu",
					ba: "Panna cotta",
					bb: "Panna cotta",
					bc: "Panna cotta",
					ca: "Affogato",
					cb: "Tiramisu",
					cc: "Panna cotta",
				};
				return { dish: map[ans], cat: t("cat_dessert", "Dessert") };
			},
		},

		//Boissons
		boisson_q1: {
			q: t("drink_q1", "Souhaitez-vous une boisson alcoolisée ?"),
			opts: [
				{
					label: t("wine", "Oui, un vin"),
					val: "a",
					next: "boisson_vin",
				},
				{
					label: t("coffee", "Non, un café"),
					val: "b",
					next: "boisson_cafe",
				},
				{
					label: t("no_preference", "Sans préférence"),
					val: "c",
					next: "boisson_vin",
				},
			],
		},
		boisson_vin: {
			q: t("wine_q", "Quel style de vin vous tente ce soir ?"),
			opts: [
				{ label: t("light_wine", "Léger et frais"), val: "a" },
				{ label: t("strong_wine", "Corsé et intense"), val: "b" },
				{ label: t("no_preference", "Sans préférence"), val: "c" },
			],
			//ans.slice(-1) récupère uniquement la dernière lettre accumulée car seul le dernier choix détermine le résultat ici
			resolve: function (ans) {
				let last = ans.slice(-1);
				let map = {
					a: "Etto Germano",
					b: "Giacomo Boveri",
					c: "Albino Rocca",
				};
				return { dish: map[last], cat: t("cat_wine", "Boisson · Vin") };
			},
		},
		boisson_cafe: {
			q: t("coffee_q", "Quel type de café préférez-vous ?"),
			opts: [
				{ label: t("short_intense", "Court et intense"), val: "a" },
				{ label: t("sweet_milk", "Doux et lacté"), val: "b" },
				{ label: t("no_preference", "Sans préférence"), val: "c" },
			],
			// même logique que boisson_vin -> seule la dernière lettre compte pour le café
			resolve: function (ans) {
				let last = ans.slice(-1);
				let map = {
					a: "Espresso",
					b: "Latte macchiato",
					c: "Latte macchiato",
				};
				return {
					dish: map[last],
					cat: t("cat_coffee", "Boisson · Café"),
				};
			},
		},
	};

	//stocke la suite de lettres représentant les choix de l'utilisateur (ex: "bac")
	//réinitialisée à "" à chaque redémarrage du chatbot
	let answers = "";

	// Références aux éléments du DOM créés dynamiquement par injectHTML()
	// déclarées ici pour être accessibles dans toutes les fonctions
	let toggleBtn, chatWindow, messagesEl;

	//Initialisation
	function init() {
		injectHTML(); //créer et insère le HTML du chatbot dans la page
		//récupère les références DOM après injection
		toggleBtn = document.getElementById("chatbot-toggle");
		chatWindow = document.getElementById("chatbot-window");
		messagesEl = document.getElementById("cb-messages");
		toggleBtn.addEventListener("click", toggleChat); // branche l'ouverture/fermeture sur le bouton
		showStep("start"); // affiche la première question dès l'initialisation
	}

	function injectHTML() {
		//créer un div temporaire pour y insérer le HTML via innerHTML
		let el = document.createElement("div");
		el.innerHTML = [
			'<button id="chatbot-toggle" aria-label="' +
				esc(t("aria_open", "Ouvrir le conseiller de table")) +
				'">',
			'<svg class="icon-chat" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">',
			'<path d="M20 2H4a2 2 0 00-2 2v18l4-4h14a2 2 0 002-2V4a2 2 0 00-2-2z"/>',
			"</svg>",
			'<svg class="icon-close" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">',
			'<path d="M18 6L6 18M6 6l12 12" stroke="#c9a84c" stroke-width="2.5" stroke-linecap="round" fill="none"/>',
			"</svg>",
			"</button>",
			'<div id="chatbot-window" role="dialog" aria-label="' +
				esc(t("aria_dialog", "Conseiller de table")) +
				'" aria-live="polite">',
			// aria-live="polite" : annonce les nouveaux messages aux lecteurs d'écran
			'<div class="cb-header">',
			'<p class="cb-header-title">L\'oro di Cicerone</p>',
			'<p class="cb-header-sub">' +
				esc(t("header_subtitle", "Votre conseiller de table")) +
				"</p>",
			"</div>",
			'<div class="cb-messages" id="cb-messages"></div>',
			"</div>",
		].join(""); // join("") assemble le tableau en une seule chaîne sans séparateur
		document.body.appendChild(el);
	}

	//Toggle ouverture/fermeture
	function toggleChat() {
		// vérifie si la fenêtre est actuellement ouverte via la classe CSS "visible"
		let isOpen = chatWindow.classList.contains("visible");
		if (isOpen) {
			chatWindow.classList.remove("visible");
			toggleBtn.classList.remove("open");
		} else {
			chatWindow.classList.add("visible");
			toggleBtn.classList.add("open");
			scrollDown();
		}
	}

	//Affichage d'une étape
	function showStep(key) {
		let node = FLOW[key]; // récupère le nœud correspondant à l'étape dans l'arbre flow
		if (!node) return; //stop si la clé n'existe pas dans flow

		//affiche l'indicateur de frappe puis execute le callback une fois le délai écoulé
		showTyping(function () {
			let row = addBotBubble(node.q); //affiche la question du bot et retourne l'élément DOM créé
			let grid = document.createElement("div");
			grid.className = "cb-btn-grid"; //conteneur flex pour aligner les boutons de choix
			//créer un bouton pour chaque option du nœud courant
			node.opts.forEach(function (opt) {
				let btn = document.createElement("button");
				btn.className = "cb-choice-btn";
				btn.textContent = opt.label;
				// passe opt, node et grid à handleChoice pour qu'elle puisse accumuler la valeur, désactiver les boutons et naviguer vers l'étape suivante
				btn.addEventListener("click", function () {
					handleChoice(opt, node, grid);
				});
				grid.appendChild(btn);
			});
			// ajoute la grille de boutons dans le conteneur .cb-msg de la bulle bot
			row.querySelector(".cb-msg").appendChild(grid);
			scrollDown();
		});
	}

	//Gestion d'un choix
	function handleChoice(opt, node, grid) {
		// désactive tous les boutons du groupe pour empêcher un double-clic
		grid.querySelectorAll(".cb-choice-btn").forEach(function (b) {
			b.disabled = true;
		});
		grid.style.opacity = "0.35"; // atténue visuellement les boutons pour indiquer qu'ils sont verrouillés
		addUserBubble(opt.label); // affiche le choix de l'utilisateur dans une bulle à droite
		if (opt.val) answers += opt.val; // accumule la lettre du choix dans answers
		if (node.resolve) {
			// si le nœud possède une fonction resolve, c'est la dernière question de la branche :
			// on calcule le résultat final et on affiche la carte de recommandation
			let result = node.resolve(answers);
			showResult(result);
		} else {
			// sinon on passe à l'étape suivante indiquée par opt.next
			let nextKey = opt.next;
			showStep(nextKey);
		}
	}

	//Affichage du résultat
	function showResult(result) {
		// affiche d'abord l'indicateur de frappe pour simuler une réponse naturelle
		showTyping(function () {
			// construit la structure DOM de la carte résultat manuellement
			// pour avoir un contrôle précis sur chaque élément
			let row = document.createElement("div");
			row.className = "cb-bot-row"; // aligne l'avatar et la bulle horizontalement via flexbox CSS
			let avatarEl = document.createElement("div");
			avatarEl.className = "cb-avatar";
			avatarEl.textContent = "L"; // initiale du restaurant
			let msgEl = document.createElement("div");
			msgEl.className = "cb-msg bot";
			msgEl.style.maxWidth = "95%"; // élargi pour que la carte résultat ait plus d'espace
			let card = document.createElement("div");
			card.className = "cb-result-card";
			// esc() protège contre l'injection HTML en cas de données inattendues dans result
			card.innerHTML = [
				'<p class="cb-result-cat">' + esc(result.cat) + "</p>", // catégorie
				'<p class="cb-result-dish">' +
					esc(nomPlat(result.dish)) +
					"</p>", // nom du plat recommandé
				'<div class="cb-divider"></div>',
				'<p class="cb-result-note">' +
					esc(
						t(
							"result_note",
							"Notre suggestion pour vous ce soir. Buon appetito !",
						),
					) +
					"</p>",
				'<button class="cb-restart-btn">' +
					esc(t("restart", "Choisir autre chose")) +
					"</button>",
			].join("");
			// branche le redémarrage sur le bouton après que innerHTML a créé l'élément
			card.querySelector(".cb-restart-btn").addEventListener(
				"click",
				restart,
			);
			msgEl.appendChild(card);
			row.appendChild(avatarEl);
			row.appendChild(msgEl);
			messagesEl.appendChild(row);
			scrollDown();
		});
	}

	//Redémarrage
	function restart() {
		answers = ""; // réinitialise la chaîne de choix pour repartir du début
		messagesEl.innerHTML = ""; // efface tous les messages affichés
		showStep("start"); // relance le chatbot
	}

	//helpers DOM

	function addBotBubble(text) {
		// créer la rangée avatar + bulle pour un message du bot
		let row = document.createElement("div");
		row.className = "cb-bot-row";
		let avatarEl = document.createElement("div");
		avatarEl.className = "cb-avatar";
		avatarEl.textContent = "L";
		let msgEl = document.createElement("div");
		msgEl.className = "cb-msg bot";
		let bubble = document.createElement("div");
		bubble.className = "cb-bubble";
		bubble.textContent = text; // textContent -> pas de risque d'injection
		msgEl.appendChild(bubble);
		row.appendChild(avatarEl);
		row.appendChild(msgEl);
		messagesEl.appendChild(row);
		scrollDown();
		return row; // retourne la rangée pour que showStep() puisse y ajouter la grille de boutons
	}

	function addUserBubble(text) {
		// bulle alignée à droite
		let msgEl = document.createElement("div");
		msgEl.className = "cb-msg user";
		let bubble = document.createElement("div");
		bubble.className = "cb-bubble";
		bubble.textContent = text;
		msgEl.appendChild(bubble);
		messagesEl.appendChild(msgEl);
		scrollDown();
	}

	function showTyping(cb) {
		// affiche temporairement trois points animés pour simuler la "frappe" du bot
		let row = document.createElement("div");
		row.className = "cb-bot-row";
		let avatarEl = document.createElement("div");
		avatarEl.className = "cb-avatar";
		avatarEl.textContent = "L";
		let msgEl = document.createElement("div");
		msgEl.className = "cb-msg bot";
		let bubble = document.createElement("div");
		bubble.className = "cb-bubble cb-typing"; // cb-typing déclenche l'animation CSS des points
		bubble.innerHTML =
			'<div class="cb-dot"></div><div class="cb-dot"></div><div class="cb-dot"></div>';
		msgEl.appendChild(bubble);
		row.appendChild(avatarEl);
		row.appendChild(msgEl);
		messagesEl.appendChild(row);
		scrollDown();
		// après 650ms, supprime l'indicateur et exécute le callback (affichage du vrai message)
		setTimeout(function () {
			messagesEl.removeChild(row);
			cb();
		}, 650);
	}

	function scrollDown() {
		// léger délai de 60ms pour laisser le DOM se mettre à jour avant de calculer scrollHeight
		setTimeout(function () {
			messagesEl.scrollTop = messagesEl.scrollHeight; // fait défiler vers le bas pour montrer le dernier message
		}, 60);
	}

	function esc(s) {
		// échappe les caractères HTML spéciaux pour éviter toute injection dans innerHTML
		return String(s)
			.replace(/&/g, "&amp;") // & doit être échappé en premier pour ne pas double-échapper les suivants
			.replace(/</g, "&lt;")
			.replace(/>/g, "&gt;")
			.replace(/"/g, "&quot;");
	}

	//Lancement
	// si le DOM n'est pas encore prêt, attend l'événement DOMContentLoaded avant d'initialiser
	// sinon appelle init() immédiatement (cas où le script est chargé après le DOM)
	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", init);
	} else {
		init();
	}
})();
