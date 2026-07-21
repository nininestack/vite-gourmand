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
        <img src="public/assets/img/web/web_focaccia.png" alt="CONTACT">
</div>



<!--FOOTER-->

<?php require_once 'includes/footer.php'; ?>

</body>

</html>