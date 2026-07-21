<?php

// CONNEXION A LA BASE DE DONNEES

require_once 'config/database/database.php';

// RECUPERER LES MENUS

$stmt = $pdo->query("
SELECT 
menus.*,
themes.nom AS theme_nom,
diets.nom AS regime_nom

FROM menus

INNER JOIN themes 
ON menus.themes_id = themes.id

INNER JOIN diets 
ON menus.diets_id = diets.id

ORDER BY menus.id ASC
");

$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- HEADER-->

<?php require_once 'includes/header_menus.php'; ?>

<section class="s-d--small">

    <!-- TOUS LES MENUS-->

    <h1>NOS MENUS</h1>
    <h4>Des bons petits plats, du fait-maison, et surtout de quoi se régaler pour toutes les occasions</h4>
</section>

<!--FILTRER-->
<!-- PAR RECHERCHE
                     THEME 
                     REGIME 
                     NBR DE PERSONNES 
                     PRIX MIN 
                     PRIX MAX  -->

<details class="filtres-details">

    <summary>RECHERCHER UN MENU</summary>

    <section class="filtres">

        <input type="text" id="searchInput" placeholder=" Rechercher un menu... ">

        <select id="themeSelect">
            <option value=""> Tous les thèmes </option>
            <option value="classique">Classique</option>
            <option value="évènement">Évènement</option>
        </select>

        <select id="regimeSelect">
            <option value=""> Tous les régimes </option>
            <option value="Standard">Standard</option>
            <option value="Végétarien">Végétarien</option>
            <option value="Pesco-végétarien">Pesco-végétarien</option>
        </select>

        <select id="personnesSelect">
            <option value="">Nombre de personnes</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">+ de 5</option>
        </select>

        <input type="number" id="prixMin" placeholder=" Prix minimum ">

        <input type="number" id="prixMax" placeholder=" Prix maximum ">

        <!-- APPLIQUER OU REINITIALISER LES FILTRES-->

        <button class="btn" id="btnAppliquer"> APPLIQUER </button>
        <button class="btn" id="btnReset"> RÉINITIALISER </button>

    </section>
</details>


<!-- AFFICHER RESULTATS DU FILTRAGE -->

<section class=" resultats">
    <div class="resultats-items">

        <!-- COMPTEUR MENUS DISPO.-->
        <div class="small-text" id="nbMenus">
            <?= count($menus) ?> menus disponibles
        </div>

        <select id="triSelect">
            <option value="">Trier par</option>
            <option value="prix-croissant">Prix croissant</option>
            <option value="prix-decroissant">Prix décroissant</option>
            <option value="nom">Nom A-Z</option>
        </select>
    </div>
</section>


<!-- AFFICHER TOUS LES MENUS -->
<section class="menus">

    <?php foreach ($menus as $menu): ?>

        <!-- THEME REGIME PERS MIN ET PRIX DU MENU-->
        <div class="menu-image" data-theme="<?= strtolower($menu['theme_nom']) ?>"
            data-regime="<?= strtolower($menu['regime_nom']) ?>" data-personnes="<?= $menu['personnes_min'] ?>"
            data-prix="<?= $menu['prix'] ?>">


            <a href="menu.php?id=<?= $menu['id'] ?>">

                <picture>
                    <!-- IMG DU MENU-->
                    <img src="<?= $menu['image_principale'] ?>" alt="<?= $menu['nom'] ?>">
                </picture>

            </a>



            <!-- NOM DU MENU -->
            <h3> <?= $menu['nom'] ?> </h3>



            <!-- DETAIL DU MENU-->
            <a href="menu.php?id=<?= $menu['id'] ?>">VOIR LE DETAIL</a>


        </div>

    <?php endforeach; ?>

</section>

<div class="separation1"></div>

<!-- IMG BAS DE PAGE -->

<div class="accueil-image">
    <img src="public/assets/img/web/web_coppa.png" alt="COPPA">
</div>



<!-- JS -->

<script>

    const searchInput = document.getElementById("searchInput");
    const themeSelect = document.getElementById("themeSelect");
    const regimeSelect = document.getElementById("regimeSelect");
    const personnesSelect = document.getElementById("personnesSelect");

    const prixMin = document.getElementById("prixMin");
    const prixMax = document.getElementById("prixMax");

    const btnAppliquer = document.getElementById("btnAppliquer");
    const btnReset = document.getElementById("btnReset");
    const nbMenus = document.getElementById("nbMenus");

    const triSelect = document.getElementById("triSelect");

    btnAppliquer.addEventListener("click", filtrerMenus);
    searchInput.addEventListener("input", filtrerMenus);

    triSelect.addEventListener("change", trierMenus);

    btnReset.addEventListener("click", () => {
        searchInput.value = "";
        themeSelect.selectedIndex = 0;
        regimeSelect.selectedIndex = 0;
        personnesSelect.selectedIndex = 0;
        prixMin.value = "";
        prixMax.value = "";

        document.querySelectorAll(".menu-image").forEach(menu => {
            menu.style.display = "block";
        });

        nbMenus.textContent = <?= count($menus) ?> + " menus disponibles";
    });


    function filtrerMenus() {

        let compteur = 0;

        const recherche = searchInput.value.toLowerCase();
        const theme = themeSelect.value.toLowerCase();
        const regime = regimeSelect.value.toLowerCase();
        const personnes = personnesSelect.value;

        const min = prixMin.value;
        const max = prixMax.value;


        const menus = document.querySelectorAll(".menu-image");

        menus.forEach(menu => {

            const titre = menu.querySelector("h3").textContent.toLowerCase();

            const menuTheme = menu.dataset.theme.toLowerCase();

            const menuRegime = menu.dataset.regime.toLowerCase();

            const menuPersonnes = Number(menu.dataset.personnes);

            const menuPrix = Number(menu.dataset.prix);


            let visible = true;


            // RECHERCHE PAR NOM

            if (recherche && !titre.includes(recherche)) {
                visible = false;
            }


            // FILTRE THEME

            if (theme && menuTheme !== theme) {
                visible = false;
            }


            // FILTRE REGIME

            if (regime && menuRegime !== regime) {
                visible = false;
            }


            // FILTRE NOMBRE DE PERSONNES

            if (personnes) {

                const nbRecherche = Number(personnes);


                if (nbRecherche === 6) {

                    if (menuPersonnes < 5) {
                        visible = false;
                    }

                } else {

                    if (menuPersonnes > nbRecherche) {
                        visible = false;
                    }

                }

            }


            // PRIX MINIMUM

            if (min && menuPrix < Number(min)) {
                visible = false;
            }


            // PRIX MAXIMUM

            if (max && menuPrix > Number(max)) {
                visible = false;
            }



            // AFFICHAGE

            if (visible) {

                menu.style.display = "block";
                compteur++;

            } else {

                menu.style.display = "none";

            }


        });


        nbMenus.textContent = compteur + " menus disponibles";

    }

    function trierMenus() {

        const choix = triSelect.value;
        const container = document.querySelector(".menus");
        const menus = Array.from(
            container.querySelectorAll(".menu-image")
        );

        menus.sort((a, b) => {
            if (choix === "prix-croissant") {
                return Number(a.dataset.prix) - Number(b.dataset.prix);
            }

            if (choix === "prix-decroissant") {
                return Number(b.dataset.prix) - Number(a.dataset.prix);
            }

            if (choix === "nom") {
                const nomA = a.querySelector("h3").textContent;
                const nomB = b.querySelector("h3").textContent;

                return nomA.localeCompare(nomB);
            }

            return 0;
        });

        menus.forEach(menu => {
            container.appendChild(menu);

        });


    }


</script>

<!-- FOOTER-->

<?php require_once 'includes/footer.php'; ?>

</body>

</html>