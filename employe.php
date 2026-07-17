<?php


// SESSION ET SECURITE

session_start();

require_once 'database/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'employe') {
    header("Location: login.php");
    exit();
}


// REQUETE COMMANDE

$sql = "
SELECT
    o.id,
    o.date_commande,
    o.date_livraison,
    o.statut,
    o.total,
    o.adresse_livraison,

    u.email,
    u.nom AS client_name,

    GROUP_CONCAT(m.nom SEPARATOR ', ') AS menu_name,
    SUM(oi.quantite) AS quantite

    FROM orders o

    JOIN users u ON o.users_id = u.id
    JOIN oders_items oi ON o.id = oi.orders_id
    JOIN menus m ON oi.menus_id = m.id
    ";

// FILTRAGE DES COMMANDES

$where = [];
$params = [];

if (!empty($_GET['search'])) {
    $where[] = "(
        o.id LIKE :search
        OR u.nom LIKE :search
        OR u.email LIKE :search
    )";

    $params['search'] = "%" . $_GET['search'] . "%";
}

if (!empty($_GET['status']) && $_GET['status'] !== 'all') {
    $where[] = "o.statut = :status";
    $params['status'] = $_GET['status'];
}

if (!empty($_GET['date'])) {
    $where[] = "DATE(o.date_livraison) = :date";
    $params['date'] = $_GET['date'];
}


if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= "
    GROUP BY o.id
    ORDER BY o.date_commande DESC
";

// EXECUTION DE LA REQUETE

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);


// STOCK

$stmt = $pdo->query("
SELECT
    s.id,
    s.stock,
    s.disponible,
    m.nom AS menu
FROM stocks s
JOIN menus m ON s.menus_id = m.id
");

$stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);



// COMMANDE STATUTS

$statusLabel = [
    'en_attente' => 'En attente',
    'en_preparation' => 'En préparation',
    'prete' => 'Prête',
    'livree' => 'Livrée',
    'annulee' => 'Annulée'
];


// REVIEWS

$sql = "
SELECT r.*, u.email
FROM reviews r
JOIN users u ON r.users_id = u.id
";

$params = [];

if (!empty($_GET['review_status']) && $_GET['review_status'] !== 'all') {

    $sql .= " WHERE r.statut = ?";
    $params[] = $_GET['review_status'];

}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);


// MENUS

