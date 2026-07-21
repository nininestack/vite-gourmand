<?php

// CONNECTION A LA BASE DE DONNEES

session_start();
require_once 'config/database/database.php';

// AJOUT AU PANIER

if (isset($_GET['add'])) {
    $menu_id = intval($_GET['add']);

    // VERIFICATION DISPONIBILITE MENU

    $stmt = $pdo->prepare("

        SELECT 
            menus.id,
            stocks.stock,
            stocks.disponible

        FROM menus

        INNER JOIN stocks
        ON menus.id = stocks.menus_id

        WHERE menus.id = ?

    ");

    $stmt->execute([$menu_id]);
    $disponibilite = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        !$disponibilite ||
        $disponibilite['disponible'] != 1 ||
        $disponibilite['stock'] <= 0
    ) {

        header("Location: menus.php?erreur=indisponible");
        exit();
    }

    // CREATION DU PANIER 

    if (!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = [];
    }

    // VERIFIER SI LE MENU EXISTE DEJA DANS LE PANIER

    $existe = false;

    foreach ($_SESSION['panier'] as &$item) {
        if ($item['menu_id'] == $menu_id) {

            $item['quantite']++;
            $existe = true;
            break;
        }
    }

    // SI NOUVEAU MENU
    if (!$existe) {

        $_SESSION['panier'][] = [
            "menu_id" => $menu_id,
            "quantite" => 1
        ];
    }

    // REDIRECTION VERS LE PANIER

    header("Location: commande.php");
    exit();
}

// RECUPERATION DE L'ID DU MENU DANS L'URL

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

} else {

    // SI X ID FOURNI RETOUR VERS LA PAGE MENUS

    header("Location: menus.php");
    exit;
}

// RECUPERATION DU MENU

