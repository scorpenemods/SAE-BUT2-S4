<?php
session_start();
require "../Model/Database.php";

$email = $_SESSION['email_verification'] ?? null;

if (!$email) {
    header("Location: ForgotPassword.php");
    exit();
}

$db = new Database();
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $verification_code = $_POST['verification_code'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Checking password matches
    if ($new_password != $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        // Verification code check
        $resetRequest = $db->getPasswordResetRequest($email, $verification_code);

        if ($resetRequest) {
            // Checking the expiration date of the code
            if (strtotime($resetRequest['expires_at']) >= time()) {
                // Update user password
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                if ($db->updateUserPasswordByEmail($email, $hashedPassword)) {
                    // Removing the verification code
                    $db->deleteVerificationCode($email);
                    $success = "Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.";
                    unset($_SESSION['email_verification']);
                } else {
                    $error = "Une erreur est survenue. Veuillez réessayer.";
                }
            } else {
                $error = "Le code de vérification a expiré. Veuillez refaire une demande.";
                $db->deleteVerificationCode($email);
            }
        } else {
            $error = "Code de vérification incorrect.";
        }
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
    <h2>Réinitialisation du mot de passe</h2>
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
        <form action="VerifyCode.php" method="POST">
            <div class="form-group">
                <label for="verification_code">Code de vérification :</label>
                <input type="text" id="verification_code" name="verification_code" placeholder="Entrez le code reçu par email" required>
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
        <a href="../Index.php">Retour à la connexion</a>
    </div>
</div>
</body>
</html>