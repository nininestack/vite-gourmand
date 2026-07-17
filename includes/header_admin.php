<!-- DEBUT DU HTML EN FR -->

<!DOCTYPE html>
<html lang="fr">

<head>

    <!-- METAS, FONTS ET CSS -->

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/assets/css/style.css">
    <link rel="stylesheet" href="public/assets/css/admin.css">
    <link rel="stylesheet" href="public/assets/css/img.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADMINISTRATEUR</title>
</head>

<body>

    <header>

        <!-- NAVIGATION ET ICONES -->

        <div class="header-left">

            <nav>
                <a href="admin.php#statistiques">STATISTIQUES</a>
                <a href="admin.php#commandes">COMMANDES</a>
                <a href="admin.php#employes">EMPLOYÉS</a>
                <a href="admin.php#menus">MENUS</a>
                <a href="admin.php#stocks">STOCKS</a>
                <a href="admin.php#avis-clients">AVIS</a>
                <a href="admin.php#contacts">MESSAGES</a>
            </nav>

        </div>


        <div class="header-icons">

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