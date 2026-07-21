<?php

// CONNECTION A LA BASE DE DONNEES

session_start();
require_once 'config/database/database.php';

// CONNECTION AUX HORAIRES

require_once 'horaires.php';
require_once 'config.php';

// VERIFICATION CLIENT CONNECTE

if (
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'client'
) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// VERIFICATION PANIER

if (
    !isset($_SESSION['panier']) ||
    empty($_SESSION['panier'])
) {

    header("Location: commande.php");
    exit();

}

// RECUPERATION CLIENT

$stmt = $pdo->prepare("
    SELECT *
    FROM users
    WHERE id = ?
");

$stmt->execute([
    $user_id
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {

    header("Location: login.php");
    exit();
}

// RECUPERATION MENUS PANIER

$menus_panier = [];
$total = 0;
$total_personnes = 0;
$delai_max = 0;

// RECUPERATION DES IDS DU PANIER

$menu_ids = [];
$quantites_panier = [];

foreach ($_SESSION['panier'] as $item) {
    $menu_ids[] = $item['menu_id'];
    $quantites_panier[$item['menu_id']] = $item['quantite'];
}

// SECURITE : VERIFICATION IDS MENUS

if (empty($menu_ids)) {
    header("Location: commande.php");
    exit();
}

// REQUETE UNIQUE MENUS

$placeholders = implode(
    ',',
    array_fill(
        0,
        count($menu_ids),
        '?'
    )
);

$stmt = $pdo->prepare("

    SELECT 
        id,
        nom,
        prix,
        personnes_min,
        delai_commande

    FROM menus

    WHERE id IN ($placeholders)

");

$stmt->execute($menu_ids);
$menus_bdd = $stmt->fetchAll(PDO::FETCH_ASSOC);

// AJOUT DES INFOS PANIER

foreach ($menus_bdd as $menu) {

    // RECUPERATION QTE CHOISIES
    $menu['quantite'] =
        $quantites_panier[$menu['id']];

    // VERIFICATION QUANTITE MINIMUM
    if ($menu['quantite'] < $menu['personnes_min']) {
        $menu['quantite'] = $menu['personnes_min'];
    }

    // NOMBRE DE PERSONNES
    $menu['personnes'] = $menu['quantite'];
    $total_personnes += $menu['quantite'];

    // CALCUL SOUS TOTAL
    $menu['sous_total'] =
        $menu['prix'] * $menu['quantite'];

    // TOTAL COMMANDE
    $total += $menu['sous_total'];


    // DELAI MAX MENU
    if ($menu['delai_commande'] > $delai_max) {
        $delai_max = $menu['delai_commande'];
    }

    // AJT PANIER FINAL
    $menus_panier[] = $menu;
}

// DELAI MIN MENU

$date_min_livraison = date(
    'Y-m-d',
    strtotime("+" . $delai_max . " days")
);

// VERIFICATION DATE DISPONIBLE

function dateDisponible($date, $jours_fermeture)
{

    $jour = date(
        'N',
        strtotime($date)
    );

    return !in_array(
        $jour,
        $jours_fermeture
    );
}

// RECHERCHE PROCHAIN JOUR OUVERT


while (!dateDisponible($date_min_livraison, $jours_fermeture)) {
    $date_min_livraison = date(
        'Y-m-d',
        strtotime("+1 day", strtotime($date_min_livraison))
    );
}

// REMISE 10% SI UN MENU DEPASSE LE MINIMUM DE 5 PERSONNES

$remise = 0;
foreach ($menus_panier as $menu) {
    if (
        $menu['quantite'] >= ($menu['personnes_min'] + 5)
    ) {
        $remise = $total * 0.10;
        break;
    }
}

// VALEURS PAR DEFAUT LIVRAISON

$frais_livraison = 0;
$distance_livraison = null;

// TOTAL FINAL

$total_final =
    $total
    -
    $remise
    +
    $frais_livraison;

?>

<!-- HEADER-->

<?php require_once 'includes/header_user.php'; ?>


<section class="s-d--small">
    <!-- INFOS CLIENT-->

    <section class="client-info">

        <h2>MES INFORMATIONS</h2>

        <div class="info-card">
            <p><strong>NOM :</strong> <?= htmlspecialchars($user['nom']) ?></p>
            <p><strong>PRÉNOM :</strong> <?= htmlspecialchars($user['prenom']) ?></p>
            <p><strong>TÉLÉPHONE :</strong> <?= htmlspecialchars($user['telephone']) ?></p>
            <p><strong>ADRESSE DE FACTURATION :</strong> <?= htmlspecialchars($user['adresse']) ?></p>
        </div>

    </section>

    <!-- RECAPITULATIF-->

    <section class="validation-commande">

        <h2>RÉCAPITULATIF</h2>

        <div class="commande-card">

            <form method="POST" action="commande_valid.php">

                <!-- DATE PRISE DE COMMANDE-->
                <p>DATE DE COMMANDE : <?= date('d/m/Y H:i') ?></p>

                <!-- DATE DE LIVRAISON-->
                <label>DATE DE LIVRAISON :</label>
                <input type="date" id="date_livraison" name="date_livraison" min="<?= $date_min_livraison ?>" required>
                <!-- HEURE DE LIVRAISON-->
                <label>HEURE DE LIVRAISON : </label>

                <select name="heure_livraison" id="heure_livraison" required>

                    <option value="">Choisir une heure</option>

                </select>

                <!-- ADRESSE DE LIVRAISON-->
                <label for="adresse_livraison">ADRESSE DE LIVRAISON</label>

                <input type="text" id="adresse_livraison" name="adresse_livraison"
                    value="<?= htmlspecialchars($_POST['adresse_livraison'] ?? '') ?>"
                    placeholder="Commencez à taper votre adresse" autocomplete="off" required>

                <div id="suggestions"></div>

                <!-- COMMENTAIRE-->
                <label>COMMENTAIRE : </label>
                <textarea name="commentaire"></textarea>

                <!-- RESUME CONTENU-->

                <h3>MA COMMANDE</h3>

                <?php foreach ($menus_panier as $menu): ?>
                    <div>
                        <h4><?= htmlspecialchars($menu['nom']) ?></h4>
                        <p>NOMBRE DE PERSONNES : <?= $menu['quantite'] ?></p>
                        <p>PRIX UNITAIRE : <?= number_format($menu['prix'], 2, ',', ' ') ?> € / personne</p>
                        <p>TOTAL : <?= number_format($menu['sous_total'], 2, ',', ' ') ?> €</p>
                    </div>
                <?php endforeach; ?>

                <!--RESUME PRIX-->
                <p>SOUS-TOTAL : <?= number_format($total, 2, ',', ' ') ?> €</p>
                <p>DISTANCE DE LIVRAISON : <span id="distance_livraison"> - </span></p>
                <p>FRAIS DE LIVRAISON : <span id="frais_livraison"> 0.00 €</span></p>
                <p>REMISE : <span id="prix_remise"><?= number_format($remise, 2, ',', ' ') ?></span> €</p>

                <div id="total_base" data-total="<?= $total ?>"></div>
                <div id="total_remise" data-remise="<?= $remise ?>"></div>

                <h3>TOTAL : <span id="total_final">
                        <?= number_format($total - $remise, 2, ',', ' ') ?> €
                    </span>
                </h3>

                <!-- VALIDER, VERIFIER ET TRAITER COMMANDE-->

                <button type="submit" name="valider_commande" formaction="traitement_commande.php"
                    class="user-btn btn-edit btn-finaliser">
                    VALIDER MA COMMANDE
                </button>

            </form>

        </div>

    </section>




    <!--JS HORAIRES-->

    <script>
        const horaires = <?= json_encode($horaires); ?>;
        const joursFermes = <?= json_encode($jours_fermeture); ?>;
    </script>


    <script src="public/assets/js/horaires.js"></script>

    <script>
        const totalCommande = <?= $total ?>;
        const remiseCommande = <?= $remise ?>;
    </script>

    <script src="public/assets/js/livraison.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= GOOGLE_API_KEY ?>&libraries=places"></script>
    <script src="public/assets/js/autocomplete.js"></script>



    <!-- FOOTER-->

    <?php require_once 'includes/footer.php'; ?>

    </body>

    </html>