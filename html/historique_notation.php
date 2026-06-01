<?php
session_start();

require_once __DIR__."/../api/config.php";

verifier_connexion($_SESSION["role"], "Cuisinier");

$bdd_cmd = $data_commandes;

//filtrer uniquement les commandes notées
$commandes_notees = array_filter($bdd_cmd, fn($cmd) => $cmd["etat"] === "notee");

//calculer les moyennes globales
$total_livraison = 0;
$total_produits = 0;
$nb = count($commandes_notees);

foreach ($commandes_notees as $cmd) {
    $total_livraison += $cmd["note_livraison"];
    $total_produits  += $cmd["note_produits"];
}

$moy_livraison = $nb > 0 ? round($total_livraison / $nb, 1) : null;
$moy_produits  = $nb > 0 ? round($total_produits  / $nb, 1) : null;

//trier par date décroissante
uasort($commandes_notees, fn($a, $b) => strtotime($b["date"]) - strtotime($a["date"]));

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
                        $plein = round($moy_livraison);
                        echo str_repeat('<span class="plein">★</span>', $plein);
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
                        $plein = round($moy_produits);
                        echo str_repeat('<span class="plein">★</span>', $plein);
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
                                    $n = $cmd["note_livraison"];
                                    echo str_repeat('<span class="plein">★</span>', $n);
                                    echo str_repeat('<span class="vide">☆</span>', 5 - $n);
                                    ?>
                                </span>
                            </div>

                            <div class="note-ligne">
                                <span class="note-intitule">Produits</span>
                                <span class="note-etoiles">
                                    <?php
                                    $n = $cmd["note_produits"];
                                    echo str_repeat('<span class="plein">★</span>', $n);
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
        // Trie les cartes d'avis dans le DOM selon le critère sélectionné.
        function trierAvis(critere) {
            const liste = document.getElementById("avis-liste");
            if (!liste) return;

            const cartes = Array.from(liste.querySelectorAll(".avis-card"));

            cartes.sort((a, b) => {
                switch (critere) {
                    case "date":
                        return b.dataset.date - a.dataset.date;
                    case "note_livraison_desc":
                        return b.dataset.livraison - a.dataset.livraison;
                    case "note_livraison_asc":
                        return a.dataset.livraison - b.dataset.livraison;
                    case "note_produits_desc":
                        return b.dataset.produits - a.dataset.produits;
                    case "note_produits_asc":
                        return a.dataset.produits - b.dataset.produits;
                    default:
                        return b.dataset.date - a.dataset.date;
                }
            });

            cartes.forEach(c => liste.appendChild(c));
        }

        // Échappe les caractères HTML spéciaux pour prévenir les injections XSS.
        function echapperHTML(valeur) {
            return String(valeur ?? "")
                .replaceAll("&", "&amp;")
                .replaceAll("<", "&lt;")
                .replaceAll(">", "&gt;")
                .replaceAll('"', "&quot;")
                .replaceAll("'", "&#039;");
        }

        // Convertit une note sur 5 en étoiles HTML pleines et vides.
        function creerEtoiles(note) {
            const n = Math.max(0, Math.min(5, parseInt(note, 10) || 0));

            return '<span class="plein">★</span>'.repeat(n)
                 + '<span class="vide">☆</span>'.repeat(5 - n);
        }

        // Formate une date SQL en format français "jj/mm/aaaa à hh:mm".
        function formaterDateCommande(dateSQL) {
            const date = new Date(String(dateSQL).replace(" ", "T"));

            if (Number.isNaN(date.getTime())) {
                return echapperHTML(dateSQL);
            }

            return date.toLocaleDateString("fr-FR", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit"
            }).replace(",", " à");
        }

        // Crée et retourne un élément DOM .avis-card à partir des données d'une commande notée.
        function creerCarteAvis(id, cmd) {
            const carte = document.createElement("div");

            carte.className = "avis-card";
            carte.dataset.id = id;
            carte.dataset.date = Math.floor(new Date(String(cmd.date).replace(" ", "T")).getTime() / 1000) || 0;
            carte.dataset.livraison = cmd.note_livraison ?? 0;
            carte.dataset.produits = cmd.note_produits ?? 0;

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

            return carte;
        }

        // Interroge l'API toutes les 15 secondes et insère les nouvelles commandes notées dans la liste.
        async function verifierNouvellesCommandesNotees() {
            try {
                const response = await fetch("../api/get_new_commande.php", {
                    cache: "no-store"
                });

                if (!response.ok) {
                    console.error("Erreur API get_new_commande.php :", response.status);
                    return;
                }

                const commandes = await response.json();
                const liste = document.getElementById("avis-liste");

                if (!liste || !commandes) return;

                const idsDejaAffiches = new Set(
                    Array.from(liste.querySelectorAll(".avis-card"))
                        .map(carte => carte.dataset.id)
                        .filter(Boolean)
                );

                const nouvellesCommandes = Object.entries(commandes)
                    .filter(([id, cmd]) => cmd?.etat === "notee" && !idsDejaAffiches.has(id))
                    .sort(([, a], [, b]) => {
                        return new Date(String(b.date).replace(" ", "T"))
                             - new Date(String(a.date).replace(" ", "T"));
                    });

                if (nouvellesCommandes.length === 0) return;

                nouvellesCommandes.forEach(([id, cmd]) => {
                    liste.prepend(creerCarteAvis(id, cmd));
                });

                const message = document.getElementById("nouvelles-commandes-notees");

                if (message) {
                    const pluriel = nouvellesCommandes.length > 1 ? "s" : "";
                    message.textContent = `${nouvellesCommandes.length} nouvelle${pluriel} commande${pluriel} notée${pluriel} ajoutée${pluriel}.`;
                    message.style.display = "block";
                }

                const selectTri = document.getElementById("tri");
                trierAvis(selectTri?.value ?? "date");
            }
            catch (e) {
                console.error("Erreur lors de la vérification des nouvelles commandes notées :", e);
            }
        }

        // Récupère la liste des plats depuis l'API PHP.
        async function getListePlat(){
            try{
                let response = await fetch("../api/get_plat.php");
                response = await response.json();
                if (!response.success) {
                    console.error("Error : " + (response.erreur ?? ""));
                    return null;
                }
                return response.data;
            }
            catch(e){
                console.error("Error : " + e);
            }
        }

        // Construit et affiche le classement des plats les plus commandés selon la catégorie sélectionnée.
        async function creerMeilleursCmd(){
            let plats = await getListePlat();
            if (plats == null) return;
            plats = Object.values(plats);
            // Supprime la première ligne, qui correspond aux allergènes et non à un plat.
            plats.shift();

            function createConteneur(){
                const conteneur = document.createElement("li");
                conteneur.classList = "podium-liste";
                return conteneur;
            }

            let liste_categories = [];
            const categorie = ["entrees", "plats", "desserts", "vins", "cafes", "all"];

            categorie.forEach(cat => {
                const { plat, total } = MeilleurCommandes(plats, cat); 
                liste_categories.push({ categorie: cat, plat, total });
            });
            
            const donneesGlobales = liste_categories.find(item => item.categorie === "all");

            if (!donneesGlobales) return;

            const trie = document.querySelector("#filtre-cmd option:checked").value;
            const platsAAfficher = Object.values(liste_categories[categorie.indexOf(trie)]);
            const totalCommandes = donneesGlobales.total;
            const conteneur = document.getElementById("meilleurs-cmd");

            conteneur.innerHTML = "";

            platsAAfficher[1].forEach(p => {
                const c = createConteneur();

                const pourcentage = totalCommandes > 0
                    ? Math.round((p.nb_commandee / totalCommandes) * 100)
                    : 0;
                        
                c.innerHTML = `
                    <strong style="color: #f5f5f5; font-weight: 500;">${p.nom}</strong>
                    <span style="color: #666; font-size: 0.8rem;">${p.nb_commandee} commandes</span>
                    <b style="color: #c9a24d; font-weight: 600;">${pourcentage}%</b>
                `;

                conteneur.appendChild(c);
            });

            const c = createConteneur();
            c.innerHTML = `<h2 style="font-size: 1rem; color: #fff; margin: 0;">Total de plats commandés : ${totalCommandes}</h2>`;
            conteneur.appendChild(c);
        }

        // Bascule l'affichage entre l'onglet des avis et l'onglet des meilleures commandes.
        function changerAffichage(target){
            if (target === null || target === undefined) return;

            const btnActuel = document.querySelector("#conteneur-btn button.actif");
            btnActuel?.classList.remove("actif");

            const nouveauBouton = document.getElementById(target);
            nouveauBouton?.classList.add("actif");

            const conteneurAvis = document.getElementById("avis-liste");
            const conteneurCmd = document.getElementById("meilleurs-cmd");

            if (target === "btn-avis") {
                document.querySelectorAll(".filtres")[0].style.display = "block";
                document.getElementById("filtre-cmd").style.display = "none";

                conteneurAvis.style.display = "block";
                conteneurCmd.style.display = "none";    
            }
            else if (target === "btn-cmd") {
                document.querySelectorAll(".filtres")[0].style.display = "none";
                document.getElementById("filtre-cmd").style.display = "block";

                conteneurAvis.style.display = "none";
                conteneurCmd.style.display = "block";    
            }
        }
        
        // Génère une première fois la section des meilleures commandes au chargement de la page.
        creerMeilleursCmd();

        // Vérifie immédiatement puis toutes les 15 secondes les nouvelles commandes notées.
        verifierNouvellesCommandesNotees();
        setInterval(verifierNouvellesCommandesNotees, 15000);

        // Actualise automatiquement les meilleures commandes toutes les 100 secondes.
        setInterval(creerMeilleursCmd, 100000);
    </script>
</body>
</html>