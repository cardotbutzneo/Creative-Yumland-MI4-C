<?php
session_start();
require_once __DIR__."/../serveur.php";

if (!isset($_SESSION["connecte"]) || $_SESSION["role"] !== "Client") {
    header("Location: connexion.php");
    exit;
}

$email = $_SESSION["email"];
$id_cle = $_GET["id"] ?? '';
$commandes = $data_commandes;
$tous_les_plats = $data_plats;

if (!isset($commandes[$id_cle])) {
    header("Location: profil_client.php");
    exit;
}

$cmd = $commandes[$id_cle];

if ($cmd["etat"] !== "payee") {
    header("Location: profil_client.php?error=non_modifiable");
    exit;
}

if (!empty($cmd["deja_modifie"])) {
    header("Location: profil_client.php?error=deja_modifie");
    exit;
}

$total_brut_original = 0;
foreach ($cmd["plats"] as $p) {
    $prix_u = $tous_les_plats[$p["nom"]]["prix"] ?? 0;
    $total_brut_original += $prix_u * $p["quantite"];
}

$montant_paye = (float)$cmd["montant"];

if (isset($_POST['json_plats'])) {
    $nouveaux_plats = json_decode($_POST["json_plats"], true);

    if (empty($nouveaux_plats)) {
        unset($commandes[$id_cle]);
        file_put_contents("../data/commandes.json", json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header("Location: profil_client.php?info=deleted");
        exit;
    }

    $nouveau_total_brut = (float)$_POST["nouveau_total"];
    $diff = round($nouveau_total_brut - $total_brut_original, 2);
    $nouveau_montant = max(0, round($montant_paye + $diff, 2));

    if ($diff > 0) {
        $_SESSION["modif_commande"] = [
            "id_cle" => $id_cle,
            "reste_a_payer" => number_format($diff, 2, '.', ''),
            "total" => number_format($nouveau_montant, 2, '.', ''),
            "plats" => $nouveaux_plats,
        ];
        header("Location: paiement.php?action=modification");
    } else {
        $commandes[$id_cle]["plats"] = $nouveaux_plats;
        $commandes[$id_cle]["montant"] = number_format($nouveau_montant, 2, '.', '');
        $commandes[$id_cle]["deja_modifie"] = true;
        file_put_contents("../data/commandes.json", json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header("Location: profil_client.php?flag=success");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier ma commande — L'oro di Cicerone</title>
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/modifier_commande.css">
    <script>
        const TOTAL_BRUT_ORIGINAL = <?= $total_brut_original ?>;
        const MONTANT_PAYE = <?= $montant_paye ?>;
        window.addEventListener("DOMContentLoaded", function() {
            alert("Attention : la commande n'est modifiable qu'une seule fois.");
        });
    </script>
    <script src="../javascript/modifier_commande.js" defer></script>
</head>
<body>
    <header>
        <a href="index.php" style="text-decoration:none;"><h1>L'oro di Cicerone</h1></a>
    </header>
    <main>
        <div class="conteneur">
            <div class="en-tete">
                <div class="numero-commande">Commande <span>#<?= htmlspecialchars($cmd["numero"]) ?></span></div>
                <h2 class="titre">Modifier ma commande</h2>
                <p class="sous-titre">Ajoutez ou retirez des plats avant que la cuisine ne démarre.</p>
            </div>

            <div class="barre-ajout">
                <select id="select-plat" class="select-plat-style">
                    <option value="" disabled selected>Ajouter un délice…</option>
                    <?php foreach ($tous_les_plats as $nom => $p) {
                        if ($nom !== "Allergenes") { ?>
                        <option value="<?= htmlspecialchars($nom) ?>" data-prix="<?= $p['prix'] ?>">
                            <?= htmlspecialchars($nom) ?> — <?= $p['prix'] ?>€
                        </option>
                    <?php } } ?>
                </select>
                <button type="button" class="btn-ajouter" onclick="ajouterPlat()">
                    <span class="icone-ajout">+</span> Ajouter
                </button>
            </div>

            <div id="liste-commande" class="liste-plats">
                <?php foreach ($cmd["plats"] as $p_cmd) {
                    $pu = $tous_les_plats[$p_cmd['nom']]['prix'] ?? 0; ?>
                    <div class="plat-ligne"
                         data-nom="<?= htmlspecialchars($p_cmd['nom']) ?>"
                         data-prix="<?= $pu ?>">
                        <div class="plat-haut">
                            <span class="plat-nom"><?= htmlspecialchars($p_cmd['nom']) ?></span>
                            <span class="plat-sous-total"><?= number_format($pu * $p_cmd['quantite'], 2) ?>€</span>
                        </div>
                        <div class="plat-bas">
                            <div class="groupe-qte">
                                <div class="controles-qte">
                                    <button type="button" onclick="modifierQte(this, -1)">−</button>
                                    <span class="qte-nb"><?= $p_cmd['quantite'] ?></span>
                                    <button type="button" onclick="modifierQte(this, 1)">+</button>
                                </div>
                                <span class="hint-unite"><?= number_format($pu, 2) ?>€/unité</span>
                            </div>
                            <button type="button" class="btn-retirer" onclick="supprimerLigne(this)">Retirer</button>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="recapitulatif">
                <div class="recapitulatif-row recapitulatif-main">
                    <span>Total de la commande : </span>
                    <span id="display-total" class="mc-total-amount">0.00€</span>
                </div>
                <div class="recapitulatif-row recap-diff" id="diff-row" style="display:none;">
                    <span id="diff-label">Supplément à régler</span>
                    <span id="diff-amount" class="diff-montant">—</span>
                </div>
                <p id="info-perdant" class="info-perdant" style="display:none;">
                    La commande modifiée est moins chère. Aucun remboursement n'est effectué.
                </p>
            </div>

            <form id="form-final" method="POST">
                <input type="hidden" name="json_plats" id="input-json">
                <input type="hidden" name="nouveau_total" id="input-total">
                <div class="actions">
                    <a href="profil_client.php" class="btn-annuler">Annuler</a>
                    <button type="button" class="btn-valider" onclick="envoyerFormulaire()">
                        Valider les modifications
                    </button>
                </div>
            </form>

        </div>
    </main>
</body>
</html>