<?php
session_start(); // Start the session

require "../Model/Database.php";// Fichier nécessaire pour accéder à la base de données
require "../Model/Person.php"; //Fichier nécessaire pour récupérer le user

$db = new Database();
$error = '';
$success = '';

//$user_login = $_SESSION['login'] ?? ''; // Login de l'utilisateur pour vérifier mot de passe
//$email = $_SESSION['email'] ?? ''; // Email de l'utilisateur pour update mot de passe

if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    // Vérifie si l'objet déserialisé est une instance de la classe Person
    if ($person instanceof Person) {
        // Sécurise et affiche le prénom et le nom de la personne connectée
        $userName = htmlspecialchars($person->getPrenom()) . ' ' . htmlspecialchars($person->getNom());
    }
} else {
    // Si aucune session d'utilisateur n'est trouvée, redirige vers la page de déconnexion
    header("Location: Logout.php");
    exit();
}


// Vérification si la méthode de la requête HTTP est POST, ce qui indique que le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $person->getEmail();
    $verification_mdp = $_POST["verification_mdp"];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];


    // Vérifie si le mot de passe est correcte
    if ($db->verifyLogin($email, $verification_mdp)) {
        if ($new_password !== $confirm_password) {
            $error = "Les mots de passe ne correspondent pas.";
        } else {
            // modification du mot de passe
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
            if ($db->updateUserPasswordByEmail($email, $hashedPassword)) {
                $success = "Le mot de passe a été changé avec succès.";
            } else {
                $error = "Erreur lors de la mise à jour du mot de passe.";
            }
        }
    } else {
        $error = "Le mot de passe est incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérification du code</title>
    <link rel="stylesheet" href="../View/ForgotPassword/ForgotPswdStyles.css">
</head>
<body>
<div class="container">
    <h2>Changement du mot de passe</h2>
    <?php if (!empty($error)) { ?>
        <div class="notification error-notification">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error; ?>
        </div>
    <?php } elseif (!empty($success)) { ?>
        <div class="notification success-notification">
            <i class="fas fa-check-circle"></i>
            <?php echo $success; ?>
        </div>
    <?php } ?>
    <?php if (empty($success)) { ?>
        <form action="ChangePassword.php" method="POST">
            <div class="form-group">
                <label for="verification_mdp">Ancien mot de passe :</label>
                <input type="password" id="verification_mdp" name="verification_mdp" placeholder="Mot de passe" required>
            </div>
            <div class="form-group">
                <label for="new_password">Nouveau mot de passe :</label>
                <input type="password" id="new_password" name="new_password" placeholder="Nouveau mot de passe" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe :</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirmez le mot de passe" required>
            </div>
            <button type="submit" class="btn">Réinitialiser le mot de passe</button>
        </form>
    <?php } ?>
    <div class="link">
        <a href="Settings.php">retour</a>
    </div>
</div>
</body>
</html>