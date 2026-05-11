<?php
session_start();
require_once __DIR__."/../serveur.php";

if (!isset($_SESSION["connecte"]) || $_SESSION["role"] !== "Client") {
    header("Location: connexion.php");
    exit;
}

$email = $_SESSION["email"];
$id_cle = $_GET["id"] ?? '';
$commandes = lire_data("../data/commandes.json");
$ts_plats = lire_data("../data/plats.json");

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

if (isset($_POST['json_plats'])) {
    $nv_plats = json_decode($_POST["json_plats"], true);
    if (empty($nv_plats)) {
        unset($commandes[$id_cle]);
        file_put_contents("../data/commandes.json", json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header("Location: profil_client.php?info=deleted");
        exit;
    }

    $nv_tot = (int)$_POST["nouveau_total"];
    $ancien_tot = (int)$cmd["montant"];
    $diff = $nv_tot - $ancien_tot;

    if ($diff > 0) {
        $_SESSION["modif_commande"] = [
            "id_cle" => $id_cle,
            "diff" => $diff,
            "reste_a_payer" => $diff,
            "total" => $nv_tot,
            "plats" => $nv_plats,
        ];
        header("Location: paiement.php?action=modification");
    } else {
        $commandes[$id_cle]["plats"] = $nv_plats;
        $commandes[$id_cle]["montant"] = $nv_tot;
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
        const total1 = <?= (int)$cmd["montant"] ?>;

        window.addEventListener("DOMContentLoaded", () => {
            alert("Une commande n'est modifiable qu'une seule fois.");
            calculerTout();
        });
    </script>
    <script src="../javascript/modifier_commande.js" defer></script>
</head>
<body>
<header>
    <a href="index.php" style="text-decoration:none;">
        <h1>L'oro di Cicerone</h1>
    </a>
</header>
<main>
    <div class="mc-wrapper">
        <div class="mc-header">
            <div class="mc-numero">
                Commande <span>#<?= htmlspecialchars($cmd["numero"]) ?></span>
            </div>
            <h2 class="mc-title">Modifier ma commande</h2>
            <p class="mc-subtitle">
                Ajoutez ou retirez des plats avant que la cuisine ne démarre.
            </p>
        </div>
        <div class="mc-add-bar">
            <select id="select-plat" class="mc-select">
                <option value="" disabled selected>
                    Ajouter un délice…
                </option>
                <?php
                foreach ($ts_plats as $nom => $x) {
                    if ($nom !== "Allergenes") {
                ?>
                    <option value="<?= htmlspecialchars($nom) ?>"data-prix="<?= $x['prix'] ?>">
                        <?= htmlspecialchars($nom) ?> — <?= $x['prix'] ?>€
                    </option>
                <?php
                    }
                }
                ?>
            </select>
            <button type="button" class="mc-btn-add" onclick="ajouterPlat()">
                + Ajouter
            </button>
        </div>
        <div id="liste-commande" class="mc-list">
            <?php
            foreach ($cmd["plats"] as $p_cmd) {
                $pu = $ts_plats[$p_cmd['nom']]['prix'] ?? 0;
            ?>
                <div class="mc-item" data-nom="<?= htmlspecialchars($p_cmd['nom']) ?>" data-prix="<?= $pu ?>">
                    <div class="mc-item-top">
                        <span class="mc-item-name">
                            <?= htmlspecialchars($p_cmd['nom']) ?>
                        </span>
                        <span class="mc-item-subtotal">
                            <?= $pu * $p_cmd['quantite'] ?>€
                        </span>
                    </div>
                    <div class="mc-item-bottom">
                        <div class="mc-qte-group">
                            <div class="mc-qte-controls">
                                <button type="button" onclick="modifierQte(this, -1)">
                                    -
                                </button>
                                <span class="qte-nb">
                                    <?= $p_cmd['quantite'] ?>
                                </span>
                                <button type="button" onclick="modifierQte(this, 1)">
                                    +
                                </button>
                            </div>
                            <span class="mc-unit-hint">
                                <?= $pu ?>€ / unité
                            </span>
                        </div>
                        <button type="button" class="mc-btn-remove" onclick="supprimerLigne(this)">
                            Retirer
                        </button>
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class="mc-summary">
            <div class="mc-summary-row mc-summary-main">
                <span>Total de la commande</span>
                <span id="display-total" class="mc-total-amount">
                    0€
                </span>
            </div>
            <div class="mc-summary-row mc-diff-row" id="diff-row" style="display:none;">
                <span id="diff-label">
                    Supplément à régler
                </span>
                <span id="diff-amount" class="mc-diff-amount">
                    —
                </span>
            </div>
            <p id="mc-info-perdant" class="mc-info-perdant" style="display:none;">
                Aucun remboursement n’est effectué si le montant de la commande après modification est inférieur au montant initial.
            </p>
        </div>
        <form id="form-final" method="POST">
            <input type="hidden" name="json_plats" id="input-json">
            <input type="hidden" name="nouveau_total" id="input-total">
            <div class="mc-actions">
                <a href="profil_client.php" class="mc-btn-cancel">
                    Annuler
                </a>
                <button type="button" class="mc-btn-confirm" onclick="envoyerFormulaire()">
                    Valider les modifications
                </button>
            </div>
        </form>
    </div>
</main>
</body>
</html>