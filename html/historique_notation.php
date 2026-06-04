```php
<?php
session_start();

require_once __DIR__."/../api/config.php";

verifier_connexion($_SESSION["role"], "Cuisinier");

// Récupère toutes les commandes depuis les données chargées dans la configuration.
$bdd_cmd = $data_commandes;
// Garde uniquement les commandes dont l'état indique qu'elles ont été notées.
$commandes_notees = array_filter($bdd_cmd, fn($cmd) => $cmd["etat"] === "notee");
// Initialise les totaux qui serviront au calcul des moyennes.
$total_livraison = 0;
$total_produits = 0;
// Compte le nombre total de commandes notées.
$nb = count($commandes_notees);
// Additionne toutes les notes de livraison et de produits.
foreach ($commandes_notees as $cmd) {
    $total_livraison += $cmd["note_livraison"];
    $total_produits  += $cmd["note_produits"];
}
// Calcule la moyenne des notes de livraison.
// Si aucune commande n'est notée, la moyenne vaut null.
$moy_livraison = $nb > 0 ? round($total_livraison / $nb, 1) : null;
// Calcule la moyenne des notes des produits.
// Si aucune commande n'est notée, la moyenne vaut null.
$moy_produits  = $nb > 0 ? round($total_produits  / $nb, 1) : null;
// Trie les commandes notées de la plus récente à la plus ancienne.
uasort($commandes_notees, fn($a, $b) => strtotime($b["date"]) - strtotime($a["date"]));
/**
 * Transforme une note numérique sur 5 en chaîne d'étoiles.
 *
 * @param int $note Note comprise entre 0 et 5.
 * @return string Représentation visuelle de la note avec des étoiles.
 */
function etoiles(int $note): string {
    return str_repeat("★", $note) . str_repeat("☆", 5 - $note);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des notations - L'oro di Cicerone</title>
    <link href="https://fonts.googleapis.com/css2?family=Monsieur+La+Doulaise&family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/historique_notation.css">
    <script src="../javascript/commande.js" defer></script>
    <script src="../script.js"></script>
</head>
<body>
    <header>
        <a href="commandes.php"><h1>L'oro di Cicerone</h1></a>
        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="commandes.php">Commandes</a></li>
                <li><a href="deconnexion.php">se déconnecter</a></li>
            </ul>
        </nav>
    </header>

    <div id="btn-affichage">
        <div id="conteneur-btn">
            <button id="btn-avis" class="actif" onclick="changerAffichage('btn-avis')">Avis</button>
            <button id="btn-cmd" onclick="changerAffichage('btn-cmd')">Meilleurs commandes</button>
        </div>
    </div>

    <main class="page-notations">
        <h1>Historique des notations</h1>

        <?php if ($nb === 0){ ?>
            <div class="aucun-avis">Aucune notation enregistrée pour le moment.</div>
        <?php } else{ ?>
            <div class="moyennes">
                <div class="moyenne-card">
                    <div class="moyenne-label">Livraison</div>
                    <div class="moyenne-note"><?= number_format($moy_livraison, 1, ',', '') ?> / 5</div>
                    <div class="moyenne-etoiles">
                        <?php
                        // Arrondit la moyenne pour l'afficher sous forme d'étoiles complètes.
                        $plein = round($moy_livraison);
                        // Affiche les étoiles pleines correspondant à la moyenne.
                        echo str_repeat('<span class="plein">★</span>', $plein);
                        // Complète l'affichage avec des étoiles vides jusqu'à 5.
                        echo str_repeat('<span class="vide">☆</span>', 5 - $plein);
                        ?>
                    </div>
                    <div class="nb-avis"><?= $nb ?> avis</div>
                </div>

                <div class="moyenne-card">
                    <div class="moyenne-label">Produits</div>
                    <div class="moyenne-note"><?= number_format($moy_produits, 1, ',', '') ?> / 5</div>
                    <div class="moyenne-etoiles">
                        <?php
                        // Arrondit la moyenne des produits pour afficher des étoiles entières.
                        $plein = round($moy_produits);
                        // Affiche les étoiles pleines.
                        echo str_repeat('<span class="plein">★</span>', $plein);
                        // Affiche les étoiles vides restantes.
                        echo str_repeat('<span class="vide">☆</span>', 5 - $plein);
                        ?>
                    </div>
                    <div class="nb-avis"><?= $nb ?> avis</div>
                </div>
            </div>

            <div class="filtres">
                <label for="tri">Trier par :</label>
                <select id="tri" onchange="trierAvis(this.value)">
                    <option value="date">Date (plus récent)</option>
                    <option value="note_livraison_desc">Livraison ↓</option>
                    <option value="note_livraison_asc">Livraison ↑</option>
                    <option value="note_produits_desc">Produits ↓</option>
                    <option value="note_produits_asc">Produits ↑</option>
                </select>
            </div>

            <div id="nouvelles-commandes-notees" class="aucun-avis" style="display: none;"></div>

            <div class="avis-liste" id="avis-liste">
                <?php foreach ($commandes_notees as $id => $cmd){ ?>
                    <div class="avis-card"
                         data-id="<?= htmlspecialchars($id) ?>"
                         data-date="<?= strtotime($cmd['date']) ?>"
                         data-livraison="<?= htmlspecialchars($cmd['note_livraison']) ?>"
                         data-produits="<?= htmlspecialchars($cmd['note_produits']) ?>">

                        <div class="avis-header">
                            <div class="avis-meta">
                                <span class="numero">Commande n° <?= htmlspecialchars($cmd["numero"] ?? $id) ?></span>
                                <span class="avis-email"><?= htmlspecialchars($cmd["email"]) ?></span>
                            </div>
                            <div class="avis-date"><?= htmlspecialchars(date("d/m/Y à H:i", strtotime($cmd["date"]))) ?></div>
                        </div>

                        <div class="avis-notes">
                            <div class="note-ligne">
                                <span class="note-intitule">Livraison</span>
                                <span class="note-etoiles">
                                    <?php
                                    // Récupère la note de livraison de la commande actuelle.
                                    $n = $cmd["note_livraison"];
                                    // Affiche les étoiles pleines correspondant à la note.
                                    echo str_repeat('<span class="plein">★</span>', $n);
                                    // Affiche les étoiles vides restantes.
                                    echo str_repeat('<span class="vide">☆</span>', 5 - $n);
                                    ?>
                                </span>
                            </div>

                            <div class="note-ligne">
                                <span class="note-intitule">Produits</span>
                                <span class="note-etoiles">
                                    <?php
                                    // Récupère la note des produits de la commande actuelle.
                                    $n = $cmd["note_produits"];
                                    // Affiche les étoiles pleines correspondant à la note.
                                    echo str_repeat('<span class="plein">★</span>', $n);
                                    // Affiche les étoiles vides restantes.
                                    echo str_repeat('<span class="vide">☆</span>', 5 - $n);
                                    ?>
                                </span>
                            </div>
                        </div>

                        <?php if (!empty($cmd["commentaire"])){ ?>
                            <div class="avis-commentaire"><?= htmlspecialchars($cmd["commentaire"]) ?></div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>

            <div class="filtres" id="filtre-cmd" style="display : none">
                <label for="tri-cmd">Trier par :</label>
                <select id="tri-cmd" onchange="creerMeilleursCmd()">
                    <option value="all">Tout</option>
                    <option value="entrees">Entrées</option>
                    <option value="plats">Plats</option>
                    <option value="desserts">Desserts</option>
                    <option value="vins">Vins</option>
                    <option value="cafes">Cafés</option>
                </select>
            </div>

            <div class="avis-card" id="meilleurs-cmd" style="display : none">
                Chargement des meilleurs commandes...
            </div>
        <?php } ?>
    </main>

    <script>
        // Trie les cartes d'avis dans la page selon le critère choisi par l'utilisateur.
        function trierAvis(critere) {
            // Récupère le conteneur qui contient toutes les cartes d'avis.
            const liste = document.getElementById("avis-liste");
            // Si le conteneur n'existe pas, on arrête la fonction.
            if (!liste) return;
            // Transforme la liste des cartes HTML en tableau JavaScript pour pouvoir les trier.
            const cartes = Array.from(liste.querySelectorAll(".avis-card"));
            // Trie les cartes en fonction du critère sélectionné.
            cartes.sort((a, b) => {
                switch (critere) {
                    case "date":
                        // Trie par date décroissante, donc du plus récent au plus ancien.
                        return b.dataset.date - a.dataset.date;

                    case "note_livraison_desc":
                        // Trie les notes de livraison de la meilleure à la moins bonne.
                        return b.dataset.livraison - a.dataset.livraison;

                    case "note_livraison_asc":
                        // Trie les notes de livraison de la moins bonne à la meilleure.
                        return a.dataset.livraison - b.dataset.livraison;

                    case "note_produits_desc":
                        // Trie les notes des produits de la meilleure à la moins bonne.
                        return b.dataset.produits - a.dataset.produits;

                    case "note_produits_asc":
                        // Trie les notes des produits de la moins bonne à la meilleure.
                        return a.dataset.produits - b.dataset.produits;

                    default:
                        // Par défaut, on trie par date décroissante.
                        return b.dataset.date - a.dataset.date;
                }
            });

            // Replace les cartes triées dans le conteneur.
            cartes.forEach(c => liste.appendChild(c));
        }
        // Protège l'affichage HTML en remplaçant les caractères spéciaux.
        // Cela évite qu'un texte utilisateur soit interprété comme du code HTML.
        function echapperHTML(valeur) {
            return String(valeur ?? "")
                .replaceAll("&", "&amp;")
                .replaceAll("<", "&lt;")
                .replaceAll(">", "&gt;")
                .replaceAll('"', "&quot;")
                .replaceAll("'", "&#039;");
        }
        // Transforme une note numérique en étoiles HTML.
        function creerEtoiles(note) {
            // Convertit la note en entier et la limite entre 0 et 5.
            const n = Math.max(0, Math.min(5, parseInt(note, 10) || 0));

            // Crée les étoiles pleines, puis complète avec des étoiles vides.
            return '<span class="plein">★</span>'.repeat(n)
                 + '<span class="vide">☆</span>'.repeat(5 - n);
        }
        // Convertit une date de commande en format français lisible.
        function formaterDateCommande(dateSQL) {
            // Remplace l'espace entre la date et l'heure par "T" pour améliorer la compatibilité avec new Date().
            const date = new Date(String(dateSQL).replace(" ", "T"));

            // Si la date est invalide, on renvoie la valeur de départ en l'échappant.
            if (Number.isNaN(date.getTime())) {
                return echapperHTML(dateSQL);
            }
            // Formate la date au format français avec le jour, le mois, l'année, l'heure et les minutes.
            return date.toLocaleDateString("fr-FR", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit"
            }).replace(",", " à");
        }

        // Crée une carte d'avis complète à partir d'une commande récupérée dynamiquement.
        function creerCarteAvis(id, cmd) {
            // Crée l'élément principal de la carte.
            const carte = document.createElement("div");
            // Ajoute la classe CSS utilisée pour le style des avis.
            carte.className = "avis-card";
            // Stocke les données utiles dans des attributs data-* pour permettre le tri.
            carte.dataset.id = id;
            carte.dataset.date = Math.floor(new Date(String(cmd.date).replace(" ", "T")).getTime() / 1000) || 0;
            carte.dataset.livraison = cmd.note_livraison ?? 0;
            carte.dataset.produits = cmd.note_produits ?? 0;
            // Construit le contenu HTML de la carte avec les informations de la commande.
            // Les données affichées sont échappées pour éviter les injections HTML.
            carte.innerHTML = `
                <div class="avis-header">
                    <div class="avis-meta">
                        <span class="numero">Commande n° ${echapperHTML(cmd.numero ?? id)}</span>
                        <span class="avis-email">${echapperHTML(cmd.email)}</span>
                    </div>
                    <div class="avis-date">${formaterDateCommande(cmd.date)}</div>
                </div>

                <div class="avis-notes">
                    <div class="note-ligne">
                        <span class="note-intitule">Livraison</span>
                        <span class="note-etoiles">${creerEtoiles(cmd.note_livraison)}</span>
                    </div>

                    <div class="note-ligne">
                        <span class="note-intitule">Produits</span>
                        <span class="note-etoiles">${creerEtoiles(cmd.note_produits)}</span>
                    </div>
                </div>

                ${cmd.commentaire ? `<div class="avis-commentaire">${echapperHTML(cmd.commentaire)}</div>` : ""}
            `;
            // Retourne la carte prête à être insérée dans la page.
            return carte;
        }

        // Vérifie s'il existe de nouvelles commandes notées et les ajoute à l'affichage.
        async function verifierNouvellesCommandesNotees() {
            try {
                const response = await fetch("../api/get_new_commande.php", {
                    cache: "no-store"
                });
                // Si la réponse HTTP n'est pas correcte, on affiche l'erreur en console.
                if (!response.ok) {
                    console.error("Erreur API get_new_commande.php :", response.status);
                    return;
                }
                // Convertit la réponse de l'API en objet JavaScript.
                const commandes = await response.json();
                // Récupère la liste actuelle des avis.
                const liste = document.getElementById("avis-liste");
                // Si la liste ou les données n'existent pas, on arrête la fonction.
                if (!liste || !commandes) return;
                // Récupère les identifiants des commandes déjà affichées.
                const idsDejaAffiches = new Set(
                    Array.from(liste.querySelectorAll(".avis-card"))
                        .map(carte => carte.dataset.id)
                        .filter(Boolean)
                );

                // Garde uniquement les commandes notées qui ne sont pas encore affichées.
                const nouvellesCommandes = Object.entries(commandes)
                    .filter(([id, cmd]) => cmd?.etat === "notee" && !idsDejaAffiches.has(id))
                    .sort(([, a], [, b]) => {
                        // Trie les nouvelles commandes de la plus récente à la plus ancienne.
                        return new Date(String(b.date).replace(" ", "T"))
                             - new Date(String(a.date).replace(" ", "T"));
                    });

                // S'il n'y a aucune nouvelle commande, on ne fait rien.
                if (nouvellesCommandes.length === 0) return;
                // Ajoute chaque nouvelle commande au début de la liste.
                nouvellesCommandes.forEach(([id, cmd]) => {
                    liste.prepend(creerCarteAvis(id, cmd));
                });
                // Récupère le bloc prévu pour afficher un message d'information.
                const message = document.getElementById("nouvelles-commandes-notees");
                // Affiche un message indiquant le nombre de nouvelles commandes ajoutées.
                if (message) {
                    const pluriel = nouvellesCommandes.length > 1 ? "s" : "";
                    message.textContent = `${nouvellesCommandes.length} nouvelle${pluriel} commande${pluriel} notée${pluriel} ajoutée${pluriel}.`;
                    message.style.display = "block";
                }
                // Réapplique le tri actuellement sélectionné après l'ajout des nouvelles cartes.
                const selectTri = document.getElementById("tri");
                trierAvis(selectTri?.value ?? "date");
            }
            catch (e) {
                // Affiche en console les erreurs liées à la récupération ou au traitement des données.
                console.error("Erreur lors de la vérification des nouvelles commandes notées :", e);
            }
        }
        // Récupère la liste des plats depuis le fichier PHP prévu pour fournir ces données.
        async function getListePlat(){
            try{
                let response = await fetch("../api/get_plat.php");
                // Convertit la réponse en JSON.
                response = await response.json();

                // Si la réponse indique un échec, on affiche l'erreur et on renvoie null.
                if (!response.success) {
                    console.error("Error : " + (response.erreur ?? ""));
                    return null;
                }
                // Renvoie les données des plats.
                return response.data;
            }
            catch(e){
                // Affiche en console les erreurs liées à la récupération des plats.
                console.error("Error : " + e);
            }
        }
        // Crée l'affichage des plats les plus commandés selon la catégorie sélectionnée.
        async function creerMeilleursCmd(){
            // Récupère les plats depuis la source de données.
            let plats = await getListePlat();
            // Si aucune donnée n'est reçue, on arrête la fonction.
            if (plats == null) return;
            // Convertit l'objet de plats en tableau.
            plats = Object.values(plats);
            // Supprime la première entrée, qui correspond aux allergènes et non à un plat.
            plats.shift();

            // Crée un élément de liste utilisé pour chaque ligne du classement.
            function createConteneur(){
                const conteneur = document.createElement("li");
                conteneur.classList = "podium-liste";
                return conteneur;
            }

            // Stocke les meilleurs plats calculés pour chaque catégorie.
            let liste_categories = [];
            // Liste des catégories disponibles dans le filtre.
            const categorie = ["entrees", "plats", "desserts", "vins", "cafes", "all"];
            // Calcule les meilleurs plats pour chaque catégorie.
            categorie.forEach(cat => {
                const { plat, total } = MeilleurCommandes(plats, cat); 
                liste_categories.push({ categorie: cat, plat, total });
            });
            // Récupère les données globales, toutes catégories confondues.
            const donneesGlobales = liste_categories.find(item => item.categorie === "all");
            // Si les données globales n'existent pas, on arrête la fonction.
            if (!donneesGlobales) return;
            // Récupère la catégorie actuellement choisie dans le menu déroulant.
            const trie = document.querySelector("#filtre-cmd option:checked").value;
            // Récupère les plats correspondant à la catégorie sélectionnée.
            const platsAAfficher = Object.values(liste_categories[categorie.indexOf(trie)]);
            // Récupère le total de commandes toutes catégories confondues.
            const totalCommandes = donneesGlobales.total;
            // Récupère le conteneur HTML où afficher les meilleurs plats.
            const conteneur = document.getElementById("meilleurs-cmd");
            // Vide le contenu actuel avant de reconstruire le classement.
            conteneur.innerHTML = "";
            // Ajoute chaque plat de la catégorie sélectionnée dans le classement.
            platsAAfficher[1].forEach(p => {
                const c = createConteneur();
                // Calcule le pourcentage de commandes du plat par rapport au total.
                const pourcentage = totalCommandes > 0
                    ? Math.round((p.nb_commandee / totalCommandes) * 100)
                    : 0;
                // Crée l'affichage d'une ligne du classement.
                c.innerHTML = `
                    <strong style="color: #f5f5f5; font-weight: 500;">${p.nom}</strong>
                    <span style="color: #666; font-size: 0.8rem;">${p.nb_commandee} commandes</span>
                    <b style="color: #c9a24d; font-weight: 600;">${pourcentage}%</b>
                `;
                // Ajoute la ligne au conteneur.
                conteneur.appendChild(c);
            });
            // Ajoute une dernière ligne indiquant le nombre total de plats commandés.
            const c = createConteneur();
            c.innerHTML = `<h2 style="font-size: 1rem; color: #fff; margin: 0;">Total de plats commandés : ${totalCommandes}</h2>`;
            conteneur.appendChild(c);
        }

        // Change l'onglet affiché entre les avis et les meilleures commandes.
        function changerAffichage(target){
            // Si aucun bouton cible n'est fourni, on arrête la fonction.
            if (target === null || target === undefined) return;
            // Récupère le bouton actuellement actif.
            const btnActuel = document.querySelector("#conteneur-btn button.actif");
            // Retire la classe active de l'ancien bouton.
            btnActuel?.classList.remove("actif");
            // Récupère le nouveau bouton à activer.
            const nouveauBouton = document.getElementById(target);
            // Ajoute la classe active au nouveau bouton.
            nouveauBouton?.classList.add("actif");
            // Récupère les deux conteneurs principaux à afficher ou masquer.
            const conteneurAvis = document.getElementById("avis-liste");
            const conteneurCmd = document.getElementById("meilleurs-cmd");
            // Affiche l'onglet des avis.
            if (target === "btn-avis") {
                document.querySelectorAll(".filtres")[0].style.display = "block";
                document.getElementById("filtre-cmd").style.display = "none";

                conteneurAvis.style.display = "block";
                conteneurCmd.style.display = "none";    
            }
            // Affiche l'onglet des meilleures commandes.
            else if (target === "btn-cmd") {
                document.querySelectorAll(".filtres")[0].style.display = "none";
                document.getElementById("filtre-cmd").style.display = "block";

                conteneurAvis.style.display = "none";
                conteneurCmd.style.display = "block";    
            }
        }
        
        // Génère une première fois la section des meilleures commandes au chargement de la page.
        creerMeilleursCmd();
        // Vérifie immédiatement si de nouvelles commandes notées existent.
        verifierNouvellesCommandesNotees();
        // Relance la vérification des nouvelles commandes notées toutes les 15 secondes.
        setInterval(verifierNouvellesCommandesNotees, 15000);
        // Actualise automatiquement les meilleures commandes toutes les 100 secondes.
        setInterval(creerMeilleursCmd, 100000);
    </script>
</body>
</html>
```