$stmt = $pdo->prepare("
SELECT
menus.*,
themes.nom AS theme_nom,
diets.nom AS regime_nom,
stocks.stock,
stocks.disponible

FROM menus

INNER JOIN themes
ON menus.themes_id = themes.id

INNER JOIN diets
ON menus.diets_id = diets.id

LEFT JOIN stocks
ON menus.id = stocks.menus_id

WHERE menus.id = ?

");

$stmt->execute([$id]);

$menu = $stmt->fetch(PDO::FETCH_ASSOC);

// SI X MENU EXISTE

if (!$menu) {
    echo "Menu introuvable";
    exit;
}

// RECUPERATION DES PLATS DU MENU

$stmt = $pdo->prepare("
SELECT *
FROM menu_items
WHERE menus_id = ?
ORDER BY ordre ASC
");

$stmt->execute([$id]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// RECUPERATION DES ALLERGENES DU MENU

$stmt = $pdo->prepare("
SELECT allergens.nom
FROM allergens
INNER JOIN menus_allergens
ON allergens.id = menus_allergens.allergens_id
WHERE menus_allergens.menus_id = ?
ORDER BY allergens.nom ASC
");

$stmt->execute([$id]);

$allergens = $stmt->fetchAll(PDO::FETCH_COLUMN);

// RECUPERER LES AVIS VALIDES DU MENU SELECTIONNE

$stmt = $pdo->prepare("
SELECT
reviews.note,
reviews.commentaire,
reviews.date_creation,
users.nom,
users.prenom

FROM reviews

INNER JOIN users
ON reviews.users_id = users.id

INNER JOIN orders
ON reviews.orders_id = orders.id

INNER JOIN oders_items
ON orders.id = oders_items.orders_id

WHERE oders_items.menus_id = ?
AND reviews.statut = 'valide'

ORDER BY reviews.date_creation DESC
");

$stmt->execute([$id]);

$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- HEADER-->

<?php require_once 'includes/header_menu.php'; ?>


<section class="s-d--small">

    <!-- MENU SELECTIONNE-->

    <h1><?= htmlspecialchars($menu['nom']) ?></h1>

    <!-- DESCRIPTION DU MENU SELECTIONNE-->

    <p><?= htmlspecialchars($menu['description']) ?></p>

</section>

<section class="section-column">

    <!-- RETOUR A TOUS LES MENUS-->


    <a href="menus.php">Retour aux menus</a>


</section>

<section class="s-l-extra-big">

    <!-- DETAIL DU MENU-->

    <section class="menu">

        <div class="menu-labels">

            <span>ENTREE</span>
            <span>PLAT</span>
            <span>DESSERT</span>

        </div>

        <!-- IMG DU MENU-->
        <!-- PASSE EN CAROUSEL EN CSS POUR MEDIA-->

        <div class="menu-images">

            <?php foreach ($items as $item): ?>

                <div class="menus-image">
                    <picture>
                        <img src="<?= $item['image'] ?>" alt="<?= $item['nom'] ?>">
                    </picture>
                </div>

            <?php endforeach; ?>

        </div>


        <section class="menu1">

            <!-- ENTREE PLAT DESSERT DU MENU-->

            <?php foreach ($items as $item): ?>

                <div class="plat">

                    <h3>
                        <?= mb_strtoupper($item['type'], 'UTF-8') ?>
                    </h3>

                    <h3>
                        <?= htmlspecialchars($item['nom']) ?>
                    </h3>

                    <p>
                        <?= htmlspecialchars($item['description']) ?>
                    </p>

                </div>

            <?php endforeach; ?>


            <div class="separation"></div>

            <!-- PRIX DU MENU -->
            <p class="prix-menu">
                <strong><?= $menu['prix'] ?> € / personne</strong>
            </p>

            <!-- PERS. MIN. -->
            <div class="small-text">
                <strong>Minimum :</strong> <?= $menu['personnes_min'] ?> personnes
            </div>

            <!-- THEME -->
            <div class="small-text">
                <strong>Thème :</strong> <?= htmlspecialchars($menu['theme_nom']) ?>
            </div>

            <!-- REGIME -->
            <div class="small-text">
                <strong>Régime :</strong> <?= htmlspecialchars($menu['regime_nom']) ?>
            </div>


            <!-- COMMANDER OU DEMANDER DEVIS -->

            <!--COMMANDER UNIQUEMENT SI MENU DISPO-->
            <?php if ($menu['disponible'] == 1 && $menu['stock'] > 0): ?>

                <a href="commande.php?id=<?= $menu['id'] ?>&add=<?= $menu['id'] ?>" class="btn btn-red">AJOUTER AU
                    PANIER</a>

            <?php else: ?>
                <p class="small-text">Menu actuellement indisponible</p>

            <?php endif; ?>

            <a href="contact.php" class="btn btn-dark">DEMANDER UN DEVIS</a>


            <!-- CONDITION & DISPO.-->

            <div class="small-text">Commande minimum <?= $menu['delai_commande'] ?> jours à l'avance</div>
            <div class="small-text"><strong><?= $menu['stock'] ?> disponibles</strong></div>

            <!-- ALLERGENES -->
            <div class="small-text">
                <strong> Allergènes : </strong>
                <?php if (!empty($allergens)): ?>
                    <?= htmlspecialchars(implode(', ', $allergens)) ?>
                <?php else: ?>
                    Aucun allergène indiqué
                <?php endif; ?>
            </div>



        </section>

    </section>

</section>



<div class="accueil-image">
    <img src="public/assets/img/web/web_sauce.png" alt="SAUCE">
</div>



<section class="s-d-small">

    <!-- AVIS -->

    <h1>PAROLES DE GOURMANDS</h1>

    <div class="reviews-container">

        <?php if (!empty($reviews)): ?>

            <?php foreach ($reviews as $review): ?>

                <div class="review">

                    <h3><?= htmlspecialchars($review['nom']) ?>
                        <?= htmlspecialchars($review['prenom']) ?>
                    </h3>
                    <p><?= htmlspecialchars($review['note']) ?>/5 ★</p>
                    <p><?= htmlspecialchars($review['commentaire']) ?></p>
                </div>

            <?php endforeach; ?>
        <?php else: ?>

            <!-- SI X AVIS -->

            <p>Aucun avis pour le moment.</p>
        <?php endif; ?>

    </div>

</section>

</script>

<!-- FOOTER-->

<?php require_once 'includes/footer.php'; ?>

</body>

</html>