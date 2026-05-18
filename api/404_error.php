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
        max-width: 480px;
        background: #111;
        box-shadow: 0 0 40px rgba(197, 160, 33, 0.06);
    }

    .error-code {
        font-size: 96px;
        font-weight: 400;
        color: #C5A021;
        line-height: 1;
        letter-spacing: -2px;
        margin-bottom: 8px;
    }

    #err-404 {
        position: fixed;
        margin: 0;
    }

    .error-divider {
        width: 40px;
        height: 1px;
        background: rgba(197, 160, 33, 0.4);
        margin: 20px auto;
    }

    .error-title {
        font-size: 22px;
        font-weight: 400;
        color: #f5f5f5;
        margin-bottom: 14px;
        letter-spacing: 0.5px;
    }

    .body-error {
        font-size: 13px;
        color: rgba(245, 245, 245, 0.45);
        line-height: 1.8;
        letter-spacing: 0.3px;
        margin-bottom: 36px;
    }

    .btn-retour {
        display: inline-block;
        padding: 12px 32px;
        border: 1px solid rgba(197, 160, 33, 0.5);
        background: transparent;
        color: #C5A021;
        font-size: 11px;
        letter-spacing: 2px;
        text-transform: uppercase;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-retour:hover {
        background: rgba(197, 160, 33, 0.08);
        border-color: #C5A021;
    }

</style>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 not found</title>
</head>
<body>
    <main>
        <section class="error-box">
            <p class="error-code">404</p>
            <div class="error-divider"></div>
            <p class="error-title">Page introuvable</p>
            <p class="body-error">
                Désolé, la page demandée n'a pas pu être trouvée.<br>
                Elle a peut-être été déplacée ou supprimée.
            </p>
            <button class="btn-retour" onclick="window.location='../html/index.php'">
                Revenir à l'accueil
            </button>
        </section>
    </main>
</body>
</html>

<script>
    // On initialise la position de départ et la vitesse
    let x = 100;
    let y = 100;
    let vitesse = 2; // Nombre de pixels déplacés par image
    let teta = Math.PI / 4; // Angle en radians (45 degrés au départ)
    const colors = ["purple", "red", "orange", "green", "blue", "gold", "cyan", "magenta"];

    function bouger404() {
        
        const element = document.querySelector("#err-404");
        if (!element) return;

        // 1. On récupère la taille de la boîte de l'élément et de la FENÊTRE (mieux que l'écran global)
        const rect = element.getBoundingClientRect();
        const largeurFenetre = window.innerWidth;
        const hauteurFenetre = window.innerHeight;

        // 2. On calcule le déplacement basé sur le vecteur de l'angle (Trigonométrie)
        let dx = Math.cos(teta) * vitesse;
        let dy = Math.sin(teta) * vitesse;

        // Nouvelle position théorique
        x += dx;
        y += dy;

        // 3. DÉTECTION DES BORDS & RECALCUL DE L'ANGLE (Rebond)
        let collision = false;

        // Bord gauche ou bord droit
        if (x <= 0 || x + rect.width >= largeurFenetre) {
            teta = Math.PI - teta; // Inversion physique de l'angle horizontal
            collision = true;
        }
        // Bord haut ou bord bas
        if (y <= 0 || y + rect.height >= hauteurFenetre) {
            teta = -teta; // Inversion physique de l'angle vertical
            collision = true;
        }

        // Si on a touché un bord, on applique ton idée : on ajoute un petit décalage aléatoire
        if (collision) {
            // Un angle aléatoire entre -5 et +5 degrés (converti en radians)
            const decalageAleatoire = (Math.random() * 10 - 5) * (Math.PI / 180);
            teta += decalageAleatoire;
            element.style.color = colors[Math.floor(Math.random() * colors.length)]; // on change de couleur dès qu'on touche l'écran
        }

        // 4. On applique la nouvelle position en CSS
        element.style.position = "fixed";
        element.style.left = x + "px";
        element.style.top = y + "px";

        // 5. On demande au navigateur de rappeler cette fonction à la prochaine image (60fps)
        requestAnimationFrame(bouger404);
    }

    function lancementAnimation(){
        document.getElementsByClassName("error-code")[0].id = "err-404";
        requestAnimationFrame(bouger404);
    }
    // On lance l'animation une première fois au chargement de la page
    setTimeout(() => {
        lancementAnimation();
        requestAnimationFrame(bouger404);
    }, 20000);
</script>