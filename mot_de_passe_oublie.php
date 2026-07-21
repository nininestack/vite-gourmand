<?php

// DEMARRER LA SESSION

session_start();

// CONNEXION BDD

require_once 'config/database/database.php';


// TRAITEMENT FORMULAIRE

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);

    // RECHERCHE DU COMPTE

    $stmt = $pdo->prepare("
SELECT id, prenom
FROM users
WHERE email = ?
");

    $stmt->execute([$email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // SI LE COMPTE EXISTE

    if ($user) {

        // GENERER TOKEN UNIQUE
        $token = bin2hex(random_bytes(32));

        // DEFINIR DATE EXP.
        $expiration = date(
            'Y-m-d H:i:s',
            strtotime('+1 hour')
        );

        // ENREGISTREMENT DU TOKEN 

        $stmt = $pdo->prepare("
DELETE FROM password_resets
WHERE user_id = ?
");

        $stmt->execute([$user['id']]);


        $stmt = $pdo->prepare("
INSERT INTO password_resets
(
    user_id,
    token,
    expired_at
)
VALUES
(
    ?, ?, ?
)
");

        $stmt->execute([
            $user['id'],
            $token,
            $expiration
        ]);

    }

    // LIEN DE REINITIALISATION

    $lien = "https://vite-gourmand.oo.gd/reset_password.php?token=" . $token;


    // MAIL REINITIALISATION MOT DE PASSE

    $email_client = $email;

    $subject = "Réinitialisation de votre mot de passe";

    $message = "
Bonjour " . $user['prenom'] . ",

Vous avez demandé la réinitialisation de votre mot de passe.

Cliquez sur le lien ci-dessous :

" . $lien . "

Ce lien est valable pendant 1 heure.

Si vous n'êtes pas à l'origine de cette demande, ignorez simplement cet e-mail.

L'équipe Vite & Gourmand
";

    $headers = "From: contact@viteetgourmand.fr\r\n";
    $headers .= "Reply-To: contact@viteetgourmand.fr\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    mail(
        $email_client,
        $subject,
        $message,
        $headers
    );

    $message = "Si cette adresse existe, un e-mail de réinitialisation a été envoyé.";



}

?>

<!-- HEADER-->

<?php require_once 'includes/header_mdp.php'; ?>

<section class="s-d--small">

    <!-- MOT DE PASSE OUBLIE -->

    <h1>MOT DE PASSE OUBLIÉ</h1>

</section>

<!-- MESSAGE DE SUCCES-->

<?php if (isset($message)): ?>

    <p class="success-message">
        <?= htmlspecialchars($message) ?>
    </p>

<?php endif; ?>

<div class="password-container">

    <form method="POST" class="user-form">

        <label>Adresse e-mail</label>

        <input type="email" name="email" required>

        <button class="user-btn btn-edit">ENVOYER LE LIEN</button>

    </form>

</div>

<!-- FOOTER-->

<?php require_once 'includes/footer.php'; ?>

</body>

</html>