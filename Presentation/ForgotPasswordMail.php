<?php
session_start();
require "../Model/Database.php";
require '../vendor/autoload.php'; // includes PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    $db = new Database();
    $user = $db->getUserByEmail($email);

    if ($user) {
        // Generate a 6-digit confirmation code
        $verification_code = random_int(100000, 999999);
        $expires_at = date("Y-m-d H:i:s", strtotime('+1 hour'));

        $db->storeVerificationCode($email, $verification_code, $expires_at);

        // send a letter with a confirmation code
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'secretariat.lps.official@gmail.com'; // our sae email
            $mail->Password = 'xtdu vchi sldx qmyi';    // Change on var to not stock real data in open source code
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('no-reply@seciut.com', 'Le Petit Stage Team');
            $mail->addAddress($email, $user['prenom'] . ' ' . $user['nom']);

            // Contents of the letter
            $mail->isHTML(true);
            $mail->Subject = 'Code de verification pour reinitialiser votre mot de passe';
            $mail->Body = "Bonjour " . htmlspecialchars($user['prenom']) . ",<br><br>Votre code de vérification est : <strong>" . $verification_code . "</strong><br>Ce code expirera dans 1 heure.<br><br>Cordialement,<br>L'équipe de Le Petit Stage.";

            $mail->send();
            $success = "Un code de vérification a été envoyé à votre adresse email.";
            // Saving the email in the session and redirect the user to the code entry page
            $_SESSION['email_verification'] = $email;
            header("Location: VerifyCode.php");
            exit();
        } catch (Exception $e) {
            error_log("Erreur lors de l'envoi de l'email : {$mail->ErrorInfo}");
            $error = "Une erreur est survenue lors de l'envoi de l'email. Veuillez réessayer plus tard.";
        }
    } else {
        $error = "Aucun compte n'est associé à cette adresse email.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié</title>
    <link rel="stylesheet" href="../View/ForgotPassword/ForgotPswdStyles.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha256-DIym3sfZPMYqRkk8oWhZyEEo9xYKpIZo2Vafz3tbv94=" crossorigin="anonymous" />
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
    <form action="" method="POST">
        <div class="form-group">
            <label for="email">Adresse email :</label>
            <div class="input-icon">
                <input type="email" id="email" name="email" placeholder="Entrez votre adresse email" required>
                <i class="fas fa-envelope"></i>
            </div>
        </div>
        <button type="submit" class="btn">Envoyer le code de vérification</button>
    </form>
    <div class="link">
        <a href="../Index.php">Retour à la connexion</a>
    </div>
</div>
<script>
    setTimeout(function() {
        var notification = document.querySelector('.notification');
        if (notification) {
            notification.style.display = 'none';
        }
    }, 5000); // Hide notification in 5 sec
</script>
</body>
</html>