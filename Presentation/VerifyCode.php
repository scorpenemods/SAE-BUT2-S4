<?php
session_start();
require "../Model/Database.php";

$db = new Database();
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $verification_code = $_POST['verification_code'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password != $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $userVerification = $db->getVerificationByCode($verification_code);
        if ($userVerification) {
            error_log("Code de vérification trouvé pour l'utilisateur : " . $userVerification['email']);

            if (strtotime($userVerification['expires_at']) >= time()) {
                $email = $userVerification['email'];
                $userId = $userVerification['user_id'];

                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

                if ($db->updateUserPasswordByEmail($email, $hashedPassword)) {
                    $db->deleteVerificationCode($userId);
                    $success = "Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.";
                } else {
                    $error = "Une erreur est survenue lors de la mise à jour du mot de passe.";
                    error_log("Erreur lors de la mise à jour du mot de passe.");
                }
            } else {
                $error = "Le code de vérification a expiré.";
                $db->deleteVerificationCode($userVerification['user_id']);
            }
        } else {
            $error = "Code de vérification incorrect.";
            error_log("Code de vérification incorrect : " . $verification_code);
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
