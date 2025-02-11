<?php
// Affichage menu de connexion
// Démarrage d'une nouvelle session ou reprise d'une session existante
session_start();
define('BASE_PATH', dirname(__DIR__));
require_once 'Model/Database.php';
require_once 'Model/Person.php';

$database = Database::getInstance();
$errorMessage = '';

// init .env variables
require __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_block_time'] = 0;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_SESSION['login_attempts'] >= 3 && time() < $_SESSION['login_block_time']) {
        $errorMessage = "Trop de tentatives ! Veuillez réessayer dans 10 secondes.";
    } else {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Adresse email invalide.';
        } else {
            /*
            // Vérification reCAPTCHA
            if (isset($_POST['g-recaptcha-response'])) {
                $recaptchaResponse = $_POST['g-recaptcha-response'];
                $secretKey = "6LfFBNEqAAAAAK9Ysfx2WsakloQLjFvAkvcgMY3q";
                $verifyURL = "https://www.google.com/recaptcha/api/siteverify";

                $response = file_get_contents($verifyURL . "?secret=" . $secretKey . "&response=" . $recaptchaResponse);
                $responseKeys = json_decode($response, true);

                if (!$responseKeys["success"]) {
                    $errorMessage = "Veuillez valider le reCAPTCHA.";
                }
            } else {
                $errorMessage = "Veuillez cocher le reCAPTCHA.";
            }
            */
            if (empty($errorMessage)) {
                $loginResult = $database->verifyLogin($email, $password);

                if (!empty($loginResult)) {
                    $_SESSION['login_attempts'] = 0;
                    $user = $loginResult['user'];
                    $_SESSION['company_id'] = Database::getInstance()->getCompanyidbyUserId($user['id']);

                    if ($loginResult['valid_email'] == 0) {
                        setcookie('email_verification_pending', '1', time() + 3600, "/");
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
                        header("Location: index.php");
                        exit();
                    }

                    if ($loginResult['status_user'] == 0) {
                        $errorMessage = "Votre compte est en attente d'activation par l'administration.";
                    } else {
                        $database->updateLastConnexion($user['id']);
                        $person = new Person(
                            $user['nom'],
                            $user['prenom'],
                            $user['telephone'],
                            $user['role'],
                            $user['activite'],
                            $user['email'],
                            $user['id']
                        );

                        $_SESSION['personne'] = $person;
                        $_SESSION['user_id'] = $person->getId();
                        $_SESSION['user'] = serialize($person);
                        $_SESSION['user_role'] = $person->getRole();
                        $_SESSION['user_name'] = $person->getPrenom() . ' ' . $person->getNom();

                        switch ($_SESSION['user_role']) {
                            case 1:
                                header("Location: Presentation/Student.php");
                                break;
                            case 2:
                                header("Location: Presentation/Professor.php");
                                break;
                            case 3:
                                header("Location: Presentation/MaitreStage.php");
                                break;
                            case 4:
                            case 5:
                                header("Location: Presentation/Secretariat.php");
                                break;
                            default:
                                header("Location: Presentation/Redirection.php");
                                break;
                        }
                        exit();
                    }
                } else {
                    $_SESSION['login_attempts']++;
                    if ($_SESSION['login_attempts'] >= 3) {
                        $_SESSION['login_block_time'] = time() + 10;
                        $errorMessage = 'Trop de tentatives ! Veuillez réessayer dans 10 secondes.';
                    } else {
                        $errorMessage = 'Mot de passe incorrects. Tentative ' . $_SESSION['login_attempts'] . ' sur 3.';
                    }
                }
            }
        }
    }
    $database->closeConnection();
}

// LANGAGE NOAH

// Vérifier si une langue est définie dans l'URL, sinon utiliser la session ou le français par défaut
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang; // Enregistrer la langue en session
} else {
    $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr'; // Langue par défaut
}

// Vérification si le fichier de langue existe, sinon charger le français par défaut
$langFile = __DIR__ . "/Locales/{$lang}.php";
if (!file_exists($langFile)) {
    $langFile = __DIR__ . "/Locales/fr.php";
}

