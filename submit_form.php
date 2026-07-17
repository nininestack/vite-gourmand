<?php

// DEMARRER LA SESSION

session_start();

// CONNEXION BDD

require_once 'database/database.php';

// VERIFIER LA METHODE

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: contact.php");
    exit();
}

// RECUPERATION DES DONNEES

$nom = trim($_POST['name']);
$entreprise = trim($_POST['company']);
$email = trim($_POST['email']);
$telephone = trim($_POST['phone']);
$message = trim($_POST['message']);

// VERIFICATION

if (
    empty($nom) ||
    empty($email) ||
    empty($message)
) {
    header("Location: contact.php?error=missing_fields");
    exit();
}

// INSERTION

$stmt = $pdo->prepare("
INSERT INTO contact_messages
(
    nom,
    entreprise,
    email,
    telephone,
    message
)

VALUES
(
    :nom,
    :entreprise,
    :email,
    :telephone,
    :message
)
");

$stmt->execute([

    'nom' => $nom,
    'entreprise' => $entreprise,
    'email' => $email,
    'telephone' => $telephone,
    'message' => $message

]);

header("Location: contact.php?success=1");
exit();