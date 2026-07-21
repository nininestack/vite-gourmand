<?php

// DEMARER LA SESSION

session_start();

// CONNEXION A LA BASE DE DONNEES

require_once 'config/database/database.php';

// VERIFIER SI L'UTILISATEUR EST CONNECTE ET EST ADMIN

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin.php#employes");
    exit();
}

// VERIFIER LES CHAMPS DU FORMULAIRE

try {

    if (empty($_POST['employee_id']) || empty($_POST['user_id'])) {
        header("Location: admin.php?error=missing_id#employes");
        exit();
    }

    $employee_id = $_POST['employee_id'];
    $user_id = $_POST['user_id'];

    $pdo->beginTransaction();

    // SUPPRIMER L'EMPLOYE DE LA TABLE EMPLOYEES

    $stmt = $pdo->prepare("
        DELETE FROM employees
        WHERE id = ?
    ");
    $stmt->execute([$employee_id]);

    // SUPPRIMER L'UTILISATEUR DE LA TABLE USERS

    $stmt = $pdo->prepare("
        DELETE FROM users
        WHERE id = ?
    ");
    $stmt->execute([$user_id]);

    $pdo->commit();

    // REDIRECTION VERS LA PAGE ADMIN AVEC UN MESSAGE DE SUCCES

    header("Location: admin.php?success=employee_deleted#employes");
    exit();

    // GESTION DES ERREURS

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die("Erreur delete employee : " . $e->getMessage());
}