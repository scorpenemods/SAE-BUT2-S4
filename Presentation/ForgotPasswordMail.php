<?php
// Démarre une session pour gérer l'état utilisateur
session_start();

// Inclusion de la classe Database et de l'autoload pour PHPMailer
require "../Model/Database.php";
require '../vendor/autoload.php'; // inclut PHPMailer
require "../Model/Email.php"; // Inclure la classe Email

// Variables pour les messages de succès et d'erreur
$success = '';
$error = '';

// Vérifie si la requête a été envoyée via la méthode POST (lorsque le formulaire est soumis)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupère l'adresse email saisie par l'utilisateur
    $email = $_POST['email'];

    // Création d'une instance de la base de données
    $db = (Database::getInstance());
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

        // Prépare l'envoi de l'email en utilisant la classe Email
        $emailSender = new Email();
        $subject = 'Code de verification pour réinitialiser votre mot de passe';
        $body = "Bonjour " . htmlspecialchars($user['prenom']) . ",<br><br>Votre code de vérification est : <strong>" . $verification_code . "</strong><br>Ce code expirera dans 1 heure.<br><br>Cordialement,<br>L'équipe de Le Petit Stage.";

        if ($emailSender->sendEmail($email, $user['prenom'] . ' ' . $user['nom'], $subject, $body, true)) {
            $success = "Un code de vérification a été envoyé à votre adresse email."; // Message de succès

            // Enregistre l'email dans la session et redirige vers la page de vérification du code
            $_SESSION['email_verification'] = $email;
            header("Location: VerifyCode.php");
            exit();
        } else {
            // En cas d'erreur lors de l'envoi de l'email
            $error = "Une erreur est survenue lors de l'envoi de l'email. Veuillez réessayer plus tard.";
        }
    } else {
        // Si aucun utilisateur n'est trouvé avec cet email, afficher une erreur
        $error = "Aucun compte n'est associé à cette adresse email.";
    }
}

// LANGAGE

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
    <title><?= $translations['title_mdp'] ?></title>
    <link rel="stylesheet" href="../View/ForgotPassword/ForgotPswdStyles.css">
    <!-- Lien vers Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha256-DIym3sfZPMYqRkk8oWhZyEEo9xYKpIZo2Vafz3tbv94=" crossorigin="anonymous" />
</head>
<body>
<div class="container">
    <h2><?= $translations['reni_mdp'] ?></h2>

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
            <label for="email"><?= $translations['adresse_mail'] ?></label>
            <div class="input-icon">
                <input type="email" id="email" name="email" placeholder='<?= $translations['enter_email'] ?>' required>
                <i class="fas fa-envelope"></i> <!-- Icône pour l'email -->
            </div>
        </div>
        <button type="submit" class="btn"><?= $translations['send_code'] ?></button>
    </form>

    <!-- Lien pour revenir à la page de connexion -->
    <div class="link">
        <a href="../Index.php"><?= $translations['retour_connexion'] ?></a>
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
