<?php
session_start();

require_once __DIR__ . "/../serveur.php";

if (!isset($_SESSION["connecte"]) || $_SESSION["connecte"] !== true) {
    header("Location: connexion.php?error=unauthorized");
    exit;
}

verifier_connexion($_SESSION["role"], "Cuisinier");

$bdd_cmd = lire_data("../data/commandes.json");

// Filtrer uniquement les commandes notées
$commandes_notees = array_filter($bdd_cmd, fn($cmd) => $cmd["etat"] === "notee");

// Calculer les moyennes globales
$total_livraison = 0;
$total_produits = 0;
$nb = count($commandes_notees);

foreach ($commandes_notees as $cmd) {
    $total_livraison += (int)$cmd["note_livraison"];
    $total_produits  += (int)$cmd["note_produits"];
}

$moy_livraison = $nb > 0 ? round($total_livraison / $nb, 1) : null;
$moy_produits  = $nb > 0 ? round($total_produits  / $nb, 1) : null;

// Trier par date décroissante
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
                         data-livraison="<?= (int)$cmd['note_livraison'] ?>"
                         data-produits="<?= (int)$cmd['note_produits'] ?>">

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
                                    $n = (int)$cmd["note_livraison"];
                                    echo str_repeat('<span class="plein">★</span>', $n);
                                    echo str_repeat('<span class="vide">☆</span>', 5 - $n);
                                    ?>
                                </span>
                            </div>
                            <div class="note-ligne">
                                <span class="note-intitule">Produits</span>
                                <span class="note-etoiles">
                                    <?php
                                    $n = (int)$cmd["note_produits"];
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

        <?php } ?>
    </main>

    <script>
        function trierAvis(critere) {
            const liste = document.getElementById("avis-liste");
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
                }
            });

            cartes.forEach(c => liste.appendChild(c));
        }
    </script>
</body>
</html>