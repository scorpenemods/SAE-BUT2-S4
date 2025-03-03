<?php
// Vérification adresse email
// Démarre la session pour gérer l'authentification de l'utilisateur
session_start();

// Inclusion des fichiers nécessaires
require_once "../Model/Database.php"; // Classe pour gérer la base de données
require_once "../Model/Email.php";    // Classe pour gérer l'envoi des emails
require_once "../vendor/autoload.php"; // Chargement automatique de Composer (inclut PHPMailer)

// (à désactiver en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Définir le fuseau horaire sur l'Europe/Paris
date_default_timezone_set('Europe/Paris');

// Initialisation de la connexion à la base de données
$database = (Database::getInstance());

// Récupérer les détails de l'utilisateur à partir de la session
$userName = $_SESSION['user_name'] ?? 'Guest'; // Nom de l'utilisateur ou 'Guest' s'il n'est pas connecté
$userEmail = $_SESSION['user_email'] ?? null;  // Email de l'utilisateur (null si non défini)
$userId = $_SESSION['user_id'] ?? null;        // ID de l'utilisateur (null si non défini)

// Si l'utilisateur n'est pas authentifié, redirige vers la page de connexion
if (!$userId || !$userEmail) {
    header("Location: ../index.php");
    exit(); // S'assurer que le script s'arrête après la redirection
}

// Initialisation des messages d'erreur et de succès
$sendError = '';  // Message d'erreur lors de l'envoi de l'email
$sendSuccess = ''; // Message de succès lors de l'envoi de l'email
$verifyError = ''; // Message d'erreur lors de la vérification du code
$verifySuccess = ''; // Message de succès lors de la vérification du code

// Fonction pour envoyer un email de vérification en utilisant la classe Email
function sendVerificationEmail($database, $userId, $email, $firstname) {
    // Génère un code de vérification à 6 chiffres
    $verification_code = random_int(100000, 999999);

    // Définit la date d'expiration du code de vérification (1 heure)
    $expires_at = date("Y-m-d H:i:s", strtotime('+1 hour'));

    // Stocke le code de vérification et sa date d'expiration dans la base de données
    $database->storeEmailVerificationCode($userId, $verification_code, $expires_at);

    // Création du sujet et du contenu de l'email
    $subject = 'Code de verification pour activer votre compte';
    $body = "Bonjour " . htmlspecialchars($firstname) . ",<br><br>Votre code de vérification est : <strong>" . $verification_code . "</strong><br>Ce code expirera dans 1 heure.<br><br>Cordialement,<br>L'équipe de Le Petit Stage.";

    // Utilisation de la classe Email pour envoyer l'email
    $emailSender = new Email();
    return $emailSender->sendEmail($email, $firstname, $subject, $body, true);
}
// -> Send a code with page load if it doesn't  exist
if ($_SERVER["REQUEST_METHOD"] === 'GET') {
    $existingCode = $database->getVerificationCode($userId);
    if (!$existingCode) {
        if (sendVerificationEmail($database, $userId, $userEmail, explode(' ', $userName)[0])) {
            $sendSuccess = "Le code de vérification a été envoyé à votre adresse email.";
        } else {
            $sendError = "Erreur lors de l'envoi de l'email.";
        }
    }
}

// Gestion de la demande de ré-envoi du code de vérification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['resend_code'])) {
    // Appelle la fonction d'envoi de l'email et affiche un message de succès ou d'erreur
    if (sendVerificationEmail($database, $userId, $userEmail, explode(' ', $userName)[0])) {
        $sendSuccess = "Le code de vérification a été renvoyé à votre adresse email.";
    } else {
        $sendError = "Erreur lors de l'envoi de l'email.";
    }
}

