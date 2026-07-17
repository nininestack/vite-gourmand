<?php
session_start();
require_once 'database/database.php';

// ACCES ADMIN & EMPLOYE 

if (
    !isset($_SESSION['user_role']) ||
    ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'employe')
) {
    header("Location: login.php");
    exit();
}

// VERIFIER ENVOI FORMULAIRE

if (!isset($_GET['id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin.php#menus");
    } else {
        header("Location: employe.php#menus");
    }
    exit();
}

try {

    // VERIFIER CHAMPS OBLIGATOIRES
    if (
        empty($_POST['nom']) ||
        empty($_POST['prix']) ||
        empty($_POST['personnes_min']) ||
        empty($_POST['description']) ||
        empty($_POST['themes_id']) ||
        empty($_POST['diets_id'])
    ) {
        header("Location: admin.php?error=missing_fields#menus");
        exit();
    }

    // SECURISER VALEURS

    $nom = trim($_POST['nom']);
    $prix = (float) $_POST['prix'];
    $personnes_min = (int) $_POST['personnes_min'];
    $description = trim($_POST['description']);
    $themes_id = (int) $_POST['themes_id'];
    $diets_id = (int) $_POST['diets_id'];

    $stmt = $pdo->prepare("
        INSERT INTO menus (
            nom,
            prix,
            personnes_min,
            description,
            themes_id,
            diets_id
        )
        VALUES (
            :nom,
            :prix,
            :personnes_min,
            :description,
            :themes_id,
            :diets_id
        )
    ");

    $stmt->execute([
        'nom' => $nom,
        'prix' => $prix,
        'personnes_min' => $personnes_min,
        'description' => $description,
        'themes_id' => $themes_id,
        'diets_id' => $diets_id
    ]);

    $menu_id = $pdo->lastInsertId();


    $stmt = $pdo->prepare("
INSERT INTO stocks (
    menus_id,
    stock,
    disponible
)
VALUES (
    :menus_id,
    0,
    0
)
");

    $stmt->execute([
        'menus_id' => $menu_id
    ]);

    // MESSAGE DE SUCCES

    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin.php?success=menu_created#menus");
    } else {
        header("Location: employe.php?success=menu_created#menus");
    }
    exit();

    // AFFICHE ERREUR SI ECHEC

} catch (Exception $e) {
    error_log($e->getMessage());
    header("Location: admin.php?error=server_error#menus");
    exit();
}