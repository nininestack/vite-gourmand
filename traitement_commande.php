<?php

// CONNECTION A LA BASE DE DONNEES

session_start();

require_once 'database/database.php';

// CONNECTION AUX HORAIRES

require_once 'horaires.php';

// CONNECTION AUX FRAIS DE LIVRAISON

require_once 'livraison.php';

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


// RECUPERATION FORMULAIRE


$date_livraison = $_POST['date_livraison'] ?? null;
$heure_livraison = $_POST['heure_livraison'] ?? null;
$adresse_livraison = $_POST['adresse_livraison'] ?? null;
$commentaire = $_POST['commentaire'] ?? null;

if (!$date_livraison || !$heure_livraison || !$adresse_livraison) {
    header("Location: commande_valid.php");
    exit();
}

// CREATION DATETIME LIVRAISON

$date_complete =
    $date_livraison . ' ' . $heure_livraison;

if ($date_livraison < date('Y-m-d')) {

    header("Location: commande_valid.php");
    exit();

}




// CONVERSION DATE PHP

$jour = date(
    'N',
    strtotime($date_livraison)
);

// VERIFICATION JOUR FERME

if (in_array($jour, $jours_fermeture)) {

    header("Location: commande_valid.php");
    exit();

}

// VERIFICATION HORAIRES DISPONIBLES

if (
    !isset($horaires[$jour]) ||
    !in_array($heure_livraison, $horaires[$jour])
) {

    header("Location: commande_valid.php");
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


// RECALCUL PANIER

$total = 0;
$remise = 0;
$menus_commande = [];

$menu_ids = [];
$quantites_panier = [];


foreach ($_SESSION['panier'] as $item) {

    $menu_ids[] = $item['menu_id'];

    $quantites_panier[$item['menu_id']] =
        $item['quantite'];

}

if (empty($menu_ids)) {

    header("Location: commande.php");
    exit();

}


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

foreach ($menus_bdd as $menu) {


    $menu['quantite'] =
        $quantites_panier[$menu['id']];


    // SECURITE QTE MINIMUM

    if ($menu['quantite'] < $menu['personnes_min']) {

        $menu['quantite'] =
            $menu['personnes_min'];
    }

    $menu['total'] =
        $menu['prix'] *
        $menu['quantite'];

    $total += $menu['total'];

    $menus_commande[] = $menu;
}


// VERIFICATION DELAI DE PREPARATION

$delai_max = 0;

foreach ($menus_commande as $menu) {
    if ($menu['delai_commande'] > $delai_max) {
        $delai_max = $menu['delai_commande'];
    }
}

// CALCUL DATE MINIMUM AUTORISEE

$date_min_livraison = date(
    'Y-m-d',
    strtotime("+" . $delai_max . " days")
);


// VERIFICATION DATE CHOISIE PAR LE CLIENT

if ($date_livraison < $date_min_livraison) {
    header("Location: commande_valid.php");
    exit();
}


// VERIFICATION REMISE

foreach ($menus_commande as $menu) {

    if (
        $menu['quantite']
        >=
        ($menu['personnes_min'] + 5)
    ) {
        $remise = $total * 0.10;
        break;
    }
}

// CALCUL FRAIS LIVRAISON API

$resultat_livraison =
    calculFraisLivraison($adresse_livraison);


if ($resultat_livraison === null) {

    header("Location: commande_valid.php");
    exit();

}


$frais_livraison =
    $resultat_livraison['frais'];

// TOTAL FINAL

$total_final =
    $total
    -
    $remise
    +
    $frais_livraison;



try {

    $pdo->beginTransaction();


    // CREATION COMMANDE

    $stmt = $pdo->prepare("

    INSERT INTO orders

    (
    users_id,
    statut,
    total,
    date_commande,
    date_livraison,
    adresse_livraison,
    commentaire,
    frais_livraison,
    remise

    )

    VALUES

    (
    ?,
    'en_attente',
    ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    ?

    )

    ");



    $stmt->execute([
        $user_id,
        $total_final,
        date('Y-m-d H:i:s'),
        $date_complete,
        $adresse_livraison,
        $commentaire,
        $frais_livraison,
        $remise
    ]);


    $order_id = $pdo->lastInsertId();



    // INSERTION DETAILS COMMANDES

    $stmt_items = $pdo->prepare("

    INSERT INTO oders_items

    (
    orders_id,
    menus_id,
    quantite,
    prix_unitaire
    )

    VALUES

    (
    ?,
    ?,
    ?,
    ?

    )

    ");

    foreach ($menus_commande as $menu) {

        $stmt_items->execute([

            $order_id,
            $menu['id'],
            $menu['quantite'],
            $menu['prix']

        ]);

    }

    $pdo->commit();

} catch (Exception $e) {

    $pdo->rollBack();

    die(
        "Erreur commande : " . $e->getMessage()
    );

}

// MAIL CONFIRMATION CLIENT

$email_client = $user['email'];
$subject = "Confirmation de votre commande Vite & Gourmand";
$message = "
Bonjour " . $user['prenom'] . ",

Nous avons bien reçu votre commande.
Numéro de commande : " . $order_id . "
Date de livraison :
" . $date_livraison . "
Adresse de livraison :
" . $adresse_livraison . "
Résumé de votre commande :

";

foreach ($menus_commande as $menu) {

    $message .= "
" . $menu['nom'] . "
Quantité : " . $menu['quantite'] . "
Prix : " . $menu['total'] . " €

";

}

$message .= "

Sous-total : " . $total . "
Remise : " . $remise . "
Livraison : " . $frais_livraison . "
Total payé : " . $total_final . " €

Merci pour votre confiance.
L'équipe Vite & Gourmand
";

$headers = "From: contact@viteetgourmand.fr\r\n";
$headers .= "Reply-To: contact@viteetgourmand.fr\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

mail(
    $email_client,
    $subject,
    $message,
    $headers
);

// VIDER PANIER

unset($_SESSION['panier']);


// REDIRECTION CONFIRMATION

header("Location: confirmation.php?id=" . $order_id);

exit();

?>