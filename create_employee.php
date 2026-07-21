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

    if (
        empty($_POST['nom']) ||
        empty($_POST['prenom']) ||
        empty($_POST['email']) ||
        empty($_POST['telephone']) ||
        empty($_POST['adresse'])
    ) {
        header("Location: admin.php?error=missing_fields#employes");
        exit();
    }

    // VERIFIER SI L'EMAIL EXISTE DEJA

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$_POST['email']]);

    if ($stmt->fetch()) {
        header("Location: admin.php?error=email_exists#employes");
        exit();
    }

    $pdo->beginTransaction();

    // RECUPERER LE MOT DE PASSE

    $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);

    // INSERER DANS LA TABLE USERS

    $stmt = $pdo->prepare("
        INSERT INTO users (nom, prenom, email, mot_de_passe, telephone, adresse, role, mot_de_passe_modifie)
        VALUES (:nom, :prenom, :email, :mot_de_passe, :telephone, :adresse, :role, :mot_de_passe_modifie)
    ");

    $stmt->execute([
        'nom' => $_POST['nom'],
        'prenom' => $_POST['prenom'],
        'email' => $_POST['email'],
        'mot_de_passe' => $mot_de_passe,
        'telephone' => $_POST['telephone'],
        'adresse' => $_POST['adresse'],
        'role' => 'employe',
        'mot_de_passe_modifie' => 0
    ]);

    $user_id = $pdo->lastInsertId();

    // INSERER DANS LA TABLE EMPLOYEES

    $stmt = $pdo->prepare("
        INSERT INTO employees (
            users_id,
            date_naissance,
            ville_naissance,
            type_contrat,
            date_embauche,
            salaire_heure,
            heures_semaine
        )
        VALUES (
            :users_id,
            :date_naissance,
            :ville_naissance,
            :type_contrat,
            :date_embauche,
            :salaire_heure,
            :heures_semaine
        )
    ");

    $stmt->execute([
        'users_id' => $user_id,
        'date_naissance' => $_POST['date_naissance'],
        'ville_naissance' => $_POST['ville_naissance'],
        'type_contrat' => $_POST['type_contrat'],
        'date_embauche' => $_POST['date_embauche'],
        'salaire_heure' => $_POST['salaire_heure'],
        'heures_semaine' => $_POST['heures_semaine']
    ]);

    $pdo->commit();

    // MAIL INFORMATION EMPLOYE

    $email_employe = $_POST['email'];

    $subject = "Création de votre compte employé Vite & Gourmand";

    $message = "
Bonjour " . $_POST['prenom'] . ",

Nous vous informons que votre compte employé Vite & Gourmand a été créé.

Votre adresse e-mail est désormais enregistrée dans notre système.

Vos informations de connexion vous seront communiquées séparément par l'administrateur.

Bienvenue au sein de Vite & Gourmand !

L'équipe Vite & Gourmand
";

    $headers = "From: contact@viteetgourmand.fr\r\n";
    $headers .= "Reply-To: contact@viteetgourmand.fr\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    mail(
        $email_employe,
        $subject,
        $message,
        $headers
    );


    // REDIRECTION VERS LA PAGE ADMIN AVEC UN MESSAGE DE SUCCES

    header("Location: admin.php?success=employee_created#employes");
    exit();

    // GESTION DES ERREURS

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die("Erreur create employee : " . $e->getMessage());
}
