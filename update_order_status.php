<?php

// SESSION ET SECURITE

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

if (!isset($_POST['order_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin.php#commandes");
    } else {
        header("Location: employe.php#commandes");
    }
    exit();
}

try {

    // METTRE A JOUR STATUT COMMANDE

    $stmt = $pdo->prepare("
        UPDATE orders
        SET statut = :status
        WHERE id = :id
    ");

    $stmt->execute([
        'status' => $_POST['status'],
        'id' => $_POST['order_id']
    ]);


    // MAIL LORSQUE LA COMMANDE EST ACCEPTEE

    if ($_POST['status'] === 'en_preparation') {

        $stmt = $pdo->prepare("
        SELECT
            users.email,
            users.prenom,
            orders.id
        FROM orders
        INNER JOIN users
        ON users.id = orders.users_id
        WHERE orders.id = ?
    ");

        $stmt->execute([$_POST['order_id']]);

        $client = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($client) {

            $subject = "Votre commande Vite & Gourmand a été acceptée";

            $message = "
Bonjour " . $client['prenom'] . ",

Bonne nouvelle !

Votre commande n°" . $client['id'] . " a été acceptée par notre équipe et est actuellement en préparation.

Nous vous informerons dès son départ en livraison.

Merci pour votre confiance.

L'équipe Vite & Gourmand
";

            $headers = "From: contact@viteetgourmand.fr\r\n";
            $headers .= "Reply-To: contact@viteetgourmand.fr\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            mail(
                $client['email'],
                $subject,
                $message,
                $headers
            );
        }
    }

    // MAIL LORSQUE LA COMMANDE EST TERMINEE

    if ($_POST['status'] === 'terminee') {

        $stmt = $pdo->prepare("
        SELECT
            users.email,
            users.prenom,
            orders.id
        FROM orders
        INNER JOIN users
        ON users.id = orders.users_id
        WHERE orders.id = ?
    ");

        $stmt->execute([$_POST['order_id']]);

        $client = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($client) {

            $subject = "Votre commande Vite & Gourmand a été livrée";

            $message = "
Bonjour " . $client['prenom'] . ",

Votre commande n°" . $client['id'] . " a bien été livrée.

Nous espérons que votre repas vous a pleinement satisfait.

Toute l'équipe Vite & Gourmand vous remercie pour votre confiance et espère vous retrouver très bientôt.

À bientôt,

L'équipe Vite & Gourmand
";

            $headers = "From: contact@viteetgourmand.fr\r\n";
            $headers .= "Reply-To: contact@viteetgourmand.fr\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            mail(
                $client['email'],
                $subject,
                $message,
                $headers
            );
        }
    }

    // MESSAGE DE SUCCES

    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin.php?success=order_updated#commandes");
    } else {
        header("Location: employe.php?success=order_updated#commandes");
    }
    exit();

    // AFFICHE ERREUR SI ECHEC

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}