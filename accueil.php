<?php

// SESSION ET SECURITE

session_start();

require_once 'database/database.php';

// RECUPERER LES MENUS POP. DANS LES COMMANDES

$stmt = $pdo->prepare("

SELECT 
    menus.*,
    COUNT(oders_items.menus_id) AS nombre_commandes

FROM menus

INNER JOIN oders_items
ON menus.id = oders_items.menus_id

GROUP BY menus.id

ORDER BY nombre_commandes DESC

LIMIT 3

");

$stmt->execute();

$menus_populaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

// RECUPERER LES AVIS VALIDES

$stmt = $pdo->prepare("
SELECT
    r.note,
    r.commentaire,
    r.date_creation,
    u.nom,
    u.prenom
FROM reviews r
JOIN users u
ON r.users_id = u.id
WHERE r.statut = 'valide'
ORDER BY r.date_creation DESC
");

$stmt->execute();

$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- HEADER-->

<?php require_once 'includes/header_accueil.php'; ?>


<!-- ACCUEIL -->

<div class="accueil-image">
    <img src="public/assets/img/web/web_tomate.png" alt="TOMATE">
</div>

<section class="s-d">

    <!-- BIENVENUE-->

    <h2>BIENVENUE CHEZ VITE & GOURMAND</h2>
    <h2>VOTRE TRAITEUR EVENEMENTIEL A BORDEAUX DEPUIS 25 ANS</h2>


    <div class="s-d-buttons">
        <a href="menus.php" class="btn btn-red">NOS MENUS</a>
        <a href="contact.php" class="btn btn-red">DEMANDER UN DEVIS</a>
    </div>

</section>

<div class="fourchette1-image">
    <img src="public/assets/img/web/web_fourchette1.png" alt="FOURCHETTE">
</div>

<!-- MENUS POP.-->

<section class="s-l">

    <h1 class="text_dark_r">MENUS POPULAIRES</h1>

    <div class="menu-populaire">

        <?php foreach ($menus_populaires as $menu): ?>

            <div class="menu-image">

                <a href="menu.php?id=<?= $menu['id'] ?>">

                    <img src="<?= htmlspecialchars($menu['image_principale']) ?>"
                        alt="<?= htmlspecialchars($menu['nom']) ?>">

                </a>

                <h3>
                    <?= htmlspecialchars($menu['nom']) ?>
                </h3>

                <a href="menu_detail.php?id=<?= $menu['id'] ?>">
                    VOIR LE DETAIL
                </a>


            </div>


        <?php endforeach; ?>


    </div>

</section>

<section class="s-d-big">

    <!-- NOTRE HISTOIRE -->


    <div class="histoire">

        <h1>NOTRE HISTOIRE</h1>
        <p>Chez Vite & Gourmand, on cuisine avec le cœur depuis plus de 25 ans. </p>
        <p>Entreprise familiale fondée à Bordeaux par Julie et José,</p>
        <p>on accompagne les petits comme les grands moments de vie : </p>
        <p>anniversaires, repas d’équipe, brunchs, fêtes de fin d’année, réceptions privées…</p>
        <p>et toutes les bonnes excuses pour bien manger.</p>
        <div class="separation"></div>
        <p>Notre idée ? Proposer une cuisine généreuse, savoureuse et sans prise de tête,</p>
        <p>avec des menus qui évoluent au fil des saisons et des envies.</P>

        <a href="about.php" class="btn btn-red">EN SAVOIR PLUS</a>

    </div>


</section>


<div class="accueil-image">
    <img src="public/assets/img/web/web_pates.png" alt="PATES">
</div>


<!-- BANIERE DEFILE -->

<div class="banniere1">
    <div class="texte_defile">
        <span>
            QUALITÉ &nbsp;&nbsp;&nbsp; GOURMANDISE &nbsp;&nbsp;&nbsp; GÉNÉROSITÉ &nbsp;&nbsp;&nbsp; FRAÎCHEUR
            &nbsp;&nbsp;
        </span>
        <span>
            QUALITÉ &nbsp;&nbsp;&nbsp; GOURMANDISE &nbsp;&nbsp;&nbsp; GÉNÉROSITÉ &nbsp;&nbsp;&nbsp; FRAÎCHEUR
            &nbsp;&nbsp;
        </span>
        <span>
            QUALITÉ &nbsp;&nbsp;&nbsp; GOURMANDISE &nbsp;&nbsp;&nbsp; GÉNÉROSITÉ &nbsp;&nbsp;&nbsp; FRAÎCHEUR
            &nbsp;&nbsp;
        </span>
    </div>
</div>


<div class="accueil-image">
    <img src="public/assets/img/web/web_sauce.png" alt="SAUCE">
</div>


<section class="s-r">

    <!-- POURQUOI -->

    <div class="pourquoinous">

        <div class="ecrevisse-image">
            <img src="public/assets/img/web/web_ecrevisse.png" alt="ECREVISSE">
        </div>

        <div class="pourquoi">

            <h1>POURQUOI NOUS CHOISIR?</h1>

            <div class="separation"></div>

            <p>→ Fraîcheur assurée </p>
            <p>→ Produits locaux & de qualité</p>
            <p>→ Adapté à tous les régimes</p>
            <p>→ Livraison soignée</p>
            <p>→ Service & prestation impeccables</p>

            <div class="separation"></div>

            <a href="menus.php" class="btn btn-dark">NOS MENUS</a>

        </div>

    </div>

</section>



<div class="fourchette2-image">
    <img src="public/assets/img/web/web_fourchette2.png" alt="FOURCHETTE2">
</div>


<section class="s-l">

    <!-- FONCTIONNEMENT -->

    <section class="fonctionne">

        <h1>COMMENT ÇA FONCTIONNE?</h1>

        <div class="separation"></div>

        <div class="etapes">
            <div class="etape">

                <div class="cercle"></div>
                <p>Choisis ton menu</p>
                <div class="small-texte">Notre catalogue <a href="menus.php">ici</a></div>

            </div>
            <div class="etape">

                <div class="cercle"></div>
                <p>Demande un devis</p>
                <div class="small-texte">Renseigne les détails tel que la date, lieu, nombre de convives...</div>

            </div>
            <div class="etape">

                <div class="cercle"></div>
                <p>Confirme ta commande</p>
                <div class="small-texte">Une fois ton devis obtenu, il ne te reste plus qu’à confirmer ta commande.
                    (n’oublie pas de te connecter à ton compte V&G)</div>
                <div class="small-texte">si tu n’en as pas encore, clique <a href="inscription.php">ici</a></div>

            </div>
            <div class="etape">

                <div class="cercle"></div>
                <p>On s'occupe du reste</p>
                <div class="small-texte">Nous nous occupons de la préparation, la livraison & la mise en place.</div>
                <div class="small-texte">Plus qu’à profiter & déguster! </div>

            </div>
        </div>

    </section>

</section>


<div class="fourchette1-image">
    <img src="public/assets/img/web/web_fourchette3.png" alt="FOURCHETTE3">
</div>


<section class="s-d-small">

    <h1>PAROLES DE GOURMANDS</h1>

    <div class="reviews-container">

        <?php foreach ($reviews as $review): ?>

            <div class="review">

                <h3><?= htmlspecialchars($review['nom']) ?>
                            <?= htmlspecialchars($review['prenom']) ?>
                </h3>
                <p><?= htmlspecialchars($review['note']) ?>/5 ★</p>
                <p><?= htmlspecialchars($review['commentaire']) ?></p>

            </div>

        <?php endforeach; ?>

    </div>

</section>


<!-- IMAGES BAS DE PAGE -->

<div class="accueilbas-containeur">
    <div class="accueilbas-image">
        <img src="public/assets/img/web/web_accueil_bas.png" alt="ACCUEIL">
    </div>

    <div class="accueilbas-image">
        <img src="public/assets/img/web/web_accueil_bas1.png" alt="ACCUEIL">
    </div>

    <div class="accueilbas-image">
        <img src="public/assets/img/web/web_accueil_bas2.png" alt="ACCUEIL">
    </div>
</div>

<!-- FOOTER-->

<?php require_once 'includes/footer.php'; ?>

</body>

</html>