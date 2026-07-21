<?php

// DEMARRER LA SESSION

session_start();

// CONNEXION BDD

require_once 'config/database/database.php';

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

// MAIL ENTREPRISE

$email_entreprise = "contact@viteetgourmand.fr";

$subject = "Nouveau message depuis le formulaire de contact";

$message = "
Vous avez reçu un nouveau message via le site Vite & Gourmand.

Nom :
$nom

Entreprise :
" . ($entreprise ?: "Non renseignée") . "

Email :
$email

Téléphone :
" . ($telephone ?: "Non renseigné") . "

Message :

$message_client
";

$headers = "From: contact@viteetgourmand.fr\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

mail(
    $email_entreprise,
    $subject,
    $message,
    $headers
);


header("Location: contact.php?success=1");
exit();