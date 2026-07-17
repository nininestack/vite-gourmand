<?php


// SESSION ET SECURITE

session_start();

require_once 'database/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
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
ORDER BY m.id DESC
");

$stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);


// COMMANDE J

$stmt = $pdo->query("
SELECT COUNT(*)
FROM orders
WHERE DATE(date_commande)=CURDATE()
");

$dailyOrders = $stmt->fetchColumn() ?? 0;


// CA J

$stmt = $pdo->query("
SELECT SUM(total)
FROM orders
WHERE DATE(date_commande)=CURDATE()
");

$dailyRevenue = $stmt->fetchColumn() ?? 0;


// COMMANDE M

$stmt = $pdo->query("
SELECT COUNT(*)
FROM orders
WHERE MONTH(date_commande)=MONTH(CURDATE())
AND YEAR(date_commande)=YEAR(CURDATE())
");

$monthlyOrders = $stmt->fetchColumn() ?? 0;


// CA M

$stmt = $pdo->query("
SELECT SUM(total)
FROM orders
WHERE MONTH(date_commande)=MONTH(CURDATE())
AND YEAR(date_commande)=YEAR(CURDATE())
");

$monthlyRevenue = $stmt->fetchColumn() ?? 0;


// COMMANDE A

$stmt = $pdo->query("
SELECT COUNT(*)
FROM orders
WHERE YEAR(date_commande)=YEAR(CURDATE())
");

$annualOrders = $stmt->fetchColumn() ?? 0;


// CA A

$stmt = $pdo->query("
SELECT SUM(total)
FROM orders
WHERE YEAR(date_commande)=YEAR(CURDATE())
");

$annualRevenue = $stmt->fetchColumn() ?? 0;


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

//EMPLOYE

$stmt = $pdo->query("SELECT
    e.id,
    e.users_id AS user_id,
    e.date_naissance,
    e.ville_naissance,
    e.type_contrat,
    e.date_embauche,
    e.salaire_heure,
    e.heures_semaine,

    u.nom,
    u.prenom,
    u.telephone,
    u.email,
    u.adresse

FROM employees e
JOIN users u ON e.users_id = u.id
ORDER BY e.id DESC");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);


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

// CONTACTS

$stmt = $pdo->query("
SELECT *
FROM contact_messages
ORDER BY date_envoi DESC
");

$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- HEADER-->

<?php require_once 'includes/header_admin.php'; ?>

<!-- MESSAGE DE SUCCES MODIF MDP-->
<?php if (isset($_GET['success']) && $_GET['success'] === 'password_changed'): ?>
    <p class="success-message">Votre mot de passe a été modifié avec succès.</p>
<?php endif; ?>

<section class="s-d--small">

    <!-- BIENVENUE ADMIN -->

    <h1>
        BIENVENUE <?= htmlspecialchars($_SESSION['user_name']) ?>
    </h1>
</section>


<section class="s-l-small">
    <img src="public/assets/img/web/web_admin.png" alt="ADMIN">
</section>


<!-- SECTION STATISTIQUES -->

<section id="statistiques">

    <h2>STATISTIQUES</h2>

    <div class="statistic">
        <div class="card">
            <div class="extra-small">Commande(s) journalière(s)</div>
            <span class="stat-value"><?= $dailyOrders ?></span>
        </div>

        <div class="card">
            <div class="extra-small">CA journalier</div>
            <span class="stat-value"><?= $dailyRevenue ?> €</span>
        </div>


        <div class="card">
            <div class="extra-small">Commande(s) mensuelle(s)</div>
            <span class="stat-value"><?= $monthlyOrders ?></span>
        </div>

        <div class="card">
            <div class="extra-small">CA mensuel</div>
            <span class="stat-value"><?= $monthlyRevenue ?> €</span>
        </div>


        <div class="card">
            <div class="extra-small">Commande(s) annuelle(s)</div>
            <span class="stat-value"><?= $annualOrders ?></span>
        </div>

        <div class="card">
            <div class="extra-small">CA annuel</div>
            <span class="stat-value"><?= $annualRevenue ?> €</span>
        </div>
    </div>

    <div class="graphique">

        <div class="statistics-header">
            <div class="extra-small">COMMANDES/MENU</div>


        </div>


        <div class="chart-container">
            <canvas id="menuChart"></canvas>
        </div>

        <button type="button" id="refreshStats" class="user-btn btn-create">
            ACTUALISER
        </button>
    </div>


</section>


<!-- SECTION COMMANDES -->

<section id="commandes">

    <h2>GERER LES COMMANDES</h2>


    <form method="GET" action="admin.php#commandes" class="filters">

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
        <a href="admin.php#commandes" class="user-btn btn-return">Réinitialiser</a>


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


<!-- SECTION EMPLOYES -->

<section id="employes">

    <h2>GERER LES EMPLOYES</h2>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'employee_created'): ?>
        <p>Employé créé avec succès</p>
    <?php endif; ?>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Date de naissance</th>
                    <th>Ville de naissance</th>
                    <th>Adresse</th>
                    <th>T.C</th>
                    <th>Date d'embauche</th>
                    <th>Salaire €/h</th>
                    <th>H/M</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>

                <!-- BOUCLE POUR AFFICHER LES EMPLOYES -->

                <?php foreach ($employees as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['nom']) ?></td>
                        <td><?= htmlspecialchars($e['prenom']) ?></td>
                        <td><?= htmlspecialchars($e['date_naissance']) ?></td>
                        <td><?= htmlspecialchars($e['ville_naissance']) ?></td>
                        <td><?= htmlspecialchars($e['adresse']) ?></td>
                        <td><?= htmlspecialchars($e['type_contrat']) ?></td>
                        <td><?= htmlspecialchars($e['date_embauche']) ?></td>
                        <td><?= htmlspecialchars($e['salaire_heure']) ?></td>
                        <td><?= htmlspecialchars($e['heures_semaine']) ?></td>
                        <td><?= htmlspecialchars($e['telephone']) ?></td>
                        <td><?= htmlspecialchars($e['email']) ?></td>

                        <td>
                            <a href="edit_employee.php?id=<?= $e['id'] ?>">
                                <button type="button" class="user-btn btn-edit">Modifier</button>
                            </a>

                            <form method="POST" action="delete_employee.php"
                                onsubmit="return confirm('Supprimer cet employé ?');">
                                <input type="hidden" name="employee_id" value="<?= $e['id'] ?>">
                                <input type="hidden" name="user_id" value="<?= $e['user_id'] ?>">
                                <button type="submit" class="user-btn btn-return">Supprimer</button>
                            </form>
                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>

        </table>
    </div>


    <!-- FORMULAIRE DE CREATION D'EMPLOYE -->

    <details class="user-details">

        <summary>CREER UN EMPLOYE</summary>

        <form method="POST" action="create_employee.php" class="user-form" autocomplete="off">

            <input type="text" name="nom" placeholder="Nom" required>
            <input type="text" name="prenom" placeholder="Prénom" required>

            <input type="date" name="date_naissance" placeholder="Date de naissance" required>
            <input type="text" name="ville_naissance" placeholder="Ville de naissance" required>

            <input type="text" name="adresse" placeholder="Adresse" required>

            <select name="type_contrat" required>
                <option value="">Type de contrat</option>
                <option value="CDI">CDI</option>
                <option value="CDD">CDD</option>
                <option value="Interim">Intérim</option>
            </select>

            <input type="date" name="date_embauche" placeholder="Date d'embauche" required>

            <input type="number" step="0.01" name="salaire_heure" placeholder="Salaire/Heure" required>

            <input type="number" name="heures_semaine" placeholder="Heures/Semaine" required>

            <input type="tel" name="telephone" placeholder="Téléphone" required>
            <input type="email" name="email" placeholder="Email" autocomplete="off" required>

            <input type="password" name="mot_de_passe" placeholder="Mot de passe temporaire" autocomplete="new-password"
                required>

            <button type="submit" class="user-btn btn-create" onclick="console.log('submit OK')">
                Créer l'employé
            </button>

        </form>

    </details>

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
                        <td><?= $menu['stock'] > 0 ? $menu['stock'] . " en stock" : "Rupture" ?></td>

                        <td>
                            <form method="GET" action="edit_menu.php">
                                <input type="hidden" name="id" value="<?= $menu['id'] ?>">
                                <button type="submit" class="user-btn btn-edit">Modifier</button>
                            </form>

                            <form action="delete_menu.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $menu['id'] ?>">

                                <button type="submit" onclick="return confirm('Supprimer ce menu ?')"
                                    class="user-btn btn-return">
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
                                    class="user-btn btn-return">
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
                                <button type="submit" class="user-btn btn-return">Supprimer</button>
                            </form>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</section>


<!-- SECTION CONTACTS -->

<section id="contacts">

    <h2>MESSAGES RECUS</h2>

    <div class="table-responsive">
        <table>

            <thead>
                <tr>
                    <th>Date</th>
                    <th>Nom</th>
                    <th>Entreprise</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Message</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($contacts as $contact): ?>

                    <tr>
                        <td><?= htmlspecialchars($contact['date_envoi']) ?></td>
                        <td><?= htmlspecialchars($contact['nom']) ?></td>
                        <td><?= htmlspecialchars($contact['entreprise']) ?></td>
                        <td><?= htmlspecialchars($contact['email']) ?></td>
                        <td><?= htmlspecialchars($contact['telephone']) ?></td>
                        <td><?= nl2br(htmlspecialchars($contact['message'])) ?></td>
                        <td>

                            <form method="POST" action="delete_contact.php">
                                <input type="hidden" name="contact_id" value="<?= $contact['id'] ?>">

                                <button class="user-btn btn-return" type="submit">Supprimer</button>
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
<script src="public/assets/js/chart.umd.min.js"></script>
<script>

    let menuChart;

    function loadStatistics() {

        fetch("api/statistiques_menus.php")

            .then(response => response.json())

            .then(data => {

                const menus = data.map(item => item.menu);
                const commandes = data.map(item => item.commandes);

                if (menuChart) {
                    menuChart.destroy();
                }

                menuChart = new Chart(
                    document.getElementById("menuChart"),
                    {
                        type: "bar",

                        data: {
                            labels: menus,

                            datasets: [
                                {
                                    label: "Nombre de commandes",
                                    data: commandes
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    }
                );

            });
    }

    // CHARGEMENT INITIAL DU GRAPHIQUE

    loadStatistics();

    // BTN ACTUALISER

    document
        .getElementById("refreshStats")
        .addEventListener("click", function () {

            console.log("clic détecté");

            fetch("api/sync_stats.php")

                .then(response => response.json())

                .then(result => {

                    console.log(result);

                    if (result.success) {

                        alert("Statistiques mises à jour !");

                        loadStatistics();

                    } else {

                        alert(result.message);

                    }

                })

                .catch(error => {
                    console.error("Erreur :", error);
                });

        });
</script>


<!-- FOOTER-->

<?php require_once 'includes/footer.php'; ?>

</body>

</html>