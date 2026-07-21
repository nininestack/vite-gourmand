<?php

// DEMARRER LA SESSION

session_start();


// CONNEXION BDD

require_once 'config/database/database.php';


// VERIFIER QUE L'UTILISATEUR EST CONNECTE

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


// VERIFIER ENVOI DU FORMULAIRE

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: user.php");
    exit();
}


// RECUPERER LES DONNEES

$nom = $_POST['name'];
$prenom = $_POST['surname'];
$email = $_POST['email'];
$telephone = $_POST['phone'];
$adresse = $_POST['adress'];


// UPDATE UTILISATEUR

$stmt = $pdo->prepare("
    UPDATE users
    SET 
        nom = :nom,
        prenom = :prenom,
        email = :email,
        telephone = :telephone,
        adresse = :adresse

    WHERE id = :id
");


$stmt->execute([

    'nom' => $nom,
    'prenom' => $prenom,
    'email' => $email,
    'telephone' => $telephone,
    'adresse' => $adresse,
    'id' => $_SESSION['user_id']

]);


// METTRE A JOUR LA SESSION 

$_SESSION['user_email'] = $email;
$_SESSION['user_name'] = $prenom;


// RETOUR PAGE PROFIL

header("Location: user.php?success=updated");
exit();

?>