<?php

session_start();  // Старт сессии

require_once 'Model/Database.php';

$database = new Database();
$errorMessage = '';

// Проверка, что форма была отправлена
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Верификация логина
    $isValid = $database->verifyLogin($username, $password);

    if ($isValid) {
        $person = $database->getPersonByUsername($username);
        $_SESSION['user_id'] = $person->getUserId(); // Сохранение user_id в сессию
        $_SESSION['user'] = serialize($person);  // Сериализация объекта пользователя
        $_SESSION['user_role'] = $person->getRole(); // Сохранение роли пользователя
        $_SESSION['user_name'] = $person->getPrenom() . ' ' . $person->getNom(); // Сохранение имени пользователя

        // Перенаправление на главную страницу в зависимости от роли
        switch ($_SESSION['user_role']) {
            case 1: // Student
                header("Location: Presentation/etudiant.php");
                break;
            case 2: // Professor
                header("Location: Presentation/professeur.php");
                break;
            case 3: // Professional Mentor
                header("Location: Presentation/maitreStage.php");
                break;
            case 4: // Secretariat
                header("Location: Presentation/secritariat.php");
                break;
            default:
                header("Location: Presentation/Redirection.php");
                break;
        }
    } else {
        $errorMessage = 'Identifiants incorrects. Veuillez réessayer.';
        $_SESSION['error'] = $errorMessage;
        header("Location: Index.php");
        exit();
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
    <link rel="stylesheet" href="View/Home/Lobby.css">
    <link rel="stylesheet" href="View/Home/Login.css">
    <script src="View/Home/Lobby.js" defer></script>
</head>
<body>
<nav class="navbar">
    <div class="navbar-left">
        <img src="Resources/LPS 1.0.png" alt="Logo" class="logo"/>
        <span class="app-name">Le Petit Stage</span>
    </div>
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

<script>
    window.onload = function() {
        var errorMessage = <?php echo json_encode($errorMessage); ?>;
        if (errorMessage) {
            alert(errorMessage);
        }
    };
</script>

<article>
    <div class="main-content">
        <h1 class="main-heading">Vous êtes un étudiant en stage à UPHF?<br> Nous avons la solution!</h1>
        <p class="sub-text">
            Une application innovante pour les étudiants, enseignants et personnel de l'UPHF. Gérez vos stages et restez connectés avec toutes les parties prenantes facilement et efficacement.
        </p>

        <div class="login-container">
            <h2>Connexion</h2>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur :</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe :</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button class="primary-button" type="submit">Se connecter</button>
                <p>Un problème pour se connecter ?</p>
                <a href="rebase/Model/ForgotPassword/ForgotPasswordMail.php">Changer le mot de passe</a>
            </form>
        </div>

        <div class="button-group">
            <p style="font-size: large"><b>ou</b></p>
            <button class="secondary-button"><a class="login-link" href="Presentation/AccountCreation.php">S’enregistrer</a></button>
        </div>
    </div>
</article>

<footer class="PiedDePage">
    <img src="Resources/Logo_UPHF.png" alt="Logo uphf" width="10%">
    <a href="Presentation/Redirection.php">Informations</a>
    <a href="Presentation/Redirection.php">A propos</a>
</footer>
</body>
</html>