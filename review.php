<?php

session_start();

require_once 'config/database/database.php';


// VERIFIER CONNEXION

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$user_id = $_SESSION['user_id'];


// VERIFIER ID COMMANDE

if (!isset($_GET['order'])) {
    header("Location: user.php");
    exit();
}


$order_id = $_GET['order'];


// VERIFIER QUE LA COMMANDE APPARTIENT AU CLIENT ET EST LIVREE

$stmt = $pdo->prepare("
SELECT *
FROM orders
WHERE id = ?
AND users_id = ?
AND statut = 'livree'
");

$stmt->execute([
    $order_id,
    $user_id
]);


$order = $stmt->fetch(PDO::FETCH_ASSOC);



if (!$order) {
    header("Location: user.php");
    exit();
}


// VERIFIER SI UN AVIS EXISTE DEJA

$stmt = $pdo->prepare("
SELECT id
FROM reviews
WHERE orders_id = ?
");

$stmt->execute([$order_id]);


if ($stmt->fetch()) {

    header("Location: user.php");
    exit();

}

// ENREGISTRER L'AVIS

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note = filter_input(INPUT_POST, 'note', FILTER_VALIDATE_INT);
    $commentaire = trim($_POST['commentaire']);

    // VERIFIER LA NOTE

    if ($note < 1 || $note > 5) {
        exit("Note invalide");
    }

    // VERIFIER COMMENTAIRE

    if ($commentaire === '') {
        exit("Le commentaire est obligatoire");
    }


    if (strlen($commentaire) > 1000) {
        exit("Commentaire trop long");
    }

    $stmt = $pdo->prepare("
    INSERT INTO reviews
    (
        users_id,
        orders_id,
        note,
        commentaire
    )
    VALUES
    (?, ?, ?, ?)
    ");

    $stmt->execute([
        $user_id,
        $order_id,
        $note,
        $commentaire
    ]);

    header("Location: user.php?success=review_sent");
    exit();

}

?>

<!-- HEADER-->

<?php require_once 'includes/header_user.php'; ?>

<section class="s-d--small">

    <!-- LAISSER UN AVIS -->

    <h1>LAISSER UN AVIS</h1>
</section>

<section class="login">

    <form method="POST" class="login-form">

        <div class="input-group">
            <label for="note">Note</label>
            <select name="note" id="note" required>
                <option value="5">★★★★★</option>
                <option value="4">★★★★</option>
                <option value="3">★★★</option>
                <option value="2">★★</option>
                <option value="1">★</option>
            </select>
        </div>

        <div class="input-group">
            <label for="commentaire">Votre avis</label>
            <textarea name="commentaire" id="commentaire" required></textarea>
        </div>

        <button type="submit" class="btn btn-red">ENVOYER MON AVIS</button>

    </form>

</section>



<!-- FOOTER-->

<?php require_once 'includes/footer.php'; ?>

</body>

</html>