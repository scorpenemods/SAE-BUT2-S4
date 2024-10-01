<?php
session_start();
require "../Model/Database.php";
require "../Model/Person.php";

$userName = "Guest";
if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    if ($person instanceof Person) {
        $userName = htmlspecialchars($person->getPrenom()) . ' ' . htmlspecialchars($person->getNom());
    }
} else {
    header("Location: Logout.php");
    exit();
}

$userRole = $person->getRole(); // Получение роли пользователя

// Ограничение доступа по ролям (настройте в зависимости от ролей)
$allowedRoles = [4]; // Здесь указаны роли, которым разрешен доступ к странице. Например, роль 2 — преподаватель.
if (!in_array($userRole, $allowedRoles)) {
    header("Location: access_denied.php");  // Перенаправление на страницу отказа в доступе
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Petit Stage - Maitre de Stage</title>
    <link rel="stylesheet" href="../View/Principal/Principal.css">
    <script src="../View/Principal/Principal.js" defer></script>
</head>
<body>
    <header class="navbar">
        <div class="navbar-left">
            <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
            <span class="app-name">Le Petit Stage - Maitre de Stage</span>
        </div>
        <div class="navbar-right">
            <p><?php echo $userName; ?></p>
            <!-- Language Switch -->
            <label class="switch">
                <input type="checkbox" id="language-switch" onchange="toggleLanguage()">
                <span class="slider round">
                    <span class="switch-sticker">🇫🇷</span>
                    <span class="switch-sticker switch-sticker-right">🇬🇧</span>
                </span>
            </label>
            <!-- Theme Switch -->
            <label class="switch">
                <input type="checkbox" id="theme-switch" onchange="toggleTheme()">
                <span class="slider round">
                    <span class="switch-sticker switch-sticker-right">🌙</span>
                    <span class="switch-sticker">☀️</span>
                </span>
            </label>
            <button class="mainbtn" onclick="toggleMenu()">
                <img src="../Resources/Param.png" alt="Settings">
            </button>
            <div class="hide-list" id="settingsMenu">
                <a href="Settings.php">Information</a>
                <a href="Logout.php">Deconnexion</a>
            </div>
        </div>
    </header>

    <section class="Menus">
        <nav>
            <span onclick="widget(0)" class="widget-button Current">Accueil</span>
            <span onclick="widget(1)" class="widget-button">Messagerie</span>
            <span onclick="widget(2)" class="widget-button">Gestion Stagiaires</span>
            <span onclick="widget(3)" class="widget-button">Documents</span>
            <span onclick="widget(4)" class="widget-button">Evaluation Stages</span>
        </nav>
        <div class="Contenus">
            <div class="Visible" id="content-0">
                <h2>Bienvenue sur la plateforme pour les Maitres de Stage!</h2><br>
                <p>Gérez vos stagiaires, communiquez facilement et suivez l'évolution de leurs compétences.</p><br>
            </div>
            <div class="Contenu" id="content-1">
                <!-- Содержимое мессенджера -->
            </div>
            <div class="Contenu" id="content-2">Contenu Gestion Stagiaires</div>
            <div class="Contenu" id="content-3">Contenu Documents</div>
            <div class="Contenu" id="content-4">Contenu Evaluation Stages</div>
        </div>
    </section>

    <footer class="PiedDePage">
        <img src="../Resources/Logo_UPHF.png" alt="Logo UPHF" width="10%">
        <a href="Redirection.php">Informations</a>
        <a href="Redirection.php">À propos</a>
    </footer>
</body>
</html>