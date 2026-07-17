<!-- DEBUT DU HTML EN FR -->

<!DOCTYPE html>
<html lang="fr">

<head>

    <!-- METAS, FONTS ET CSS -->

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/assets/css/style.css">
    <link rel="stylesheet" href="public/assets/css/user.css">
    <link rel="stylesheet" href="public/assets/css/img.css">
    <link rel="stylesheet" href="public/assets/css/commande.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CLIENT</title>
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
                <a href="menus.php">MENUS</a>
                <a href="about.php">À PROPOS</a>
                <a href="contact.php">CONTACT</a>
            </nav>

        </div>

        <div class="header-icons">

            <a href="commande.php">
                <img src="public/assets/img/apanier.png" alt="MA COMMANDE" class="icon">
            </a>

            <a href="deconnexion.php">
                <img src="public/assets/img/adeco.png" alt="MON COMPTE" class="icon">
            </a>

        </div>

    </header>

    <script>

        // SCRIPT POUR CACHER LE HEADER LORS DU SCROLL

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