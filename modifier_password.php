<?php

// DEMARRER LA SESSION

session_start();

// CONNEXION BDD

require_once 'database/database.php';

// VERIFIER CONNEXION

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


// RECUPERER USER

$stmt = $pdo->prepare("
SELECT *
FROM users
WHERE id = ?
");

$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$error = null;

// TRAITEMENT FORMULAIRE

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ancien = $_POST['ancien_mot_de_passe'];
    $nouveau = $_POST['nouveau_mot_de_passe'];
    $confirmation = $_POST['confirmation'];


    // VERIFIER ANCIEN MOT DE PASSE
    if (!password_verify($ancien, $user['mot_de_passe'])) {
        $error = "Ancien mot de passe incorrect";
    }

    // VERIFIER CONFIRMATION
    elseif ($nouveau !== $confirmation) {
        $error = "Les mots de passe ne correspondent pas";
    }

    // VERIFIER REGLES MOT DE PASSE
    elseif (strlen($nouveau) < 10) {
        $error = "Le mot de passe doit contenir au moins 10 caractères";

    } elseif (!preg_match('/[A-Z]/', $nouveau)) {
        $error = "Le mot de passe doit contenir au moins une majuscule";

    } elseif (!preg_match('/[a-z]/', $nouveau)) {
        $error = "Le mot de passe doit contenir au moins une minuscule";

    } elseif (!preg_match('/[0-9]/', $nouveau)) {
        $error = "Le mot de passe doit contenir au moins un chiffre";

    } elseif (!preg_match('/[\W_]/', $nouveau)) {
        $error = "Le mot de passe doit contenir au moins un caractère spécial";

    } else {

        // HASH DU NOUVEAU MOT DE PASSE

        $hash = password_hash(
            $nouveau,
            PASSWORD_DEFAULT
        );


        // UPDATE

        $stmt = $pdo->prepare("
        UPDATE users
        SET 
        mot_de_passe = ?,
        mot_de_passe_modifie = 1

        WHERE id = ?
        ");


        $stmt->execute([
            $hash,
            $user_id
        ]);


        // REDIRECTION SELON LE ROLE

        if ($user['role'] == 'admin') {
            header("Location: admin.php?success=password_changed");

        } elseif ($user['role'] == 'employe') {
            header("Location: employe.php?success=password_changed");

        } else {
            header("Location: user.php?success=password_changed");
        }

        exit();

    }

}

?>

<!-- HEADER-->

<?php require_once 'includes/header_mdp.php'; ?>

<section class="s-d--small">

    <!-- MODIFIER MOT DE PASSE -->

    <h1>MODIFIER MON MOT DE PASSE</h1>
</section>

<!-- AFFICHAGE DES ERREURS-->

<?php if ($error): ?>
    <p class="error-message">
        <?= $error ?>
    </p>
<?php endif; ?>

<!-- FORMULAIRE DE MODIFICATION-->

<div class="password-container">

    <form method="POST" class="user-form">

        <label>Ancien mot de passe</label>
        <input type="password" name="ancien_mot_de_passe" required>

        <label>Nouveau mot de passe</label>

        <input type="password" name="nouveau_mot_de_passe" required>


        <div class="small-texte">
            10 caractères minimum, avec une majuscule, une minuscule,
            un chiffre et un caractère spécial.
        </div>


        <label>Confirmer</label>
        <input type="password" name="confirmation" required>

        <button class="user-btn btn-edit">MODIFIER</button>

    </form>

</div>


<!-- FOOTER-->

<?php require_once 'includes/footer.php'; ?>

</body>

</html>