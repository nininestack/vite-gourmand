<?php

// CONNECTION A LA BASE DE DONNEES

session_start();
require_once 'config/database/database.php';


// VERIFICATION DU PANIER

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}


// ACTIONS DU PANIER

if (isset($_GET['action']) && isset($_GET['id'])) {

    $action = $_GET['action'];
    $menu_id = intval($_GET['id']);

    foreach ($_SESSION['panier'] as $key => &$item) {


        if ($item['menu_id'] == $menu_id) {


            if ($action === "plus") {
                $item['quantite']++;
            } elseif ($action === "moins") {

                $stmt = $pdo->prepare("
                    SELECT personnes_min
                    FROM menus
                    WHERE id = ?
                ");

                $stmt->execute([
                    $menu_id
                ]);

                $menu = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($menu) {

                    if ($item['quantite'] > $menu['personnes_min']) {
                        $item['quantite']--;
                    }
                }
            } elseif ($action === "supprimer") {
                unset($_SESSION['panier'][$key]);
            }

            break;
        }

    }

    $_SESSION['panier'] = array_values($_SESSION['panier']);

    header("Location: commande.php");
    exit();
}

// AJOUT MENU AU PANIER

if (isset($_GET['add'])) {
    $menu_id = intval($_GET['add']);

    // VERIFIER MENU EXISTE

    $stmt = $pdo->prepare("
        SELECT personnes_min
        FROM menus
        WHERE id = ?
    ");

    $stmt->execute([$menu_id]);
    $menu = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($menu) {

        // VERIFIER SI MENU DEJA DANS PANIER

        $deja_present = false;

        foreach ($_SESSION['panier'] as &$item) {

            if ($item['menu_id'] == $menu_id) {

                $deja_present = true;
                break;
            }
        }
        unset($item);

        // SINON AJOUT

        if (!$deja_present) {
            $_SESSION['panier'][] = [
                "menu_id" => $menu_id,
                "quantite" => $menu['personnes_min']
            ];
        }

    }

    // RETOUR PANIER

    header("Location: commande.php");
    exit();

}

// VERIFICATION PANIER

$panier_vide = empty($_SESSION['panier']);

// TOTAL DU PANIER
// MENU(S), PRIX, PERSONNES

$menus_panier = [];
$total = 0;
$total_personnes = 0;

// RECUPERATION DES INFORMATIONS

if (!$panier_vide) {

    foreach ($_SESSION['panier'] as $item) {

        $stmt = $pdo->prepare("
            SELECT 
                image_principale,
                id,
                nom,
                prix,
                personnes_min,
                delai_commande
            FROM menus
            WHERE id = ?
        ");

        $stmt->execute([
            $item['menu_id']
        ]);


        $menu = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($menu) {

            // QTE MIN
            if ($item['quantite'] < $menu['personnes_min']) {
                $menu['quantite'] = $menu['personnes_min'];
            } else {
                $menu['quantite'] = $item['quantite'];
            }

            // NBR PERS.
            $menu['personnes'] = $menu['quantite'];

            // SOUS TOTAL
            $menu['sous_total'] = $menu['prix'] * $menu['quantite'];

            // TOTAUX
            $total += $menu['sous_total'];

            $total_personnes += $menu['quantite'];

            // AJOUT TABLEAU
            $menus_panier[] = $menu;
        }


    }

}

// CALCUL REMISE

$remise = 0;

foreach ($menus_panier as $menu) {

    if (
        $menu['quantite'] >= ($menu['personnes_min'] + 5)
    ) {

        $remise = $total * 0.10;
        break;

    }

}

?>

<!-- HEADER-->

<?php require_once 'includes/header_user.php'; ?>


<section class="s-d--small">

    <!-- MA COMMANDE-->

    <h1>MA COMMANDE</h1>
</section>


<section class="panier">

    <div class="panier-vide">
        <!-- SI PANIER VIDE-->
        <?php if ($panier_vide): ?>
            <p>Votre panier est vide.</p>

            <!-- REDIRECTION VERS PAGE MENUS-->
            <a href="menus.php" class="btn btn-red">NOS MENUS</a>
        <?php else: ?>
        </div>

        <!--CONTENU DU PANIER-->

        <?php foreach ($menus_panier as $menu): ?>
            <div class="ligne-panier">

                <img src="<?= htmlspecialchars($menu['image_principale']) ?>" alt="<?= htmlspecialchars($menu['nom']) ?>"
                    class="panier-image">

                <div class="panier-details">

                    <h2><?= htmlspecialchars($menu['nom']) ?></h2>

                    <p>PRIX UNITAIRE : <?= number_format($menu['prix'], 2, ',', ' ') ?> €/ personne</p>
                    <p>NOMBRE DE PERSONNES : <?= $menu['quantite'] ?></p>
                    <p>SOUS-TOTAL : <?= number_format($menu['sous_total'], 2, ',', ' ') ?> €</p>

                    <div class="quantite">

                        <a href="commande.php?action=moins&id=<?= $menu['id'] ?>">-</a>
                        <span><?= $menu['quantite'] ?></span>
                        <a href="commande.php?action=plus&id=<?= $menu['id'] ?>">+</a>

                    </div>


                    <a class="user-btn btn-create" href="commande.php?action=supprimer&id=<?= $menu['id'] ?>">
                        SUPPRIMER
                    </a>


                </div>


            </div>

        <?php endforeach; ?>
        <hr>

        <!-- PRIX TOTAL DU PANIER-->

        <p>SOUS-TOTAL : <?= number_format($total, 2, ',', ' ') ?> €</p>

        <?php if ($remise > 0): ?>
            <p>REMISE : - <?= number_format($remise, 2, ',', ' ') ?> €
            </p>
        <?php endif; ?>

        <h2>TOTAL : <?= number_format($total - $remise, 2, ',', ' ') ?> €</h2>
    <?php endif; ?>

</section>

<?php if (!$panier_vide): ?>

    <!-- REDIRECTION VERS FINALISATION COMMANDE-->

    <a href="commande_valid.php" class="user-btn btn-edit btn-finaliser">FINALISER MA COMMANDE</a>
<?php endif; ?>


<!-- FOOTER-->

<?php require_once 'includes/footer.php'; ?>

</body>

</html>