$stmt = $pdo->query("
SELECT
    m.*,
    t.nom AS theme,
    d.nom AS diet,
    COALESCE(s.stock,0) AS stock

FROM menus m

JOIN themes t
ON m.themes_id = t.id

JOIN diets d
ON m.diets_id = d.id

LEFT JOIN stocks s
ON m.id = s.menus_id

ORDER BY m.id DESC
");
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

// THEMES ET DIETS

$stmt = $pdo->query("SELECT * FROM themes ORDER BY nom");
$themes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT * FROM diets ORDER BY nom");
$diets = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- HEADER-->

<?php require_once 'includes/header_employe.php'; ?>

<!-- MESSAGE DE SUCCES MODIF MDP-->
<?php if (isset($_GET['success']) && $_GET['success'] === 'password_changed'): ?>
    <p class="success-message">Votre mot de passe a été modifié avec succès.</p>
<?php endif; ?>


<section class="s-d--small">

    <!-- BIENVENUE EMPLOYE -->

    <h1>
        BIENVENUE <?= htmlspecialchars($_SESSION['user_name']) ?>
    </h1>
</section>


<section class="s-l-small">
    <img src="public/assets/img/web/web_employe.png" alt="EMPLOYE">
</section>



<!-- SECTION COMMANDES -->

<section id="commandes">

    <h2>GERER LES COMMANDES</h2>

    <form method="GET" class="filters">

        <input type="search" name="search" placeholder="N° commande, client ou email..."
            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

        <input type="date" name="date" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">

        <select name="status" onchange="this.form.submit()">

            <option value="all" <?= (($_GET['status'] ?? '') == 'all') ? 'selected' : '' ?>>
                Toutes les commandes
            </option>

            <option value="en_attente" <?= (($_GET['status'] ?? '') == 'en_attente') ? 'selected' : '' ?>>
                En attente
            </option>

            <option value="en_preparation" <?= (($_GET['status'] ?? '') == 'en_preparation') ? 'selected' : '' ?>>
                En préparation
            </option>

            <option value="prete" <?= (($_GET['status'] ?? '') == 'prete') ? 'selected' : '' ?>>
                Prête
            </option>

            <option value="livree" <?= (($_GET['status'] ?? '') == 'livree') ? 'selected' : '' ?>>
                Livrée
            </option>

            <option value="annulee" <?= (($_GET['status'] ?? '') == 'annulee') ? 'selected' : '' ?>>
                Annulée
            </option>

        </select>

        <button type="submit" class="user-btn btn-create">Filtrer</button>
        <a href="employe.php#commandes" class="user-btn btn-delete">Réinitialiser</a>


    </form>

    <!-- TABLEAU DES COMMANDES -->

    <div class="table-responsive">
        <table>

            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Email</th>
                    <th>Date de livraison</th>
                    <th>Adresse de livraison</th>
                    <th>Statut</th>
                    <th>Menu</th>
                    <th>Quantité</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <!-- BOUCLE POUR AFFICHER LES COMMANDES -->

            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($order['id']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($order['date_commande'])) ?></td>
                        <td><?= htmlspecialchars($order['client_name']) ?></td>
                        <td><?= htmlspecialchars($order['email']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($order['date_livraison'])) ?></td>
                        <td><?= htmlspecialchars($order['adresse_livraison']) ?></td>
                        <td><?= $statusLabel[$order['statut']] ?? $order['statut'] ?></td>
                        <td><?= htmlspecialchars($order['menu_name']) ?></td>
                        <td><?= htmlspecialchars($order['quantite']) ?></td>
                        <td><?= htmlspecialchars($order['total']) ?> €</td>

                        <td>
                            <form method="POST" action="update_order_status.php" style="display:flex; gap:5px;">

                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

                                <select name="status" onchange="this.form.submit()">

                                    <option value="en_attente" <?= $order['statut'] == 'en_attente' ? 'selected' : '' ?>>
                                        En attente
                                    </option>

                                    <option value="en_preparation" <?= $order['statut'] == 'en_preparation' ? 'selected' : '' ?>>
                                        En préparation
                                    </option>

                                    <option value="prete" <?= $order['statut'] == 'prete' ? 'selected' : '' ?>>
                                        Prête
                                    </option>

                                    <option value="livree" <?= $order['statut'] == 'livree' ? 'selected' : '' ?>>
                                        Livrée
                                    </option>

                                    <option value="annulee" <?= $order['statut'] == 'annulee' ? 'selected' : '' ?>>
                                        Annulée
                                    </option>

                                </select>

                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>

        </table>
    </div>

</section>



<!-- SECTION MENUS -->

<section id="menus">

    <h2>GERER LES MENUS</h2>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prix</th>
                    <th>Personnes min.</th>
                    <th>Thème</th>
                    <th>Régime</th>
                    <th>Disponible</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <!-- BOUCLE POUR AFFICHER LES MENUS -->

            <tbody>
                <?php foreach ($menus as $menu): ?>
                    <tr>
                        <td><?= htmlspecialchars($menu['nom']) ?></td>
                        <td><?= number_format($menu['prix'], 2, ',', ' ') ?> €</td>
                        <td><?= $menu['personnes_min'] ?></td>
                        <td><?= htmlspecialchars($menu['theme']) ?></td>
                        <td><?= htmlspecialchars($menu['diet']) ?></td>
                        <td><?= $menu['stock'] > 0 ? $menu['stock'] . " en stock" : "Non" ?></td>

                        <td>
                            <form method="GET" action="edit_menu.php">
                                <input type="hidden" name="id" value="<?= $menu['id'] ?>">
                                <button type="submit" class="user-btn btn-edit">Modifier</button>
                            </form>

                            <form action="delete_menu.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $menu['id'] ?>">

                                <button type="submit" onclick="return confirm('Supprimer ce menu ?')"
                                    class="user-btn btn-delete">
                                    Supprimer
                                </button>
                            </form>
                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>

        </table>
    </div>

    <!-- FORMULAIRE DE CREATION DE MENU -->

    <details class="user-details">

        <summary>CREER UN NOUVEAU MENU</summary>

        <form method="POST" action="create_menu.php" class="user-form">

            <input type="text" name="nom" placeholder="Nom du menu" required>
            <input type="number" name="prix" placeholder="Prix" required>
            <input type="number" name="personnes_min" placeholder="Personnes min" required>

            <textarea name="description" placeholder="Description" required></textarea>

            <select name="themes_id" required>
                <option value="">Choisir un thème</option>
                <?php foreach ($themes as $t): ?>
                    <option value="<?= $t['id'] ?>">
                        <?= htmlspecialchars($t['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>


            <select name="diets_id" required>
                <option value="">Choisir un régime</option>
                <?php foreach ($diets as $d): ?>
                    <option value="<?= $d['id'] ?>">
                        <?= htmlspecialchars($d['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="user-btn btn-create">
                Créer le menu
            </button>

        </form>

    </details>

</section>


<!-- SECTION STOCKS -->

<section id="stocks">

    <h2>GERER LES STOCKS</h2>

    <div class="table-responsive">
        <table>

            <thead>
                <tr>
                    <th>Menu</th>
                    <th>Stock</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <!-- BOUCLE POUR AFFICHER LES STOCKS -->

            <tbody>
                <?php foreach ($stocks as $stock): ?>

                    <tr>
                        <td><?= htmlspecialchars($stock['menu']) ?></td>
                        <td><?= $stock['stock'] ?></td>
                        <td><?= $stock['stock'] > 0 ? 'Disponible' : 'Rupture' ?></td>

                        <td>

                            <a href="edit_stock.php?id=<?= $stock['id'] ?>">
                                <button type="button" class="user-btn btn-edit">Modifier</button>
                            </a>

                            <form method="POST" action="delete_stock.php" style="display:inline;">

                                <input type="hidden" name="id" value="<?= $stock['id'] ?>">

                                <button type="submit" onclick="return confirm('Mettre ce menu en rupture de stock ?')"
                                    class="user-btn btn-delete">
                                    Rupture
                                </button>

                            </form>

                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</section>




<!-- SECTION AVIS CLIENTS -->

<section id="avis-clients">

    <h2>GERER LES AVIS CLIENTS</h2>

    <!-- TABLEAU DES AVIS CLIENTS -->

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Score</th>
                    <th>Commentaire</th>
                    <th>Client</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <!-- BOUCLE POUR AFFICHER LES AVIS CLIENTS -->

            <tbody>
                <?php foreach ($reviews as $review): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($review['date_creation'])) ?></td>

                        <td><?= date('H:i', strtotime($review['date_creation'])) ?></td>

                        <td><?= htmlspecialchars($review['note']) ?>/5</td>
                        <td><?= htmlspecialchars($review['commentaire']) ?></td>
                        <td><?= htmlspecialchars($review['email']) ?></td>
                        <td><?= $review['statut'] === 'valide' ? 'Validé' : ($review['statut'] === 'annule' ? 'Annulé' : 'En attente') ?>
                        </td>

                        <td>

                            <form method="POST" action="approve_review.php" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $review['id'] ?>">
                                <button type="submit" class="user-btn btn-edit">Valider</button>
                            </form>

                            <form method="POST" action="delete_review.php" style="display:inline;"
                                onsubmit="return confirm('Supprimer cet avis ?');">
                                <input type="hidden" name="id" value="<?= $review['id'] ?>">
                                <button type="submit" class="user-btn btn-delete">Supprimer</button>
                            </form>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</section>


<!-- BOUTON DE DECONNEXION ET MODIF MDP -->

<div class="logout">
    <a href="deconnexion.php" class="user-btn btn-edit">SE DÉCONNECTER</a>
    <a href="modifier_password.php" class="user-btn btn-return">MODIFIER MON MOT DE PASSE</a>
</div>


<script src="public/assets/js/admin.js"></script>


<!-- FOOTER-->

<?php require_once 'includes/footer.php'; ?>

</body>

</html>