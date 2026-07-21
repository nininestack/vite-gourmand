<?php

// SESSION ET SECURITE

session_start();
require_once 'config/database/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'client') {
    header("Location: login.php");
    exit();
}

// RECUPERER LES INFORMATIONS DU CLIENT

$stmt = $pdo->prepare("
    SELECT *
    FROM users
    WHERE id = :id
");

$stmt->execute([
    'id' => $_SESSION['user_id']
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$user) {
    header("Location: login.php");
    exit();
}

// RECUPERER LES COMMANDES DU CLIENT

$stmt = $pdo->prepare("
SELECT *
FROM orders
WHERE users_id = :user_id
ORDER BY date_commande DESC
");

$stmt->execute([
    'user_id' => $_SESSION['user_id']
]);

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// LABEL DES STATUTS COMMANDES

$statusLabel = [
    'en_attente' => 'En attente',
    'en_preparation' => 'En préparation',
    'prete' => 'Prête',
    'livree' => 'Livrée',
    'annulee' => 'Annulée'
];

?>

<!-- HEADER-->

<?php require_once 'includes/header_user.php'; ?>

<!-- MESSAGE DE SUCCES DE CREATION DE COMPTE-->
<?php if (isset($_GET['success']) && $_GET['success'] === 'register'): ?>
    <p class="success-message">Votre compte a bien été créé !</p>
<?php endif; ?>

<!-- MESSAGE DE SUCCES MODIF MDP-->
<?php if (isset($_GET['success']) && $_GET['success'] === 'password_changed'): ?>
    <p class="success-message">Votre mot de passe a été modifié avec succès.</p>
<?php endif; ?>


<section class="s-d--small">

    <!-- BIENVENUE CLIENT -->

    <h1>
        BIENVENUE <?= htmlspecialchars($_SESSION['user_name']) ?>
    </h1>

</section>

<section class="s-l-small">
    <img src="public/assets/img/web/web_connecte.png" alt="USER">
</section>


<!-- INFORMATIONS CLIENT-->

<section class="user-layout">

    <section class="user">

        <form class="user-form" method="POST" action="update_user.php">

            <h2>MES INFORMATIONS</h2>

            <?php if (isset($_GET['success']) && $_GET['success'] === 'updated'): ?>
                <p class="success-message">Vos informations ont été mises à jour.</p>
            <?php endif; ?>

            <div class="input-group">
                <label for="name"></label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['nom']) ?>" required>
            </div>

            <div class="input-group">
                <label for="surname"></label>
                <input type="text" id="surname" name="surname" value="<?= htmlspecialchars($user['prenom']) ?>"
                    required>
            </div>

            <div class="input-group">
                <label for="email"></label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="input-group">
                <label for="phone"></label>
                <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['telephone']) ?>" required>
            </div>

            <div class="input-group">
                <label for="adress"></label>
                <input type="text" id="adress" name="adress" value="<?= htmlspecialchars($user['adresse']) ?>" required>
            </div>


            <!-- FORMULAIRE DE MODIFICATIONS INF. ET MDP-->

            <button type="submit" class="user-btn btn-edit">MODIFIER MES INFORMATIONS</button>

            <a href="modifier_password.php" class="user-btn btn-return">MODIFIER MON MOT DE PASSE</a>

        </form>

    </section>

    <!-- COMMANDE EN COURS-->

    <section class="current-order">

        <h2>MES COMMANDES EN COURS</h2>

        <?php

        $currentOrder = false;

        foreach ($orders as $order):

            if ($order['statut'] !== 'livree' && $order['statut'] !== 'annulee'):

                $currentOrder = true;
                ?>

                <div class="current-order-card">

                    <p><strong>COMMANDE :</strong> #<?= $order['id'] ?></p>

                    <p><strong>DATE :</strong>
                        <?= htmlspecialchars($order['date_commande']) ?>
                    </p>

                    <p><strong>LIVRAISON :</strong>
                        <?= htmlspecialchars($order['date_livraison']) ?>
                    </p>

                    <p><strong>STATUT :</strong>
                        <?= $statusLabel[$order['statut']] ?>
                    </p>

                    <a href="recapitulatif.php?id=<?= $order['id'] ?>" class="user-btn btn-edit">
                        VOIR LE RÉCAPITULATIF
                    </a>

                </div>

                <hr>

                <?php

            endif;

        endforeach;


        if (!$currentOrder):

            ?>

            <p>Aucune commande en cours.</p>

        <?php endif; ?>

    </section>

</section>


<section class="user-layout">

    <!-- HISTORIQUE DES COMMANDES-->

    <section class="order-history">

        <h2>HISTORIQUE DE MES COMMANDES</h2>

        <?php

        // VERIFIER S'IL EXISTE UN HISTORIQUE
        
        $hasHistory = false;
        foreach ($orders as $order):

            // AFFICHER UNIQUEMENT LES COMMANDES LIVREES OU ANNULEES
        
            if ($order['statut'] === 'livree' || $order['statut'] === 'annulee'):
                $hasHistory = true;
                ?>

                <!-- CARTE DE COMMANDES -->

                <div class="history-card">

                    <p><strong>COMMANDE :</strong> #<?= $order['id'] ?></p>

                    <p><strong>DATE :</strong>
                        <?= htmlspecialchars($order['date_commande']) ?>
                    </p>

                    <p><strong>LIVRAISON PRÉVUE LE :</strong>
                        <?= htmlspecialchars($order['date_livraison']) ?>
                    </p>

                    <p><strong>STATUT :</strong>
                        <?= $statusLabel[$order['statut']] ?>
                    </p>

                    <!-- VOIR LE RECAPITULATIF-->

                    <a href="recapitulatif.php?id=<?= $order['id'] ?>" class="user-btn btn-edit">
                        VOIR LE RÉCAPITULATIF
                    </a>

                    <?php

                    // LAISSER UN AVIS UNIQUEMENT SI LA COMMANDE EST LIVREE
            
                    if ($order['statut'] === 'livree'): ?>

                        <a href="review.php?order=<?= $order['id'] ?>" class="user-btn btn-create">
                            LAISSER UN AVIS
                        </a>

                    <?php endif; ?>

                </div>
                <hr>
            <?php endif; endforeach;

        // SI X HISTORIQUE 
        
        if (!$hasHistory):
            ?>

            <p>Aucune commande dans votre historique.</p>

        <?php endif; ?>



    </section>

</section>


<!-- BOUTON DE DECONNEXION -->

<div class="logout">
    <a href="deconnexion.php" class="user-btn btn-edit">
        SE DÉCONNECTER
    </a>
</div>

<div class="separation1"></div>

<div class="accueil-image">
    <img src="public/assets/img/web/web_coppa.png" alt="COPPA">
</div>


<!-- FOOTER-->

<?php require_once 'includes/footer.php'; ?>

</body>

</html>