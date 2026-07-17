<?php
session_start();

// Vide toutes les variables de session
$_SESSION = [];

// Détruit la session
session_destroy();

// Supprime le cookie de session si nécessaire
if (ini_get("session.use_cookies")) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Retour à l'accueil
header("Location: accueil.php");
exit();