// Charger les traductions
$translations = include $langFile;

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Petit Stage</title>
    <!-- Liens vers les feuilles de style CSS et les icônes FontAwesome -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./View/Home/Lobby.css">
    <link rel="stylesheet" href="./View/Home/Login.css">
    <script src="./View/Home/Lobby.js" defer></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #f39c12;
            color: white;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: opacity 0.5s, visibility 0.5s, transform 0.5s;
            font-family: 'Roboto', sans-serif;
        }

        .notification.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .notification a {
            color: #fff;
            text-decoration: underline;
            font-weight: bold;
            margin-left: 10px;
        }

        .notification button {
            background: transparent;
            border: none;
            color: white;
            font-size: 1.5rem;
            margin-left: 20px;
            cursor: pointer;
        }

        .password-container i {
            color: #005c97;
        }

    </style>
</head>
<body>
<!-- Navigation principale avec logo et interrupteurs pour les paramètres de langue et de thème -->
<nav class="navbar">
    <!-- Partie gauche avec logo et nom de l'application -->
    <div class="navbar-left">
        <img src="Resources/LPS 1.0.png" alt="Logo" class="logo"/>
        <span class="app-name"><?= $translations['titre_appli'] ?></span>
    </div>
    <!-- Partie droite avec contrôles pour les préférences de l'utilisateur -->
    <div class="navbar-right">
        <?php
        include 'Model/LanguageSelection.php';
        ?>
        <label class="switch">
            <input type="checkbox" id="theme-switch" onchange="toggleTheme()">
            <span class="slider round">
                <span class="switch-sticker switch-sticker-right">🌙</span> <!-- Sticker Dark Mode -->
                <span class="switch-sticker">☀️</span>
            </span>
        </label>
    </div>
</nav>

<article>
    <!-- Contenu principal avec une introduction et formulaire de connexion -->
    <div class="main-content">

        <h1 class="main-heading"><?= $translations['welcome_message'] ?><br> <?= $translations['welcome_message2'] ?></h1>
        <p class="sub-text">
            <?= $translations['description_index'] ?>
        </p>
        <!-- Formulaire de connexion -->
        <div class="login-container">
            <h2><?= $translations['connexion_index'] ?></h2>
            <?php if (!empty($errorMessage)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="email"><?= $translations['email_index'] ?></label>
                    <input type="text" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password"><?= $translations['mdp_index'] ?></label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" required>
                        <i class="fas fa-eye" id="togglePassword" style="cursor: pointer;"></i>
                    </div>
                </div>

                <!--<div class="g-recaptcha" data-sitekey="6LfFBNEqAAAAAEp-LTer6T6GICYukcpXLQXPjNgg"></div>-->

                <button class="primary-button" type="submit"><?= $translations['connected_index'] ?></button>
                <p><?= $translations['connexion_problem']?></p>
                <a href="Presentation/ForgotPasswordMail.php"><?= $translations['changed_mdp_index'] ?></a>
            </form>
        </div>
        <!-- Liens pour les utilisateurs non connectés -->
        <div class="button-group">
            <p style="font-size: large"><b><?= $translations['ou']?></b></p>
            <button class="secondary-button"><a class="login-link" href="Presentation/AccountCreation.php"><?= $translations['register_button_index'] ?></a></button>
        </div>
    </div>
</article>
<!-- Уведомление -->
<div class="notification" id="emailVerificationNotification">
    <?= $translations['validate_email_index'] ?> <a href="Presentation/EmailValidationNotice.php"><?= $translations['register_button_index_button'] ?></a>
    <button onclick="closeNotification()">&times;</button>
</div>
<?php include './View/Footer.php'; ?>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (getCookie('email_verification_pending') === '1') {
            var notification = document.getElementById('emailVerificationNotification');
            notification.classList.add('show');
            document.cookie = "email_verification_pending=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        }

        // Gestion du toggle de mot de passe
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('password');

        togglePassword.addEventListener('click', function () {
            // Toggle le type de l'input entre 'password' et 'text'
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);

            // Toggle l'icône entre fa-eye et fa-eye-slash
            this.classList.toggle('fa-eye-slash');
        });
    });

    function getCookie(name) {
        var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        if (match) return match[2];
        return null;
    }

    function closeNotification() {
        var notification = document.getElementById('emailVerificationNotification');
        notification.classList.remove('show');
    }

    sessionStorage.setItem("lastPage", "index.php");

</script>
</body>
</html>
