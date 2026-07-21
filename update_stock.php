<?php

// SESSION ET SECURITE

session_start();

require_once 'config/database/database.php';

// ACCES ADMIN & EMPLOYE 

if (
    !isset($_SESSION['user_role']) ||
    ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'employe')
) {
    header("Location: login.php");
    exit();
}

// VERIFIER ENVOI FORMULAIRE

if (!isset($_POST['id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin.php#menus");
    } else {
        header("Location: employe.php#menus");
    }
    exit();
}
// RECUPERER LA NOUVELLE QTE ENVOYE PAR LE FORMULAIRE

try {
    $stock = (int) $_POST['stock'];

    // REQUETE SQL POUR MODIFIER STOCK MENU CONCERNE
    //SI STOCK > 0 = DISPONIBLE
    //SI STOCK < 0 = RUPTURE

    $stmt = $pdo->prepare("
        UPDATE stocks
        SET
            stock = :stock,
            disponible = :disponible
        WHERE id = :id
    ");

    // EXECUTION REQUETE AVEC NOUVELLES VALEURS
    //REMPLACE PAR NVELLES QTE
    //CALCUL AUTOMATIQUE DU STATUT 
    //IDENTIFIANT MODIFIE

    $stmt->execute([
        'stock' => $stock,
        'disponible' => $stock > 0 ? 1 : 0,
        'id' => $_POST['id']
    ]);

    // MESSAGE DE SUCCES

    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin.php?success=stock_updated#stocks");
    } else {
        header("Location: employe.php?success=stock_updated#stocks");
    }
    exit();

    // AFFICHE ERREUR SI ECHEC

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
