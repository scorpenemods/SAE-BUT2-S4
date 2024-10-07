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
            $mail->Subject = 'Code de vérification pour réinitialiser votre mot de passe';
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