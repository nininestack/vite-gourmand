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

// VERIFIER ENVOI FORMULAIRE

if (!isset($_GET['id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin.php#menus");
    } else {
        header("Location: employe.php#menus");
    }
    exit();
}

$id = (int) $_GET['id'];

$stmt = $pdo->prepare("
SELECT 
    m.*,
    s.stock
FROM menus m
LEFT JOIN stocks s
ON m.id = s.menus_id
WHERE m.id = ?
");

$stmt->execute([$id]);

$menu = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$menu) {
    die("Menu introuvable.");
}

// THEMES
$themes = $pdo->query("
SELECT *
FROM themes
ORDER BY nom
")->fetchAll(PDO::FETCH_ASSOC);

// DIETS
$diets = $pdo->query("
SELECT *
FROM diets
ORDER BY nom
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- HEADER-->

<?php require_once 'includes/header_edit.php'; ?>


<section class="s-d--small">

    <!-- MODIFIER MENU -->

    <h1>METTRE A JOUR LE MENU</h1>

</section>

<section class="section-center-edit">

    <!-- RENVOI VERS UPDATE_MENU -->

    <form method="POST" action="update_menu.php">


        <input type="hidden" name="id" value="<?= $menu['id'] ?>">

        <!-- MODIFICATIONS DU MENU CHOISI -->

        <label>Nom</label>

        <input type="text" name="nom" value="<?= htmlspecialchars($menu['nom']) ?>" required>

        <p>Stock actuel :<?= $menu['stock'] ?? 0 ?></p>

        <label>Prix</label>

        <input type="number" step="0.01" name="prix" value="<?= $menu['prix'] ?>" required>

        <label>Nombre minimum de personnes</label>

        <input type="number" name="personnes_min" value="<?= $menu['personnes_min'] ?>" required>

        <label>Description</label>

        <textarea name="description" required>
  <?= htmlspecialchars($menu['description']) ?>
</textarea>

        <label>Thème</label>

        <select name="themes_id">
            <?php foreach ($themes as $theme): ?>
                <option value="<?= $theme['id'] ?>" <?= $theme['id'] == $menu['themes_id'] ? "selected" : "" ?>>
                    <?= htmlspecialchars($theme['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Régime alimentaire</label>

        <select name="diets_id">
            <?php foreach ($diets as $diet): ?>
                <option value="<?= $diet['id'] ?>" <?= $diet['id'] == $menu['diets_id'] ? "selected" : "" ?>>
                    <?= htmlspecialchars($diet['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <br><br>

        <!-- ENREGISTRER -->

        <button type="submit" class="user-btn btn-create">Enregistrer</button>

        <!-- ANNULER -->
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <a href="admin.php#menus"> <button type="button" class="user-btn btn-return">Annuler</button> </a>
        <?php else: ?>
            <a href="employe.php#menus"> <button type="button" class="user-btn btn-return">Annuler</button> </a>
        <?php endif; ?>

    </form>

</section>

<div class="separation1"></div>

<div class="accueil-image">
    <img src="public/assets/img/web/web_coppa.png" alt="ACCUEIL">
</div>


<!-- FOOTER-->

<?php require_once 'includes/footer.php'; ?>

</body>

</html>