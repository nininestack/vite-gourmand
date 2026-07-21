<?php

// DEMARER LA SESSION

session_start();

// CONNEXION A LA BASE DE DONNEES

require_once 'config/database/database.php';

// VERIFIER SI L'UTILISATEUR EST CONNECTE ET EST ADMIN

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: admin.php#employes");
    exit();
}

// RECUPERER LES INFORMATIONS DE L'EMPLOYE ET DE L'UTILISATEUR LIE

$stmt = $pdo->prepare("
    SELECT e.*, u.nom, u.prenom, u.email, u.telephone, u.adresse
    FROM employees e
    JOIN users u ON e.users_id = u.id
    WHERE e.id = ?
");

$stmt->execute([$_GET['id']]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    die("Employé introuvable");
}
?>

<!-- HEADER-->

<?php require_once 'includes/header_edit.php'; ?>

<section class="s-d--small">

    <!-- FORMULAIRE DE MODIFICATION DE L'EMPLOYE -->

    <h1>MODIFIER L'EMPLOYÉ</h1>
</section>

<section class="section-center-edit">

    <form method="POST" action="update_employee.php">

        <input type="hidden" name="id" value="<?= $employee['id'] ?>">

        <input type="text" name="nom" value="<?= htmlspecialchars($employee['nom']) ?>" required>
        <input type="text" name="prenom" value="<?= htmlspecialchars($employee['prenom']) ?>" required>

        <input type="date" name="date_naissance" value="<?= $employee['date_naissance'] ?>" required>
        <input type="text" name="ville_naissance" value="<?= htmlspecialchars($employee['ville_naissance']) ?>"
            required>

        <input type="text" name="adresse" value="<?= htmlspecialchars($employee['adresse']) ?>" required>

        <select name="type_contrat" required>
            <option value="CDI" <?= $employee['type_contrat'] == 'CDI' ? 'selected' : '' ?>>CDI</option>
            <option value="CDD" <?= $employee['type_contrat'] == 'CDD' ? 'selected' : '' ?>>CDD</option>
            <option value="Interim" <?= $employee['type_contrat'] == 'Interim' ? 'selected' : '' ?>>Intérim</option>
        </select>

        <input type="date" name="date_embauche" value="<?= $employee['date_embauche'] ?>" required>

        <input type="number" step="0.01" name="salaire_heure" value="<?= $employee['salaire_heure'] ?>" required>

        <input type="number" name="heures_semaine" value="<?= $employee['heures_semaine'] ?>" required>

        <input type="tel" name="telephone" value="<?= htmlspecialchars($employee['telephone']) ?>" required>
        <input type="email" name="email" value="<?= htmlspecialchars($employee['email']) ?>" required>

        <!-- BOUTON DE SOUMISSION ET LIEN DE RETOUR -->

        <button type="submit" class="user-btn btn-create">SAUVEGARDER</button>

        <a href="admin.php#employes" class="user-btn btn-return">RETOUR</a>

    </form>

</section>

<div class="separation1"></div>

<div class="accueil-image">
    <picture>
        <source media="(max-width: 768px)" srcset="public/assets/img/mob/mob_coppa.png">
        <source media="(min-width: 769px)" srcset="public/assets/img/web/web_coppa.png">
        <img src="public/assets/img/mob/mob_coppa.png" alt="ACCUEIL">
    </picture>
</div>


<!-- FOOTER-->

<?php require_once 'includes/footer.php'; ?>

</body>

</html>