<?php
global $database;
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Petit Stage - Professeur</title>
    <link rel="stylesheet" href="../View/Principal/Principal.css">
    <script src="../View/Principal/Principal.js" defer></script>
</head>

<body>
<header class="navbar">
    <div class="navbar-left">
        <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
        <span class="app-name">Le Petit Stage - Professeur</span>
    </div>
    <div class="navbar-right">
        <p><?php echo $userName; ?></p>
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

<div class="sidebar-toggle" id="sidebar-toggle">&#x25B6;</div>
<div class="sidebar" id="sidebar">
    <div class="search">
        <input type="text" placeholder="Search">
    </div>
    <div class="students">
        <div class="student">
            <span>Etudiant 1</span>
        </div>
        <div class="student selected">
            <span>Etudiant 2</span>
        </div>
        <div class="student">
            <span>Etudiant 3</span>
        </div>
        <div class="student">
            <span>Etudiant 4</span>
        </div>
    </div>
</div>

<section class="Menus">
    <nav>
        <span onclick="widget(0)" class="widget-button Current">Accueil</span>
        <span onclick="widget(1)" class="widget-button">Messagerie</span>
        <span onclick="widget(2)" class="widget-button">Gestion Étudiants</span>
        <span onclick="widget(3)" class="widget-button">Documents</span>
        <span onclick="widget(4)" class="widget-button">Livret de suivi</span>
    </nav>
    <div class="Contenus">
        <div class="Visible" id="content-0">
            <h2>Bienvenue sur la plateforme pour Professeurs!</h2><br>
            <p>Gérez les étudiants, suivez leur progression et communiquez facilement avec eux.</p><br>
        </div>
        <div class="Contenu" id="content-1">
            <!-- Содержимое мессенджера -->
            <div class="messenger">
                <div class="contacts">
                    <div class="search-bar">
                        <input type="text" id="search-input" placeholder="Rechercher des contacts..." onkeyup="searchContacts()">
                    </div>
                    <h3>Contacts</h3>
                    <ul id="contacts-list">
                        <li>Contact 1</li>
                        <li>Contact 2</li>
                        <li>Contact 3</li>
                    </ul>
                </div>
                <div class="chat-window">
                    <div class="chat-header">
                        <h3 id="chat-header-title">Chat avec Contact 1</h3>
                    </div>
                    <div class="chat-body" id="chat-body">
                        <?php
                        $database = new Database();
                        $senderId = $_SESSION['user_id'] ?? null; // Проверка наличия user_id в сессии
                        if (!$senderId) {
                            die("Ошибка: ID пользователя не установлен в сессии.");
                        }
                        $receiverId = 1; // ID получателя (установите значение в соответствии с текущим собеседником)
                        $messages = $database->getMessages($senderId, $receiverId);
                        foreach ($messages as $msg) {
                            echo "<div class='message'>";
                            echo "<p>" . htmlspecialchars($msg['contenu']) . "</p>"; // Защита от XSS
                            if ($msg['file_path']) {
                                echo "<a href='" . htmlspecialchars($msg['file_path']) . "' download>Скачать файл</a>";
                            }
                            echo "<span class='timestamp'>" . htmlspecialchars($msg['timestamp']) . "</span>";
                            echo "</div>";
                        }
                        ?>
                    </div>
                    <div class="chat-footer">
                        <form id="messageForm" enctype="multipart/form-data" method="POST" action="sendMessage.php">
                            <input type="file" id="file-input" name="file" style="display:none" onchange="document.getElementById('messageForm').submit();">
                            <button type="button" class="attach-button" onclick="document.getElementById('file-input').click();">📎</button>
                            <input type="hidden" name="receiver_id" value="<?php echo $receiverId; ?>"> <!-- ID получателя -->
                            <input type="text" id="message-input" name="message" placeholder="Tapez un message...">
                            <button type="submit" onclick="sendMessage()">Envoyer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="Contenu" id="content-2">Contenu Gestion Étudiants</div>
        <div class="Contenu" id="content-3">Contenu Documents</div>
        <div class="Contenu" id="content-4">Contenu Livret de suivi</div>
    </div>
</section>
</body>

<footer class="PiedDePage">
    <img src="../Resources/Logo_UPHF.png" alt="Logo UPHF" width="10%">
    <a href="Redirection.php">Informations</a>
    <a href="Redirection.php">À propos</a>
</footer>

</html>