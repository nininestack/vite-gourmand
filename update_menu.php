<?php

// SESSION ET SECURITE

session_start();

require_once "database/database.php";

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

// VERIFIER CHAMPS OBLIGATOIRES
if (
    empty($_POST['id']) ||
    empty($_POST['nom']) ||
    empty($_POST['prix']) ||
    empty($_POST['personnes_min']) ||
    empty($_POST['description']) ||
    empty($_POST['themes_id']) ||
    empty($_POST['diets_id'])
) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin.php?error=missing_fields#menus");
    } else {
        header("Location: employe.php?error=missing_fields#menus");
    }

    exit();
}

try {

    $stmt = $pdo->prepare("
        UPDATE menus
        SET
            nom = :nom,
            prix = :prix,
            personnes_min = :personnes_min,
            description = :description,
            themes_id = :themes_id,
            diets_id = :diets_id
        WHERE id = :id
    ");

    $stmt->execute([
        'nom' => $_POST['nom'],
        'prix' => $_POST['prix'],
        'personnes_min' => $_POST['personnes_min'],
        'description' => $_POST['description'],
        'themes_id' => $_POST['themes_id'],
        'diets_id' => $_POST['diets_id'],
        'id' => $_POST['id']
    ]);

    // MESSAGE DE SUCCES 

    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin.php?success=menu_updated#menus");
    } else {
        header("Location: employe.php?success=menu_updated#menus");
    }
    exit();

    // AFFICHE ERREUR SI ECHEC

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