// Gestion de la vérification du code de vérification saisi par l'utilisateur
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['validate_code'])) {
    $entered_code = trim($_POST['verification_code']); // Code saisi par l'utilisateur

    // Vérifier que le code est composé uniquement de chiffres et a la bonne longueur
    if (!ctype_digit($entered_code) || strlen($entered_code) !== 6) {
        $verifyError = "Le code de vérification doit être un nombre de 6 chiffres.";
    } else {
        $userVerification = $database->getVerificationCode($userId); // Récupère le code de la base de données

        // Vérifier que le code de vérification a été récupéré
        if ($userVerification !== false) {
            // Debugging: Afficher les codes pour comparaison
            error_log("Code saisi par l'utilisateur : " . $entered_code);
            error_log("Code en base de données : " . $userVerification['code']);

            // Comparer les codes en tant que chaînes de caractères
            if (strval($entered_code) === strval($userVerification['code'])) {
                $currentTime = new DateTime('now', new DateTimeZone('Europe/Paris')); // Heure actuelle
                $expiresAt = new DateTime($userVerification['expires_at'], new DateTimeZone('Europe/Paris')); // Heure d'expiration du code

                // Vérifie si le code est encore valide (non expiré)
                if ($currentTime < $expiresAt) {
                    // Met à jour le statut de validation de l'email dans la base de données
                    $database->updateEmailValidationStatus($userId, 1);

                    // Supprime le code de vérification après validation
                    if ($database->deleteVerificationCode($userId)) {
                        error_log("Code supprimé pour l'utilisateur avec l'ID $userId.");
                    } else {
                        error_log("Erreur lors de la suppression du code pour l'utilisateur avec l'ID $userId.");
                    }

                    // Supprime le cookie indiquant que la vérification de l'email est en attente
                    setcookie('email_verification_pending', '', time() - 3600, "/");

                    // Redirection vers la page de succès après validation
                    header("Location: Success.php");
                    exit();
                } else {
                    // Le code a expiré
                    $verifyError = "Le code a expiré. Veuillez demander un nouveau code.";
                }
            } else {
                // Le code saisi est incorrect
                $verifyError = "Code de vérification incorrect. Veuillez réessayer.";
            }
        } else {
            // Aucun code de vérification valide n'a été trouvé
            $verifyError = "Aucun code de vérification valide n'a été trouvé. Veuillez demander un nouveau code.";
        }
    }
}


//TRADUCTION

// Vérifier si une langue est définie dans l'URL, sinon utiliser la session ou le français par défaut
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang; // Enregistrer la langue en session
} else {
    $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr'; // Langue par défaut
}

// Vérification si le fichier de langue existe, sinon charger le français par défaut
$langFile = "../Locales/{$lang}.php";
if (!file_exists($langFile)) {
    $langFile = "../Locales/fr.php";
}

// Charger les traductions
$translations = include $langFile;

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
            // Animation pour faire apparaître la notification de manière fluide
            const notification = document.getElementById('notification');
            if (notification) {
                notification.classList.add('fade-in');
                setTimeout(() => {
                    notification.classList.remove('fade-in');
                }, 5000); // La notification disparaît après 5 secondes
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
            <p><?php echo htmlspecialchars($userName); ?></p> <!-- Affiche le nom de l'utilisateur -->
        </div>
    </header>

    <div class="container">
        <h2><?= $translations['welcome_message']?>!</h2>
        <p><?= $translations['pour finaliser votre inscription, veuillez valider votre adresse email. Un code de vérification a été envoyé à votre adresse email']?>.</p>
        <p><?= $translations["si vous n'avez pas reçu d'email, cliquez sur le bouton ci-dessous pour le renvoyer"]?>.</p>

        <!-- Affichage des messages d'erreur et de succès -->
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

        <!-- Formulaire pour renvoyer le code de vérification -->
        <form action="" method="POST">
            <button type="submit" name="resend_code" class="btn"><?= $translations['send_code']?></button>
        </form>

        <hr>

        <!-- Formulaire pour valider le code de vérification -->
        <h3><?= $translations['valider votre code de vérification']?></h3>
        <?php if (!empty($verifyError)): ?>
            <div class="notification error-notification">
                <?php echo htmlspecialchars($verifyError); ?>
            </div>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="form-group">
                <label for="verification_code"><?= $translations['code de vérification']?> :</label>
                <input type="text" id="verification_code" name="verification_code" placeholder=<?= $translations['entrez le code']?> required>
            </div>
            <button type="submit" name="validate_code" class="btn"><?= $translations['validate']?></button>
        </form>
    </div>
</div>

<footer class="PiedDePage">
    <img src="../Resources/Logo_UPHF.png" alt="Logo UPHF">
    <a href="Redirection.php"><?= $translations['information_settings']?></a>
    <a href="Redirection.php"><?= $translations['a_propos']?></a>
</footer>
</body>
</html>

