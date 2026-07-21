<?php

// SESSION ET SECURITE

session_start();

// CONNEXION A LA BASE DE DONNEES

require_once 'config/database/database.php';

// VERIFIER SI L'UTILISATEUR EST CONNECTE ET EST ADMIN

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
$stmt->execute([$_POST['contact_id']]);

header("Location: admin.php#contacts");
exit();