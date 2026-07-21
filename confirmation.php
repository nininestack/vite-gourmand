<?php

// SESSION + DATABASE

session_start();
require_once 'config/database/database.php';

// VERIFICATION CLIENT

if (
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'client'
) {
    header("Location: login.php");
    exit();
}

// VERIFICATION ID COMMANDE

if (
    !isset($_GET['id'])
) {
    header("Location: user.php");
    exit();
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// RECUPERATION COMMANDE

$stmt = $pdo->prepare("
SELECT *
FROM orders
WHERE id = ?
AND users_id = ?
");

$stmt->execute([
    $order_id,
    $user_id
]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: user.php");
    exit();
}

// RECUPERATION DES MENUS

$stmt = $pdo->prepare("
SELECT
oders_items.quantite,
oders_items.prix_unitaire,
menus.nom


FROM oders_items

INNER JOIN menus

ON menus.id = oders_items.menus_id

WHERE oders_items.orders_id = ?

");


$stmt->execute([
    $order_id
]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// STATUT 

$statuts = [
    'en_attente' => 'En attente de préparation',
    'preparation' => 'En préparation',
    'livraison' => 'En livraison',
    'terminee' => 'Terminée',
    'annulee' => 'Annulée'
];


?>

<?php require_once 'includes/header_user.php'; ?>

<section class="s-d--small">

    <h1>COMMANDE CONFIRMÉE</h1>
</section>


<section class="order-recap">

    <h2>Merci <?= htmlspecialchars($_SESSION['user_name']) ?> !</h2>

    <p>Votre commande a bien été enregistrée.</p>

    <h3>Numéro de commande : #<?= $order['id'] ?></h3>

    <hr>

    <h2>Informations livraison</h2>

    <p>Date de commande : <?= date('d/m/Y H:i', strtotime($order['date_commande'])) ?></p>
    <p>Livraison prévue le : <?= date('d/m/Y à H:i', strtotime($order['date_livraison'])) ?></p>
    <p>Adresse : <?= htmlspecialchars($order['adresse_livraison']) ?></p>

    <?php if (!empty($order['commentaire'])): ?>
        <p>Commentaire : <?= htmlspecialchars($order['commentaire']) ?></p>
    <?php endif; ?>

    <hr>

    <h2>Détail de votre commande</h2>

    <?php foreach ($items as $item): ?>

        <div class="recap-menu">
            <div class="recap-menu-info">

                <h4><?= htmlspecialchars($item['nom']) ?></h4>

                <p>Quantité : <?= $item['quantite'] ?>personnes</p>
                <p>Prix unitaire : <?= $item['prix_unitaire'] ?> €</p>
                <p>Sous-total :
                    <?=
                        $item['prix_unitaire']
                        *
                        $item['quantite']
                        ?> €
                </p>

            </div>
        </div>

        <hr>
    <?php endforeach; ?>

    <h2>Récapitulatif paiement</h2>

    <p>Sous-total :
        <?=
            $order['total']
            +
            $order['remise']
            -
            $order['frais_livraison']
            ?> €
    </p>
    <p>Remise : - <?= $order['remise'] ?> €</p>
    <p>Livraison : <?= $order['frais_livraison'] ?> €</p>

    <h2>Total : <?= $order['total'] ?> € </h2>

    <p>Statut : <?= $statuts[$order['statut']] ?? $order['statut'] ?></p>


    <!-- RETOUR COMPTE CLIENT-->

    <a href="user.php" class="user-btn btn-return">RETOUR A MON COMPTE</a>

</section>


<div class="accueil-image">
    <img src="public/assets/img/web/web_tomate.png" alt="TOMATE">
</div>

<!-- FOOTER-->

<?php require_once 'includes/footer.php'; ?>

</body>

</html>