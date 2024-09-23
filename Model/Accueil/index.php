<?php
// index.php
require '../../Class/Database.php';
session_start();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $db = new Database();

    $_POST['role'] = $db->execute('select role from a_usersae where username = $username');

    try {
        if ($db->authenticateUser($username, $password)) {
            // Successful login
            $_SESSION['user'] = $username;
            header('Location: /../Model/Login/Login.php');
            exit;
        } else {
            // Failed login
            $error = "Échec de la connexion. Vérifiez vos identifiants.";
        }
    } catch (Exception $e) {

    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Petit Stage</title>
    <link rel="stylesheet" href="lobby.css">
    <link rel="stylesheet" href="login.css">
    <script src="accueil.js" defer></script>
</head>
<body>
<!-- Navbar -->
<nav class="navbar">
    <div class="navbar-left">
        <img src="../../Ressources/LPS 1.0.png" alt="Logo" class="logo"/>
        <span class="app-name">Le Petit Stage</span>
    </div>
    <div class="navbar-right">
        <!-- Language Switch -->
        <label class="switch">
            <input type="checkbox" id="language-switch" onchange="toggleLanguage()">
            <span class="slider round">
                <span class="switch-sticker">🇫🇷</span> <!-- Sticker Français -->
                <span class="switch-sticker switch-sticker-right">🇬🇧</span> <!-- Sticker English -->
            </span>
        </label>
        <!-- Theme Switch -->
        <label class="switch">
            <input type="checkbox" id="theme-switch" onchange="toggleTheme()">
            <span class="slider round">
                <span class="switch-sticker">🌙</span> <!-- Sticker Dark Mode -->
                <span class="switch-sticker switch-sticker-right">☀️</span> <!-- Sticker Light Mode -->
            </span>
        </label>
    </div>
</nav>
<article>
<!-- Main Content -->
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
            <button class="primary-button" ><a class="login-link">Se connecter</a></button>
            <p>Un problème pour se connecter ?</p>
            <a href="../Parametre/Parametre/Parametre.php">Changer le mot de passe</a>
        </form>
    </div>

    <div class="button-group">
        <p style="font-size: large"><b>ou</b></p>
        <button class="secondary-button"><a class="login-link" href="../AccountCreation/AccountCreation.php">S’enregistrer</a></button>
    </div>
</div></article>
<footer class="PiedDePage">
    <img src="../../Ressources/Logo_UPHF.png" alt="Logo uphf" width="10%">
    <a href="../Redirection/Redirection.php">Informations</a>
    <a href="../Redirection/Redirection.php">A propos</a>
</footer>
</body>
</html>