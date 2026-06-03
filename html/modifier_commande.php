<?php
session_start();
require_once __DIR__."/../serveur.php";

$data_langue = lire_data("../data/langue.json");

$langue = $_COOKIE["langue"] ?? "fr";

if ($langue !== "fr" && $langue !== "en") {
    $langue = "fr";
}

$isFrench = ($langue === "fr");
$text = $data_langue[$langue] ?? $data_langue["fr"];

if (!isset($_SESSION["connecte"]) || $_SESSION["role"] !== "Client") {
    header("Location: connexion.php");
    exit;
}

$email = $_SESSION["email"];
$id_cle = $_GET["id"] ?? '';
$commandes = lire_data("../data/commandes.json");
$tous_les_plats = lire_data("../data/plats.json");

if (!isset($commandes[$id_cle])) { // Vérifie que la commande existe
    header("Location: profil_client.php");
    exit;
}

$cmd = $commandes[$id_cle];

if ($cmd["etat"] !== "payee") { // Seules les commandes payées sont modifiables
    header("Location: profil_client.php?error=non_modifiable");
    exit;
}

if (!empty($cmd["deja_modifie"])) { // Empêche les modifications multiples
    header("Location: profil_client.php?error=deja_modifie");
    exit;
}

$total_brut_original = 0; // Calcule le total brut de la commande originale 
foreach ($cmd["plats"] as $p) {
    $prix_u = $tous_les_plats[$p["nom"]]["prix"] ?? 0;
    $total_brut_original += $prix_u * $p["quantite"];
}

$montant_paye = (float)$cmd["montant"]; // Montant déjà payé par le client

if (isset($_POST['json_plats'])) {
    $nouveaux_plats = json_decode($_POST["json_plats"], true); 

    if (empty($nouveaux_plats)) { // Si la commande est vide après modification, on la supprime
        unset($commandes[$id_cle]);
        file_put_contents("../data/commandes.json", json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header("Location: profil_client.php?info=deleted");
        exit;
    }

    $nouveau_total_brut = (float)$_POST["nouveau_total"]; // Calcule le nouveau total de la commande modifiée
    $diff = round($nouveau_total_brut - $total_brut_original, 2); // Différence entre le nouveau total et l'ancien total
    $nouveau_montant = max(0, round($montant_paye + $diff, 2)); // Nouveau montant total à enregistrer

    if ($diff > 0) { // Si la commande modifiée est plus chère, on redirige vers la page de paiement pour régler le supplément
        $_SESSION["modif_commande"] = [ // Sauvegarde temporaire des modifications dans la session
            "id_cle" => $id_cle,
            "reste_a_payer" => number_format($diff, 2, '.', ''),
            "total" => number_format($nouveau_montant, 2, '.', ''),
            "plats" => $nouveaux_plats,
        ];
        header("Location: paiement.php?action=modification");
    } else { // Si la commande modifiée est moins chère ou du même prix, on enregistre directement les modifications sans passer par le paiement
        $commandes[$id_cle]["plats"] = $nouveaux_plats; 
        $commandes[$id_cle]["montant"] = number_format($nouveau_montant, 2, '.', '');
        $commandes[$id_cle]["deja_modifie"] = true; // Marque la commande comme déjà modifiée
        file_put_contents("../data/commandes.json", json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); // Sauvegarde des modifications
        header("Location: profil_client.php?flag=success");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php if ($isFrench) echo "fr"; else echo "en"; ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $text["modifier_commande"]["title"] ?></title>
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/modifier_commande.css">
    <script>
        const TOTAL_BRUT_ORIGINAL = <?= $total_brut_original ?>;
        const MONTANT_PAYE = <?= $montant_paye ?>;
        window.addEventListener("DOMContentLoaded", function() { // Avertissement affiché au chargement de la page
            alert("<?= addslashes($text["modifier_commande"]["alert_once"]) ?>");
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
                <div class="numero-commande"><?= $text["modifier_commande"]["order"] ?> <span>#<?= htmlspecialchars($cmd["numero"]) ?></span></div>
                <h2 class="titre"><?= $text["modifier_commande"]["page_title"] ?></h2>
                <p class="sous-titre"><?= $text["modifier_commande"]["subtitle"] ?></p>
            </div>

            <div class="barre-ajout">
                <select id="select-plat" class="select-plat-style">
                    <option value="" disabled selected><?= $text["modifier_commande"]["select_placeholder"] ?></option>
                    <?php foreach ($tous_les_plats as $nom => $p) {
                        if ($nom !== "Allergenes") { ?>
                        <option value="<?= htmlspecialchars($nom) ?>" data-prix="<?= $p['prix'] ?>">
                            <?= htmlspecialchars($nom) ?> - <?= $p['prix'] ?>€
                        </option>
                    <?php } } ?>
                </select>
                <button type="button" class="btn-ajouter" onclick="ajouterPlat()">
                    <span class="icone-ajout">+</span> <?= $text["modifier_commande"]["add_button"] ?>
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
                                    <button type="button" onclick="modifierQte(this, -1)">-</button>
                                    <span class="qte-nb"><?= $p_cmd['quantite'] ?></span>
                                    <button type="button" onclick="modifierQte(this, 1)">+</button>
                                </div>
                                <span class="hint-unite"><?= number_format($pu, 2) ?>€/<?= $text["modifier_commande"]["unit"] ?></span>
                            </div>
                            <button type="button" class="btn-retirer" onclick="supprimerLigne(this)"><?= $text["modifier_commande"]["remove_button"] ?></button>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="recapitulatif">
                <div class="recapitulatif-row recapitulatif-main">
                    <span><?= $text["modifier_commande"]["order_total"] ?> </span>
                    <span id="display-total" class="mc-total-amount">0.00€</span>
                </div>
                <div class="recapitulatif-row recap-diff" id="diff-row" style="display:none;">
                    <span id="diff-label"><?= $text["modifier_commande"]["extra_to_pay"] ?></span>
                    <span id="diff-amount" class="diff-montant">—</span>
                </div>
                <p id="info-perdant" class="info-perdant" style="display:none;">
                    <?= $text["modifier_commande"]["no_refund"] ?>
                </p>
            </div>

            <form id="form-final" method="POST">
                <input type="hidden" name="json_plats" id="input-json">
                <input type="hidden" name="nouveau_total" id="input-total">
                <div class="actions">
                    <a href="profil_client.php" class="btn-annuler"><?= $text["modifier_commande"]["cancel"] ?></a>
                    <button type="button" class="btn-valider" onclick="envoyerFormulaire()">
                        <?= $text["modifier_commande"]["validate"] ?>
                    </button>
                </div>
            </form>

        </div>
    </main>
</body>
</html>