<?php

// VERIFICATION DES CHAMPS

if (
    empty($_POST['name']) ||
    empty($_POST['email']) ||
    empty($_POST['message'])
) {
    header("Location: contact.php?error=1");
    exit();
}

// RECUPERATION DES DONNEES

$nom = trim($_POST['name']);
$entreprise = trim($_POST['company']);
$email = trim($_POST['email']);
$telephone = trim($_POST['phone']);
$message_client = trim($_POST['message']);

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

// REDIRECTION

header("Location: contact.php?success=1");
exit();

?>

<!-- HEADER-->

<?php require_once 'includes/header_contact.php'; ?>

<?php if (isset($_GET['success'])): ?>

    <p class="success-message">
        Votre message a bien été envoyé.
        Nous vous répondrons dans les plus brefs délais.
    </p>

<?php endif; ?>


<?php if (isset($_GET['error'])): ?>

    <p class="error-message">
        Veuillez remplir tous les champs obligatoires.
    </p>

<?php endif; ?>

<!-- FORMULAIRE DE CONTACT -->

<section class="contact">

    <h1>NOUS CONTACTER</h1>

    <h4>Un devis, une question, une demande particulière ? N'hésitez pas à nous contacter !</h4>

    <h2>FORMULAIRE DE CONTACT</h2>

    <form class="contact-form" action="submit_form.php" method="post">

        <div class="input-group">
            <label for="name">Nom :</label>
            <input type="text" id="name" name="name" required>
        </div>

        <div class="input-group">
            <label for="company">Entreprise : (optionnel)</label>
            <input type="text" id="company" name="company">
        </div>

        <div class="input-group">
            <label for="email">Email :</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="input-group">
            <label for="phone">Téléphone : (optionnel)</label>
            <input type="tel" id="phone" name="phone">
        </div>

        <div class="input-group">
            <label for="message">Message :</label>
            <textarea id="message" name="message" rows="5" required></textarea>
        </div>

        <button type="submit" class="btn btn-red">ENVOYER</button>

    </form>

</section>

<div class="accueil-image">
    <picture>
        <source media="(max-width: 768px)" srcset="public/assets/img/mob/mob_focaccia.png">
        <source media="(min-width: 769px)" srcset="public/assets/img/web/web_focaccia.png">
        <img src="public/assets/img/mob/mob_focaccia.png" alt="CONTACT">
    </picture>
</div>



<!--FOOTER-->

<?php require_once 'includes/footer.php'; ?>

</body>

</html>