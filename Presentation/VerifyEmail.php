<?php
// manage right email
/*
 * Ce script gère la vérification de l'email utilisateur.
 * Il valide le code de vérification et met à jour le statut de l'utilisateur.
 */
session_start();
require "../Model/Database.php";

date_default_timezone_set('Europe/Paris');  // Setting the time zone

$database = (Database::getInstance());
$verificationError = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $code = $_POST['verification_code'];

    // Receiving userId by email
    $userId = $database->getUserIdByEmail($email);

    if (!$userId) {
        $verificationError = "Adresse email non reconnue.";
    } else {
        $userVerification = $database->getVerificationCode($userId);

        // Checking for code availability
        if ($userVerification && $code == $userVerification['code']) {
            // Getting the current time
            $currentTime = new DateTime('now', new DateTimeZone('Europe/Paris'));
            // Getting the code expiration time
            $expiresAt = new DateTime($userVerification['expires_at'], new DateTimeZone('Europe/Paris'));

            // Check if the current date is less than the code expiration date
            if ($currentTime < $expiresAt) {
                $database->updateEmailValidationStatus($userId, 1); // Updating the validation status
                header("Location: Success.php");
                exit();
            } else {
                $verificationError = "Le code a expiré. Veuillez demander un nouveau code.";
            }
        } else {
            $verificationError = "Code de vérification incorrect. Veuillez réessayer.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Validation de l'Email</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../View/Verification/VerificationStyles.css">
</head>
<body>
<div class="wrapper">
    <div class="container">
        <h2>Validez votre adresse email</h2>
        <p>Nous avons envoyé un code de vérification à votre adresse email. Veuillez entrer le code ci-dessous pour vérifier votre adresse email.</p>
        <?php if (!empty($verificationError)) { ?>
            <div class="notification error-notification">
                <?php echo htmlspecialchars($verificationError); ?>
            </div>
        <?php } ?>
        <form action="" method="POST">
            <div class="form-group">
                <label for="email">Adresse Email :</label>
                <input type="email" id="email" name="email" placeholder="Entrez votre email" required value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="verification_code">Code de vérification :</label>
                <input type="text" id="verification_code" name="verification_code" placeholder="Entrez le code" required>
            </div>
            <button type="submit" class="btn">Valider</button>
        </form>
        <div class="link">
            <a href="../index.php">Retour à l'accueil</a>
        </div>
    </div>
</div>
</body>
</html>