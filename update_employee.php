<?php

// SESSION ET SECURITE

session_start();

require_once 'database/database.php';

// ACCES ADMIN & EMPLOYE 

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin.php#employes");
    exit();
}

try {

    if (
        empty($_POST['id']) ||
        empty($_POST['nom']) ||
        empty($_POST['prenom']) ||
        empty($_POST['email'])
    ) {
        header("Location: admin.php?error=missing_fields#employes");
        exit();
    }
    $pdo->beginTransaction();

    // RECUPERER USERS_ID
    $stmt = $pdo->prepare("SELECT users_id FROM employees WHERE id = ?");
    $stmt->execute([$_POST['id']]);
    $users_id = $stmt->fetchColumn();

    if (!$users_id) {
        throw new Exception("Employé introuvable");
    }

    // UPDATE USERS
    $stmt = $pdo->prepare("
        UPDATE users
        SET nom = ?, prenom = ?, email = ?, telephone = ?, adresse = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $_POST['nom'],
        $_POST['prenom'],
        $_POST['email'],
        $_POST['telephone'],
        $_POST['adresse'],
        $users_id
    ]);

    // UPDATE EMPLOYEES
    $stmt = $pdo->prepare("
        UPDATE employees
        SET date_naissance = ?,
            ville_naissance = ?,
            type_contrat = ?,
            date_embauche = ?,
            salaire_heure = ?,
            heures_semaine = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $_POST['date_naissance'],
        $_POST['ville_naissance'],
        $_POST['type_contrat'],
        $_POST['date_embauche'],
        $_POST['salaire_heure'],
        $_POST['heures_semaine'],
        $_POST['id']
    ]);

    $pdo->commit();

    header("Location: admin.php?success=updated#employes");
    ;
    exit();

    // AFFICHE ERREUR SI ECHEC

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die("Erreur update employee : " . $e->getMessage());
}