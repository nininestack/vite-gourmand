<?php

// DEMARRER SESSION

session_start();


// CONNEXION BDD

require_once 'config/database/database.php';


$error = null;


// RECUPERATION TOKEN

$token = $_GET['token'] ?? '';


// VERIFICATION TOKEN

$stmt = $pdo->prepare("
SELECT *
FROM password_resets
WHERE token = ?
AND expired_at > NOW()
");

$stmt->execute([$token]);

$reset = $stmt->fetch(PDO::FETCH_ASSOC);


// SI TOKEN INVALIDE

if (!$reset) {

    die("Lien de réinitialisation invalide ou expiré.");

}


// TRAITEMENT FORMULAIRE

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $nouveau = $_POST['nouveau_mot_de_passe'];
    $confirmation = $_POST['confirmation'];



    // VERIFICATION CONFIRMATION

    if ($nouveau !== $confirmation) {

        $error = "Les mots de passe ne correspondent pas.";

    }


    // REGLES MOT DE PASSE
    elseif (strlen($nouveau) < 10) {

        $error = "Le mot de passe doit contenir au moins 10 caractères.";

    } elseif (!preg_match('/[A-Z]/', $nouveau)) {

        $error = "Le mot de passe doit contenir au moins une majuscule.";

    } elseif (!preg_match('/[a-z]/', $nouveau)) {

        $error = "Le mot de passe doit contenir au moins une minuscule.";

    } elseif (!preg_match('/[0-9]/', $nouveau)) {

        $error = "Le mot de passe doit contenir au moins un chiffre.";

    } elseif (!preg_match('/[\W_]/', $nouveau)) {

        $error = "Le mot de passe doit contenir au moins un caractère spécial.";

    } else {


        // HASH NOUVEAU MOT DE PASSE

        $hash = password_hash(
            $nouveau,
            PASSWORD_DEFAULT
        );



        // UPDATE USER

        $stmt = $pdo->prepare("
        UPDATE users
        SET mot_de_passe = ?,
            mot_de_passe_modifie = 1
        WHERE id = ?
        ");


        $stmt->execute([

            $hash,
            $reset['user_id']

        ]);



        // SUPPRESSION TOKEN UTILISE

        $stmt = $pdo->prepare("
        DELETE FROM password_resets
        WHERE token = ?
        ");


        $stmt->execute([$token]);



        // REDIRECTION LOGIN

        header(
            "Location: login.php?success=password_reset"
        );

        exit();

    }

}

?>


<!-- HEADER -->

<?php require_once 'includes/header_mdp.php'; ?>


<section class="s-d--small">

    <h1>NOUVEAU MOT DE PASSE</h1>

</section>



<?php if ($error): ?>

    <p class="error-message">
        <?= htmlspecialchars($error) ?>
    </p>

<?php endif; ?>



<div class="password-container">


    <form method="POST" class="user-form">


        <label>Nouveau mot de passe</label>

        <input type="password" name="nouveau_mot_de_passe" required>



        <div class="small-texte">
            10 caractères minimum, avec une majuscule,
            une minuscule, un chiffre et un caractère spécial.
        </div>



        <label>Confirmer le nouveau mot de passe</label>

        <input type="password" name="confirmation" required>



        <button class="user-btn btn-edit">
            MODIFIER
        </button>


    </form>


</div>



<!-- FOOTER -->

<?php require_once 'includes/footer.php'; ?>


</body>

</html>