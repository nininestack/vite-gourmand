<?php

// DEMARER LA SESSION

session_start();

// CONNEXION A LA BASE DE DONNEES

require_once 'config/database/database.php';

// VERIFIER ENVOI DU FORMULAIRE

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // RECUPERER LES DONNEES DU FORMULAIRE

    $email = $_POST['email'];
    $password = $_POST['mot_de_passe'];


    // REQUETE SQL POUR VERIFIER LES IDENTIFIANTS   

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);


    // VERIFIER MOT DE PASSE

    if ($user && password_verify($password, $user['mot_de_passe'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['prenom'];


        // REDIRECTION SELON LE ROLE DE L'UTILISATEUR

        if ($user['role'] === 'admin') {
            // ADMIN
            header('Location: admin.php');
            exit();
        }

        if ($user['role'] === 'employe') {
            // EMPLOYEE
            header('Location: employe.php');
            exit();
        }

        if ($user['role'] === 'client') {
            // CLIENT
            header('Location: user.php');
            exit();
        }


        // SI NON ADMIN, EMPLOYEE OU CLIENT, REDIRECTION VERS ACCUEIL

        header('Location: accueil.php');
        exit();
    } else {
        $error_message = "Email ou mot de passe incorrect.";
    }
}
?>

<!-- HEADER-->

<?php require_once 'includes/header_login.php'; ?>


<section class="s-d--small">

    <h1>SE CONNECTER</h1>

    <!-- AFFICHER LE MESSAGE D'ERREUR SI LES IDENTIFIANTS SONT INCORRECTS -->

    <?php if (isset($error_message)): ?>
        <p style="color:red;"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <!-- AFFICHER MESSAGE DE SUCCES MDP REINITIALISE-->

    <?php if (isset($_GET['success']) && $_GET['success'] === 'password_reset'): ?>
        <p class="success-message">
            Votre mot de passe a été modifié. Vous pouvez maintenant vous connecter.
        </p>
    <?php endif; ?>


</section>

<section class="login">

    <form class="login-form" method="POST">

        <div class="input-group">
            <label for="email">Email :</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="input-group">
            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        </div>


        <button type="submit" class="btn btn-red">SE CONNECTER</button>


    </form>


    <div class="register-link">

        <div class="forgot-password">
            <a href="mot_de_passe_oublie.php">Mot de passe oublié ?</a>
        </div>

        <div class="small-text">Pas encore de compte?</div>
        <a href="register.php">S'inscrire</a>

    </div>

</section>

<section class="s-l-medium">

    <div class="s-l-medium-text">

        <h2>MES AVANTAGES</h2>
        <p>Suivre mes commandes et devis</p>
        <p>Factures & historique de commandes</p>
        <p>Coordonnées enregistrées pour tes prochains évènements</p>

    </div>

    <div class="s-l-medium-image">
        <img src="public/assets/img/web/web_connexion.png" alt="LOGIN">
    </div>

</section>



<!-- FOOTER-->

<?php require_once 'includes/footer.php'; ?>

</body>

</html>