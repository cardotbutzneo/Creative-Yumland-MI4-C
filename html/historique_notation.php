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
            <div class="avis-liste" id="avis-liste">
                <?php foreach ($commandes_notees as $id => $cmd){ ?>
                    <div class="avis-card"
                         data-date="<?= strtotime($cmd['date']) ?>"
                         data-livraison="<?= $cmd['note_livraison'] ?>"
                         data-produits="<?= $cmd['note_produits'] ?>">

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
            <div class="avis-card" id="meilleurs-cmd" style='display : none'>
                Chargement des meilleurs commandes...
            </div>

        <?php } ?>
    </main>

    <script>
        // Trie dynamiquement les cartes d'avis selon le critère choisi dans la liste déroulante.
        function trierAvis(critere) {
            // Récupère le conteneur principal qui contient tous les avis.
            const liste = document.getElementById("avis-liste");

            // Transforme la NodeList des cartes en tableau afin de pouvoir utiliser sort().
            const cartes = Array.from(liste.querySelectorAll(".avis-card"));

            // Compare deux cartes selon le critère sélectionné.
            cartes.sort((a, b) => {
                switch (critere) {
                    // Tri par date décroissante : l'avis le plus récent apparaît en premier.
                    case "date":
                        return b.dataset.date - a.dataset.date;

                    // Tri par note de livraison décroissante : meilleure note en premier.
                    case "note_livraison_desc":
                        return b.dataset.livraison - a.dataset.livraison;

                    // Tri par note de livraison croissante : moins bonne note en premier.
                    case "note_livraison_asc":
                        return a.dataset.livraison - b.dataset.livraison;

                    // Tri par note des produits décroissante : meilleure note en premier.
                    case "note_produits_desc":
                        return b.dataset.produits - a.dataset.produits;

                    // Tri par note des produits croissante : moins bonne note en premier.
                    case "note_produits_asc":
                        return a.dataset.produits - b.dataset.produits;
                }
            });

            // Réinsère les cartes dans le DOM dans le nouvel ordre calculé.
            cartes.forEach(c => liste.appendChild(c));
        }

        // Récupère la liste des plats depuis l'API PHP.
        async function getListePlat(){
            try{
                // Envoie une requête HTTP vers le fichier API qui retourne les plats.
                let response = await fetch("../api/get_plat.php");

                // Convertit la réponse reçue en objet JavaScript.
                response = await response.json();

                // Si l'API indique un échec, on affiche l'erreur dans la console et on arrête la fonction.
                if (!response.success) {
                    console.error("Error : " + (response.erreur ?? ""));
                    return null;
                }

                // Retourne uniquement les données utiles des plats.
                return response.data;
            }
            catch(e){
                // Capture les erreurs réseau ou les erreurs de conversion JSON.
                console.error("Error : " + e);
            }
        }

        // Crée et affiche la liste des plats les plus commandés selon la catégorie sélectionnée.
        async function creerMeilleursCmd(){
            // Récupère les plats depuis l'API.
            let plats = await getListePlat();

            // Si aucune donnée n'est récupérée, on arrête la fonction.
            if (plats == null) return;
            
            // Convertit l'objet des plats en tableau pour faciliter le traitement.
            plats = Object.values(plats);

            // Supprime la première ligne, qui correspond aux allergènes et non à un plat.
            plats.shift();

            // Crée un élément <li> qui servira à afficher une ligne du podium.
            function createConteneur(){
                const conteneur = document.createElement("li");
                conteneur.classList = "podium-liste";
                return conteneur;
            }

            // Tableau qui contiendra les meilleurs plats pour chaque catégorie.
            let liste_categories = [];
            
            // Liste des catégories prises en compte dans le classement.
            const categorie = ["entrees", "plats", "desserts", "vins", "cafes", "all"];

            // Calcule les meilleurs plats commandés pour chaque catégorie.
            categorie.forEach(cat => {
                // MeilleurCommandes() est supposée retourner un objet contenant le tableau des plats et le total.
                const { plat, total } = MeilleurCommandes(plats, cat); 
            
                // Stocke le résultat de la catégorie actuelle.
                liste_categories.push({ categorie: cat, plat, total });
            });
            
            // Récupère les données globales, toutes catégories confondues.
            const donneesGlobales = liste_categories.find(item => item.categorie === "all");

            // Si les données globales n'existent pas, on évite une erreur et on arrête la fonction.
            if (!donneesGlobales) return;

            // Récupère la catégorie actuellement sélectionnée dans le filtre.
            const trie = document.querySelector("#filtre-cmd option:checked").value;

            // Récupère les plats correspondant à la catégorie sélectionnée.
            // Object.values(...) transforme l'objet {categorie, plat, total} en tableau.
            const platsAAfficher = Object.values(liste_categories[categorie.indexOf(trie)]);

            // Total global de plats commandés, utilisé pour calculer les pourcentages.
            const totalCommandes = donneesGlobales.total;

            // Récupère le conteneur HTML où seront affichées les meilleures commandes.
            const conteneur = document.getElementById("meilleurs-cmd");

            // Vide le conteneur pour éviter de dupliquer les résultats à chaque actualisation.
            conteneur.innerHTML = "";

            // Parcourt les plats à afficher pour créer une ligne HTML par plat.
            platsAAfficher[1].forEach(p => {
                const c = createConteneur();

                // Calcule la part du plat dans le nombre total de commandes.
                const pourcentage = totalCommandes > 0 ? Math.round((p.nb_commandee / totalCommandes) * 100) : 0;
                        
                // Génère le contenu HTML de la ligne : nom, nombre de commandes et pourcentage.
                c.innerHTML = `
                    <strong style="color: #f5f5f5; font-weight: 500;">${p.nom}</strong>
                    <span style="color: #666; font-size: 0.8rem;">${p.nb_commandee} commandes</span>
                    <b style="color: #c9a24d; font-weight: 600;">${pourcentage}%</b>
                `;

                // Ajoute la ligne dans le conteneur des meilleures commandes.
                conteneur.appendChild(c);
            });

            // Ajoute une dernière ligne indiquant le total de plats commandés.
            const c = createConteneur();
            c.innerHTML = `<h2 style="font-size: 1rem; color: #fff; margin: 0;">Total de plats commandés : ${totalCommandes}</h2>`;
            conteneur.appendChild(c);
        }

        // Change l'affichage entre l'onglet des avis et l'onglet des meilleures commandes.
        function changerAffichage(target){
            // Sécurité : si aucun bouton cible n'est fourni, on arrête la fonction.
            if (target === null || target === undefined) return;

            // Récupère le bouton actuellement actif.
            const btnActuel = document.querySelector("#conteneur-btn button.actif");

            // Retire la classe actif de l'ancien bouton sélectionné.
            btnActuel?.classList.remove("actif");

            // Récupère le nouveau bouton sélectionné grâce à son id.
            const nouveauBouton = document.getElementById(target);

            // Ajoute la classe actif au nouveau bouton, s'il existe.
            nouveauBouton?.classList.add("actif");

            // Récupère les différents éléments à afficher ou masquer.
            const conteneurAvis = document.getElementById('avis-liste');
            const conteneurCmd = document.getElementById('meilleurs-cmd');
            const btnAvis = document.getElementById('btn-avis');
            const btnCmd = document.getElementById('btn-cmd');

            // Affichage de la section des avis.
            if (target === "btn-avis") {
                // Affiche le filtre des avis.
                document.querySelectorAll(".filtres")[0].style.display = 'block';

                // Masque le filtre des meilleures commandes.
                document.getElementById("filtre-cmd").style.display = "none";

                // Affiche les avis et masque les meilleures commandes.
                conteneurAvis.style.display = "block";
                conteneurCmd.style.display = "none";    
            }
            // Affichage de la section des meilleures commandes.
            else if (target === "btn-cmd") {
                // Masque le filtre des avis.
                document.querySelectorAll(".filtres")[0].style.display = 'none';

                // Affiche le filtre des catégories de commandes.
                document.getElementById("filtre-cmd").style.display = "block";

                // Masque les avis et affiche les meilleures commandes.
                conteneurAvis.style.display = "none";
                conteneurCmd.style.display = "block";    
            }
        }
        
        // Génère une première fois la section des meilleures commandes au chargement de la page.
        creerMeilleursCmd();

        // Actualise automatiquement les meilleures commandes toutes les 100 secondes.
        setInterval(creerMeilleursCmd,100000);
    </script>
</body>
</html>