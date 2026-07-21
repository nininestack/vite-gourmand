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

// VERIFIER ID AVIS

if (!isset($_POST['id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin.php#avis-clients");
    } else {
        header("Location: employe.php#avis-clients");
    }
    exit();
}

try {

    // MODIFIER STATUT AVIS

    $stmt = $pdo->prepare("
    UPDATE reviews
    SET statut = 'valide'
    WHERE id = ?
");

    $stmt->execute([$_POST['id']]);


    // MESSAGE DE SUCCES

    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin.php?success=review_validated#avis-clients");
    } else {
        header("Location: employe.php?success=review_validated#avis-clients");
    }
    exit();

    // AFFICHE ERREUR SI ECHEC

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
