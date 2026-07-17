<!-- DEBUT DU HTML EN FR -->

<!DOCTYPE html>
<html lang="fr">

<head>

    <!-- METAS, FONTS ET CSS -->

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/assets/css/style.css">
    <link rel="stylesheet" href="public/assets/css/contact.css">
    <link rel="stylesheet" href="public/assets/css/img.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CONTACT</title>
</head>

<body>

    <header>

        <!-- LOGO, NAVIGATION ET ICONES -->

        <div class="header-left">
            <div class="alogo1-image">
                <a href="accueil.php">
                    <img src="public/assets/img/alogo1.png" alt="LOGO">
                </a>
            </div>

            <nav>
                <a href="accueil.php">ACCUEIL</a>
                <a href="menus.php">MENUS</a>
                <a href="about.php">À PROPOS</a>
            </nav>

        </div>

        <div class="header-icons">

            <a href="user.php">
                <img src="public/assets/img/acompte.png" alt="MON COMPTE" class="icon">
            </a>

            <a href="commande.php">
                <img src="public/assets/img/apanier.png" alt="MA COMMANDE" class="icon">
            </a>

        </div>

    </header>

    <script>

        let lastScroll = 0;
        const header = document.querySelector("header");

        window.addEventListener("scroll", () => {

            let currentScroll = window.scrollY;

            if (currentScroll > lastScroll && currentScroll > 100) {

                header.classList.add("cache");
            } else {
                header.classList.remove("cache");
            }
            lastScroll = currentScroll;

        });

    </script>