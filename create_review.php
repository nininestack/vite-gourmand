<?php
session_start();
require_once 'config/database/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: accueil.php");
    exit();
}


try {

    $stmt = $pdo->prepare("
        INSERT INTO reviews (
            users_id,
            orders_id,
            note,
            commentaire
        )
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $_SESSION['user_id'],
        $_POST['orders_id'],
        $_POST['note'],
        $_POST['commentaire']
    ]);


    header("Location: accueil.php?success=review_sent");
    exit();


} catch (PDOException $e) {

    die("Erreur : " . $e->getMessage());

}

