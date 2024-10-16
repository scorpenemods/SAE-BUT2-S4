<?php
session_start();
require_once "../Model/Database.php";
require_once "../Model/Email.php";
require_once "../vendor/autoload.php";

// Display errors for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set default timezone
date_default_timezone_set('Europe/Paris');

// Initialize Database connection
$database = new Database();

// Fetch user details from session
$userName = $_SESSION['user_name'] ?? 'Guest';
$userEmail = $_SESSION['user_email'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

// Redirect to login if user is not authenticated
if (!$userId || !$userEmail) {
    header("Location: ../Index.php");
    exit();
}

// Initialize error and success messages
$sendError = '';
$sendSuccess = '';
$verifyError = '';
$verifySuccess = '';

// Send verification email using Email class
function sendVerificationEmail($database, $userId, $email, $firstname) {
    // Generate verification code and store in database
    $verification_code = random_int(100000, 999999);
    $expires_at = date("Y-m-d H:i:s", strtotime('+1 hour'));
    $database->storeEmailVerificationCode($userId, $verification_code, $expires_at);

    // Create email subject and body
    $subject = 'Code de verification pour activer votre compte';
    $body = "Bonjour " . htmlspecialchars($firstname) . ",<br><br>Votre code de vérification est : <strong>" . $verification_code . "</strong><br>Ce code expirera dans 1 heure.<br><br>Cordialement,<br>L'équipe de Le Petit Stage.";

    // Use the Email class to send the email
    $emailSender = new Email();
    return $emailSender->sendEmail($email, $firstname, $subject, $body, true);
}

// Handle resend verification code request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['resend_code'])) {
    if (sendVerificationEmail($database, $userId, $userEmail, explode(' ', $userName)[0])) {
        $sendSuccess = "Le code de vérification a été renvoyé à votre adresse email.";
    } else {
        $sendError = "Erreur lors de l'envoi de l'email.";
    }
}

// Handle verification code validation request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['validate_code'])) {
    $entered_code = $_POST['verification_code'];
    $userVerification = $database->getVerificationCode($userId);

    if ($userVerification && $entered_code == $userVerification['code']) {
        $currentTime = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $expiresAt = new DateTime($userVerification['expires_at'], new DateTimeZone('Europe/Paris'));

        if ($currentTime < $expiresAt) {
            // Update email validation status
            $database->updateEmailValidationStatus($userId, 1);

            // Delete verification code after validation
            if ($database->deleteVerificationCode($userId)) {
                error_log("Code supprimé pour l'utilisateur avec l'ID $userId.");
            } else {
                error_log("Erreur lors de la suppression du code pour l'utilisateur avec l'ID $userId.");
            }

            // Remove verification pending cookie
            setcookie('email_verification_pending', '', time() - 3600, "/");

            // Redirect to success page
            header("Location: Success.php");
            exit();
        } else {
            $verifyError = "Le code a expiré. Veuillez demander un nouveau code.";
        }
    } else {
        $verifyError = "Code de vérification incorrect. Veuillez réessayer.";
    }
}

// Send verification email on page load if not already sent
if (!isset($_SESSION['verification_email_sent'])) {
    if (sendVerificationEmail($database, $userId, $userEmail, explode(' ', $userName)[0])) {
        $_SESSION['verification_email_sent'] = true;
    } else {
        $sendError = "Erreur lors de l'envoi de l'email.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation de l'Email</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../View/Verification/VerificationStyles.css">
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const notification = document.getElementById('notification');
            if (notification) {
                notification.classList.add('fade-in');
                setTimeout(() => {
                    notification.classList.remove('fade-in');
                }, 5000);
            }
        });
    </script>
</head>
<body>
<div class="wrapper">
    <header class="navbar">
        <div class="navbar-left">
            <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
            <span class="app-name">Le Petit Stage</span>
        </div>
        <div class="navbar-right">
            <p><?php echo htmlspecialchars($userName); ?></p>
        </div>
    </header>

    <div class="container">
        <h2>Bienvenue sur Le Petit Stage!</h2>
        <p>Pour finaliser votre inscription, veuillez valider votre adresse email. Un code de vérification a été envoyé à votre adresse email.</p>
        <p>Si vous n'avez pas reçu d'email, cliquez sur le bouton ci-dessous pour le renvoyer.</p>

        <?php if (!empty($sendError)): ?>
            <div class="notification error-notification">
                <?php echo htmlspecialchars($sendError); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($sendSuccess)): ?>
            <div class="notification success-notification">
                <?php echo htmlspecialchars($sendSuccess); ?>
            </div>
        <?php endif; ?>

        <!-- Форма для отправки запроса на повторную отправку кода -->
        <form action="" method="POST">
            <button type="submit" name="resend_code" class="btn">Renvoyer le code</button>
        </form>

        <hr>

        <!-- Форма для ввода кода верификации -->
        <h3>Valider votre code de vérification</h3>
        <?php if (!empty($verifyError)): ?>
            <div class="notification error-notification">
                <?php echo htmlspecialchars($verifyError); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($verifySuccess)): ?>
            <div class="notification success-notification">
                <?php echo htmlspecialchars($verifySuccess); ?>
            </div>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="form-group">
                <label for="verification_code">Code de vérification :</label>
                <input type="text" id="verification_code" name="verification_code" placeholder="Entrez le code" required>
            </div>
            <button type="submit" name="validate_code" class="btn">Valider</button>
        </form>
    </div>
</div>

<footer class="PiedDePage">
    <img src="../Resources/Logo_UPHF.png" alt="Logo UPHF">
    <a href="Redirection.php">Informations</a>
    <a href="Redirection.php">À propos</a>
</footer>
</body>
</html>
