<?php 
/**
 * Fichier de maintenance
 * Il est volontairement isolé du reste pour éviter tout bug vennant interférer
 */
?>

<style>
    body{
        background-color: #0f0f0f;
    }
    main {
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        place-items: center;
        padding: 40px 20px;
    }

    .error-box {
        text-align: center;
        border: 1px solid rgba(197, 160, 33, 0.35);
        border-radius: 8px;
        padding: 60px 50px;
        max-width: 520px;
        background: #111;
        box-shadow: 0 0 40px rgba(197, 160, 33, 0.06);
    }

    .error-divider {
        width: 40px;
        height: 1px;
        background: rgba(197, 160, 33, 0.4);
        margin: 20px auto;
    }

    .error-title {
        font-size: 40px;
        font-weight: 400;
        color: #f5f5f5;
        margin-bottom: 14px;
        letter-spacing: 0.5px;
    }

    .body-error {
        font-size: 22px;
        color: rgba(245, 245, 245, 0.45);
        line-height: 1.8;
        letter-spacing: 0.3px;
        margin-bottom: 36px;
    }
    .lang-selector {
        display: flex;
        justify-content: right;
        padding-right: 50px;
        gap: 20px;
        margin-top: 30px;
    }

    .lang-selector a {
        display: inline-block;
        transition: transform 0.2s, opacity 0.2s;
        opacity: 0.5;
    }

    .lang-selector a:hover, 
    .lang-selector a.active {
        opacity: 1;
        transform: scale(1.1);
    }

    .lang-selector img {
        width: 35px;
        height: auto;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }

</style>

<?php 

$texte_global = [ // on stocke ici pour ne pas dépendre d'un serveur distant qui peut être inaccessible en cas de panne
    "fr" => [
        "title" => "Maintenance - L'oro di Cicerone",
        "subtitle" => "Maintenance en cours...",
        "message"=> "Nous effectuons actuellement une maintenance sur notre site. Nous nous excusons pour la gêne occasionnée. Nous vous invitons à revenir plus tard."
    ],
    "en" => [
        "title" => "Maintenance - L'oro di Cicerone",
        "subtitle" => "Currently performing maintenance",
        "message"=> "We are currently performing maintenance on our website. We apologize for the inconvenience and invite you to come back later."
    ]
];

$lang = "fr"; // par défaut

if (!empty($_GET["lang"])) {

    $lang = strtolower(substr($_GET["lang"], 0, 2));
} else if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    
    $lang = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
}

if ($lang !== "en" && $lang !== "fr") {
    $lang = "fr"; 
}

$text = $texte_global[$lang];
?>

<!DOCTYPE html>
<html lang=<?= $lang ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $text["title"] ?></title>
</head>
<body>
    <div class="lang-selector">
        <a href="?lang=fr" class="<?= $lang === 'fr' ? 'active' : '' ?>">
            <img src="https://purecatamphetamine.github.io/country-flag-icons/3x2/FR.svg" alt="Français">
        </a>
        <a href="?lang=en" class="<?= $lang === 'en' ? 'active' : '' ?>">
            <img src="https://purecatamphetamine.github.io/country-flag-icons/3x2/GB.svg" alt="English">
        </a>
    </div>
    <main>
        <section class="error-box">
            <p class="error-title"><?= $text["subtitle"] ?></p>
            <div class="error-divider"></div>
            <p class="body-error">
                <?= $text["message"] ?>
            </p>
        </section>
    </main>
</body>
</html>