<?php

// DEMARRER LA SESSION

session_start();


// CONNEXION BASE DE DONNEES

require_once 'config/database/database.php';


// VERIFIER ENVOI FORMULAIRE

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    // RECUPERER DONNEES

    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $adresse = $_POST['adresse'];
    $password = $_POST['mot_de_passe'];
    $confirmation = $_POST['confirmation'];



    // VERIFIER MOTS DE PASSE

    if ($password !== $confirmation) {

        $error_message = "Les mots de passe ne correspondent pas.";

    } else {



        // VERIFIER SI EMAIL EXISTE DEJA

        $stmt = $pdo->prepare("
        SELECT id 
        FROM users 
        WHERE email = ?
        ");

        $stmt->execute([$email]);

        $existingUser = $stmt->fetch();



        if ($existingUser) {

            $error_message = "Cette adresse email existe déjà.";

        } else {



            // HASH MOT DE PASSE

            $hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );



            // INSERTION UTILISATEUR

            $stmt = $pdo->prepare("
            INSERT INTO users 
            (
                nom,
                prenom,
                email,
                telephone,
                adresse,
                mot_de_passe,
                role
            )
            VALUES
            (?, ?, ?, ?, ?, ?, 'client')
            ");



            $stmt->execute([
                $nom,
                $prenom,
                $email,
                $telephone,
                $adresse,
                $hash
            ]);



            // RECUPERER L'ID DU NOUVEL UTILISATEUR

            $user_id = $pdo->lastInsertId();


            // CREER LA SESSION

            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'client';
            $_SESSION['user_name'] = $prenom;


            // REDIRECTION ESPACE CLIENT

            header("Location: user.php?success=register");
            exit();

        }

    }

}

?>

<!-- HEADER-->

<?php require_once 'includes/header_login.php'; ?>


<section class="s-d--small">
    <h1>S'INSCRIRE</h1>

    <!-- AFFICHER LE MESSAGE D'ERREUR SI LES IDENTIFIANTS SONT INCORRECTS -->

    <?php if (isset($error_message)): ?>
        <p style="color:red;"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

</section>

<section class="login">

    <!-- FORMULAIRE D INSCRIPTION-->

    <form class="login-form" method="POST">

        <div class="input-group">
            <label for="name">Nom :</label>
            <input type="text" id="nom" name="nom" required>
        </div>

        <div class="input-group">
            <label for="surname">Prénom :</label>
            <input type="text" id="prenom" name="prenom" required>
        </div>

        <div class="input-group">
            <label for="email">Email :</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="input-group">
            <label for="phone">Téléphone :</label>
            <input type="tel" id="telephone" name="telephone" required>
        </div>

        <div class="input-group">
            <label for="adress">Adresse :</label>
            <input type="text" id="adresse" name="adresse" required>
        </div>

        <div class="input-group">
            <label for="password">Mot de passe :</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        </div>

        <div class="input-group">
            <label for="confirmPassword">Confirmer le mot de passe :</label>
            <input type="password" id="confirmation" name="confirmation" required>
        </div>

        <button type="submit" class="btn btn-red">S'INSCRIRE</button>

    </form>

    <div class="register-link">

        <div class="small-text">Déjà un compte?</div>
        <a href="login.php">Se connecter</a>

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