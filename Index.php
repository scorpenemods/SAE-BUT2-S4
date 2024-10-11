<?php
// Démarrage d'une nouvelle session ou reprise d'une session existante
session_start();

// Inclusion des fichiers nécessaires pour accéder à la base de données et à la définition de la classe Person
require_once 'Model/Database.php';
require_once 'Model/Person.php';

// Création d'une nouvelle instance de la classe Database pour interagir avec la base de données
$database = new Database();
$errorMessage = '';

// Vérification si la méthode de la requête HTTP est POST, ce qui indique que le formulaire de connexion a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Appel de la méthode verifyLogin pour vérifier les identifiants de l'utilisateur
    $loginResult = $database->verifyLogin($username, $password);

    // Si la vérification est réussie et que $user est un tableau (signifiant un utilisateur valide), exécute le bloc suivant
    if ($loginResult['status'] === 'success') {
        $user = $loginResult['user'];

        $person = new Person(
            $user['nom'],
            $user['prenom'],
            $user['telephone'],
            $user['login'],
            $user['role'],
            $user['activite'],
            $user['email'],
            $user['id']
        );

        $_SESSION['user_id'] = $person->getUserId();
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
                header("Location: Presentation/Secretariat.php");
                break;
            default:
                header("Location: Presentation/Redirection.php");
                break;
        }
        exit();
    } elseif ($loginResult['status'] === 'email_not_validated') {
        $user = $loginResult['user'];
        setcookie('email_verification_pending', '1', time() + 3600, "/");
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
        header("Location: Index.php");
        exit();
    } elseif ($loginResult['status'] === 'pending') {
        $errorMessage = "Votre compte est en attente d'activation par l'administration.";
    } else {
        $errorMessage = 'Identifiants incorrects. Veuillez réessayer.';
    }

    $database->closeConnection();
}
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
        <span class="app-name">Le Petit Stage</span>
    </div>
    <!-- Partie droite avec contrôles pour les préférences de l'utilisateur -->
    <div class="navbar-right">
        <label class="switch">
            <input type="checkbox" id="language-switch" onchange="toggleLanguage()">
            <span class="slider round">
                <span class="switch-sticker">🇫🇷</span>
                <span class="switch-sticker switch-sticker-right">🇬🇧</span>
            </span>
        </label>
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
        <h1 class="main-heading">Vous êtes un étudiant en stage à UPHF?<br> Nous avons la solution!</h1>
        <p class="sub-text">
            Une application innovante pour les étudiants, enseignants et personnel de l'UPHF. Gérez vos stages et restez connectés avec toutes les parties prenantes facilement et efficacement.
        </p>
        <!-- Formulaire de connexion -->
        <div class="login-container">
            <h2>Connexion</h2>
            <?php if (!empty($errorMessage)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur :</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe :</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" required>
                        <i class="fas fa-eye" id="togglePassword" style="cursor: pointer;"></i>
                    </div>
                </div>
                <button class="primary-button" type="submit">Se connecter</button>
                <p>Un problème pour se connecter ?</p>
                <a href="Presentation/ForgotPasswordMail.php">Changer le mot de passe</a>
            </form>
        </div>
        <!-- Liens pour les utilisateurs non connectés -->
        <div class="button-group">
            <p style="font-size: large"><b>ou</b></p>
            <button class="secondary-button"><a class="login-link" href="Presentation/AccountCreation.php">S’enregistrer</a></button>
        </div>
    </div>
</article>

<!-- Уведомление -->
<div class="notification" id="emailVerificationNotification">
    Votre adresse email n'est pas validée. <a href="Presentation/EmailValidationNotice.php">Valider maintenant</a>
    <button onclick="closeNotification()">&times;</button>
</div>

<footer class="PiedDePage">
    <!-- Pied de page avec logo additionnel et liens -->
    <img src="Resources/Logo_UPHF.png" alt="Logo uphf" width="10%">
    <a href="Presentation/Redirection.php">Informations</a>
    <a href="Presentation/Redirection.php">A propos</a>
</footer>

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

</script>
</body>
</html>
