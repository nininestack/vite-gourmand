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

try {

    $stmt = $pdo->prepare("
    UPDATE stocks
    SET
        stock = 0,
        disponible = 0
    WHERE id = ?
    ");

    $stmt->execute([$_POST['id']]);


// MESSAGE DE SUCCES

if ($_SESSION['user_role'] === 'admin') {
    header("Location: admin.php?success=stock_deleted#stocks");
} else {
    header("Location: employe.php?success=stock_deleted#stocks");
}
exit();

// AFFICHE ERREUR SI ECHEC 

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}