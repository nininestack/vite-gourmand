<?php

// SESSION ET SECURITE

session_start();

require_once 'database/database.php';

// ACCES ADMIN & EMPLOYE 

if (
    !isset($_SESSION['user_role']) ||
    ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'employe')
) {
    header("Location: login.php");
    exit();
}

// VERIFIER ID DE STOCK ENVOYE URL

if (!isset($_GET['id'])) {

    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin.php#stocks");
    } else {
        header("Location: employe.php#stocks");
    }

    exit();
}

// RECUPERER STOCK CONCERNE DEPUIS BASE DE DONNEE
// RECUPERER NOM MENU ASSOCIE 
// RELIER STOCKS & MENUS
// PRENDRE UNIQ STOCK DEMANDE
// REMPLACER PAR ID RECU DANS URL

$stmt = $pdo->prepare("

SELECT
    s.*,
    m.nom AS menu
FROM stocks s
JOIN menus m ON s.menus_id = m.id
WHERE s.id = ?
");

$stmt->execute([$_GET['id']]);
$stock = $stmt->fetch(PDO::FETCH_ASSOC);

// SI X STOCK TROUVE

if (!$stock) {
    die("Stock introuvable.");
}

?>

<!-- HEADER-->

<?php require_once 'includes/header_edit.php'; ?>


<section class="s-d--small">

    <!-- MODIFIER STOCK -->

    <h1>METTRE A JOUR LE STOCK</h1>

</section>

<section class="section-center-edit">

    <!-- RENVOI VERS UPDATE_STOCK -->

    <form method="POST" action="update_stock.php">

        <input type="hidden" name="id" value="<?= $stock['id'] ?>">

        <!-- MENU CONCERNE -->

        <p><strong>Menu :</strong>
            <?= htmlspecialchars($stock['menu']) ?>
        </p>

        <!-- MODIFICATION QTE -->

        <label>Stock</label>

        <input type="number" name="stock" value="<?= $stock['stock'] ?>" min="0" required>

        <br><br>

        <!-- STATUT AUTOMATIQUE -->

        <p>
            <strong>Statut actuel :</strong>
            <?= $stock['stock'] > 0 ? 'Disponible' : 'Rupture' ?>
        </p>

        <!-- ENREGISTRER -->

        <button type="submit" class="user-btn btn-create">Enregistrer</button>

        <!-- ANNULER -->
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <a href="admin.php#stock"> <button type="button" class="user-btn btn-return">Annuler</button> </a>
        <?php else: ?>
            <a href="employe.php#stock"> <button type="button" class="user-btn btn-return">Annuler</button> </a>
        <?php endif; ?>
    </form>

</section>

<div class="separation1"></div>

<div class="accueil-image">
    <picture>
        <source media="(max-width: 768px)" srcset="public/assets/img/mob/mob_coppa.png">
        <source media="(min-width: 769px)" srcset="public/assets/img/web/web_coppa.png">
        <img src="public/assets/img/mob/mob_coppa.png" alt="ACCUEIL">
    </picture>
</div>


<!-- FOOTER-->

<?php require_once 'includes/footer.php'; ?>

</body>

</html>