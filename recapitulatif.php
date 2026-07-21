<?php

// SESSION ET SECURITE

session_start();
require_once 'config/database/database.php';

if (
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'client'
) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {

    header("Location: user.php");
    exit();

}

$order_id = intval($_GET['id']);

if ($order_id <= 0) {

    header("Location: user.php");
    exit();

}

// RECUPERATION COMMANDE

$stmt = $pdo->prepare("
SELECT *
FROM orders
WHERE id = ?
AND users_id = ?
");

$stmt->execute([
    $order_id,
    $_SESSION['user_id']
]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {

    header("Location:user.php");
    exit();

}

// RECUPERATION DES MENUS

$stmt = $pdo->prepare("
SELECT 
menus.nom,
menus.image_principale,
oders_items.quantite,
oders_items.prix_unitaire

FROM oders_items

JOIN menus

ON menus.id = oders_items.menus_id

WHERE oders_items.orders_id = ?

");

$stmt->execute([
    $order_id
]);

$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

// STATUT 

$statusLabel = [

    'en_attente' => 'En attente',
    'acceptee' => 'Acceptée',
    'en_preparation' => 'En préparation',
    'en_livraison' => 'En livraison',
    'livree' => 'Livrée',
    'annulee' => 'Annulée'

];

?>

<!-- HEADER-->

<?php require_once 'includes/header_user.php'; ?>

<section class="s-d--small">

    <!-- RECAPITULATIF COMMANDE -->

    <h1>RÉCAPITULATIF DE LA COMMANDE #<?= $order['id'] ?></h1>

</section>

<main class="order-recap">

    <h3>INFORMATIONS</h3>

    <!--INFOS COMMANDE-->
    <p>DATE DE COMMANDE :<?= date('d/m/Y H:i', strtotime($order['date_commande'])) ?></p>
    <p>STATUT :<?= $statusLabel[$order['statut']] ?? $order['statut'] ?></p>
    <p>LIVRAISON PRÉVUE LE :<?= date('d/m/Y H:i', strtotime($order['date_livraison'])) ?></p>

    <!--ADRESSE LIVRAISON-->
    <h3>ADRESSE DE LIVRAISON</h3>

    <p><?= htmlspecialchars($order['adresse_livraison']) ?></p>

    <!-- COMMENTAIRE EVENTUEL-->
    <?php if ($order['commentaire']): ?>
        <h3>COMMENTAIRE</h3>
        <p><?= htmlspecialchars($order['commentaire']) ?></p>
    <?php endif; ?>

    <!-- MENU(S) CHOISI(S)-->

    <h3>MA COMMANDE</h3>

    <?php foreach ($menus as $menu): ?>
        <div class="recap-menu">

            <!-- IMG DU MENU-->
            <img src="<?= htmlspecialchars($menu['image_principale']) ?>" alt="<?= htmlspecialchars($menu['nom']) ?>"
                class="recap-menu-image">

            <!-- NOM QTE PRIX ET TOTAL DU MENU-->
            <div class="recap-menu-info">

                <h4><?= htmlspecialchars($menu['nom']) ?></h4>

                <p>QUANTITÉ :<?= $menu['quantite'] ?> personnes</p>
                <p>PRIX UNITAIRE :<?= $menu['prix_unitaire'] ?> €</p>

                <p>TOTAL :<?= $menu['quantite'] * $menu['prix_unitaire'] ?> €</p>

            </div>
        </div>

    <?php endforeach; ?>

    <!-- TOTAL DE LA COMMANDE -->

    <h3>TOTAL</h3>

    <p>REMISE :<?= $order['remise'] ?> €</p>
    <p>LIVRAISON :<?= $order['frais_livraison'] ?> €</p>

    <h3>TOTAL PAYÉ :<?= $order['total'] ?> €</h3>

    <!-- RETOUR COMPTE CLIENT-->

    <a href="user.php" class="user-btn btn-return">RETOUR A MON COMPTE</a>

</main>

<div class="accueil-image">
    <img src="public/assets/img/web/web_tomate.png" alt="TOMATE">
</div>

<!-- FOOTER-->

<?php require_once 'includes/footer.php'; ?>

</body>

</html>