<?php
// Démarre une session pour gérer l'état utilisateur
session_start();

// Inclusion de la classe Database et de l'autoload pour PHPMailer
require "../Model/Database.php";
require '../vendor/autoload.php'; // inclut PHPMailer

// Importation des classes PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Variables pour les messages de succès et d'erreur
$success = '';
$error = '';

// Vérifie si la requête a été envoyée via la méthode POST (lorsque le formulaire est soumis)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupère l'adresse email saisie par l'utilisateur
    $email = $_POST['email'];

    // Création d'une instance de la base de données
    $db = new Database();
    // Récupère les informations utilisateur en fonction de l'email
    $user = $db->getUserByEmail($email);

    // Si un utilisateur est trouvé avec cet email
    if ($user) {
        // Génère un code de vérification à 6 chiffres
        $verification_code = random_int(100000, 999999);
        // Définir la date d'expiration (1 heure)
        $expires_at = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // Stocke le code de vérification dans la base de données avec la date d'expiration
        $userId = $user['id'];  // L'ID de l'utilisateur devrait être dans $user
        $db->storeVerificationCode($userId, $verification_code, $expires_at);

        // Prépare l'envoi de l'email avec PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Configuration du serveur SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'secretariat.lps.official@gmail.com'; // Email utilisé pour l'envoi
            $mail->Password = 'xtdu vchi sldx qmyi'; // A remplacer par une variable pour ne pas stocker de données sensibles
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Destinataire de l'email
            $mail->setFrom('no-reply@seciut.com', 'Le Petit Stage Team'); // Adresse de l'expéditeur
            $mail->addAddress($email, $user['prenom'] . ' ' . $user['nom']); // Adresse du destinataire

            // Contenu de l'email
            $mail->isHTML(true); // Définit que l'email est en HTML
            $mail->Subject = 'Code de verification pour réinitialiser votre mot de passe'; // Sujet de l'email
            $mail->Body = "Bonjour " . htmlspecialchars($user['prenom']) . ",<br><br>Votre code de vérification est : <strong>" . $verification_code . "</strong><br>Ce code expirera dans 1 heure.<br><br>Cordialement,<br>L'équipe de Le Petit Stage."; // Corps de l'email

            // Envoie l'email
            $mail->send();
            $success = "Un code de vérification a été envoyé à votre adresse email."; // Message de succès

            // Enregistre l'email dans la session et redirige vers la page de vérification du code
            $_SESSION['email_verification'] = $email;
            header("Location: VerifyCode.php");
            exit();
        } catch (Exception $e) {
            // En cas d'erreur lors de l'envoi de l'email, log l'erreur et affiche un message
            error_log("Erreur lors de l'envoi de l'email : {$mail->ErrorInfo}");
            $error = "Une erreur est survenue lors de l'envoi de l'email. Veuillez réessayer plus tard.";
        }
    } else {
        // Si aucun utilisateur n'est trouvé avec cet email, afficher une erreur
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
    <!-- Lien vers Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha256-DIym3sfZPMYqRkk8oWhZyEEo9xYKpIZo2Vafz3tbv94=" crossorigin="anonymous" />
</head>
<body>
<div class="container">
    <h2>Réinitialisation du mot de passe</h2>

    <!-- Affiche un message d'erreur ou de succès selon le cas -->
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

    <!-- Formulaire pour soumettre l'adresse email -->
    <form action="" method="POST">
        <div class="form-group">
            <label for="email">Adresse email :</label>
            <div class="input-icon">
                <input type="email" id="email" name="email" placeholder="Entrez votre adresse email" required>
                <i class="fas fa-envelope"></i> <!-- Icône pour l'email -->
            </div>
        </div>
        <button type="submit" class="btn">Envoyer le code de vérification</button>
    </form>

    <!-- Lien pour revenir à la page de connexion -->
    <div class="link">
        <a href="../Index.php">Retour à la connexion</a>
    </div>
</div>

<!-- Script pour cacher les notifications après 5 secondes -->
<script>
    setTimeout(function() {
        var notification = document.querySelector('.notification');
        if (notification) {
            notification.style.display = 'none';
        }
    }, 5000); // Masquer la notification après 5 secondes
</script>
</body>
</html>
