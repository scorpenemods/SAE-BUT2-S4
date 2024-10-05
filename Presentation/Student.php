<?php

global $database;
session_start(); // Start the session at the beginning of the script

require_once "../Model/Database.php"; // Assuming your Person class is here, or included in Database.php
require_once "../Model/Person.php"; // Ensure Person class is correctly included

// Initialize username as Guest in case no user is logged in
$userName = "Guest";
date_default_timezone_set('Europe/Paris');

// Проверка, что пользователь залогинен
if (!isset($_SESSION['user'])) {
    header("Location: Logout.php");
    exit();
}

// Получение данных пользователя из сессии
$person = unserialize($_SESSION['user']);
$userName = $person->getPrenom() . ' ' . $person->getNom();
$senderId = $person->getUserId();  // ID текущего пользователя
$userRole = $person->getRole(); // Получение роли пользователя

// Ограничение доступа по ролям (настройте в зависимости от ролей)
$allowedRoles = [1]; // Здесь указаны роли, которым разрешен доступ к странице. Например, роль 2 — преподаватель.
if (!in_array($userRole, $allowedRoles)) {
    header("Location: access_denied.php");  // Перенаправление на страницу отказа в доступе
    exit();
}

$receiverId = 2; // Установите динамически, на основе выбранного контакта в мессенджере

$database = new Database();
$messages = $database->getMessages($senderId, $receiverId); // Получение сообщений между пользователями
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Petit Stage</title>
    <link rel="stylesheet" href="/View/Principal/Principal.css">
    <script src="/View/Principal/Principal.js" defer></script>
    <script src="/View/Principal/deleteMessage.js" defer></script>
</head>
<body>
<header class="navbar">
    <div class="navbar-left">
        <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
        <span class="app-name">Le Petit Stage</span>
    </div>
    <div class="navbar-right">
        <button class="mainbtn" onclick="toggleMenu()">
            <p><?php echo $userName; ?></p>
        </button>
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
                <span class="switch-sticker switch-sticker-right">🌙</span> <!-- Sticker Dark Mode -->
                <span class="switch-sticker">☀️</span> <!-- Sticker Light Mode -->
            </span>
        </label>
        <!--
        <button class="mainbtn" onclick="toggleMenu()">
            <img src="../Resources/Param.png" alt="Settings">
        </button>
        -->
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
        <span onclick="widget(2)" class="widget-button">Offres</span>
        <span onclick="widget(3)" class="widget-button">Documents</span>
        <span onclick="widget(4)" class="widget-button">Livret de suivi</span>
    </nav>
    <div class="Contenus">
        <!-- Accueil Content -->
        <div class="Visible" id="content-0">
            <h2>Bienvenue à Le Petit Stage!</h2><br>
            <p>
                Cette application est conçue pour faciliter la gestion des stages pour les étudiants de l'UPHF, les enseignants, les tuteurs et le secrétariat.
                Voici ce que vous pouvez faire :
            </p><br>
            <ul>
                <li><strong>Messagerie:</strong> Communiquez facilement avec votre tuteur, enseignant, ou autres contacts.</li><br>
                <li><strong>Offres de stage:</strong> Consultez les offres de stage disponibles et postulez directement.</li><br>
                <li><strong>Documents:</strong> Téléchargez et partagez des documents nécessaires pour votre stage.</li><br>
                <li><strong>Livret de suivi:</strong> Suivez votre progression et recevez des retours de votre tuteur ou enseignant.</li><br>
            </ul><br>
        </div>

        <!-- Messenger Content -->
        <div class="Contenu" id="content-1">
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

                <!-- Right click for delete -->
                <div id="context-menu" class="context-menu">
                    <ul>
                        <li id="copy-text">Copy</li>
                        <li id="delete-message">Delete</li>
                    </ul>
                </div>

                <div class="chat-window">
                    <div class="chat-header">
                        <h3 id="chat-header-title">Chat avec Contact 1</h3>
                    </div>
                    <div class="chat-body" id="chat-body">
                        <?php
                        // Функция для форматирования даты
                        function formatTimestamp($timestamp) {
                            $date = new DateTime($timestamp);
                            $now = new DateTime();
                            $yesterday = new DateTime('yesterday');

                            // Сравнение даты сообщения с сегодняшней датой
                            if ($date->format('Y-m-d') == $now->format('Y-m-d')) {
                                return 'Today ' . $date->format('H:i');
                            }
                            // Сравнение даты сообщения со вчерашней датой
                            elseif ($date->format('Y-m-d') == $yesterday->format('Y-m-d')) {
                                return 'Yesterday ' . $date->format('H:i');
                            } else {
                                return $date->format('d.m.Y H:i'); // Короткий формат даты и времени
                            }
                        }

                        // Пример использования в вашем цикле для вывода сообщений
                        foreach ($messages as $msg) {
                            $messageClass = ($msg['sender_id'] == $senderId) ? 'self' : 'other'; // Определение класса в зависимости от отправителя
                            echo "<div class='message $messageClass' data-message-id='" . htmlspecialchars($msg['id']) . "'>";
                            echo "<p>" . htmlspecialchars($msg['contenu']) . "</p>"; // Защита от XSS
                            if ($msg['file_path']) {
                                $fileUrl = htmlspecialchars(str_replace("../", "/", $msg['file_path']));
                                echo "<a href='" . $fileUrl . "' download>Télécharger le fichier</a>";
                            }
                            // Используем функцию formatTimestamp для вывода форматированной даты и времени
                            echo "<div class='timestamp-container'><span class='timestamp'>" . formatTimestamp($msg['timestamp']) . "</span></div>";
                            echo "</div>";
                        }
                        ?>
                    </div>
                    <div class="chat-footer">
                        <form id="messageForm" enctype="multipart/form-data" method="POST" action="sendMessage.php">
                            <input type="file" id="file-input" name="file" style="display:none">
                            <button type="button" class="attach-button" onclick="document.getElementById('file-input').click();">📎</button>
                            <input type="hidden" name="receiver_id" value="2"> <!-- Замените на нужный ID -->
                            <input type="text" id="message-input" name="message" placeholder="Tapez un message...">
                            <button type="button" onclick="sendMessage(event)">Envoyer</button> <!-- dynamic messages sending -->
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Offres Content -->
        <div class="Contenu" id="content-2">Contenu Offres</div>

        <!-- Documents Content -->
        <div class="Contenu" id="content-3">Contenu Documents</div>

        <!-- Livret de suivi Content -->
        <div class="Contenu" id="content-4">Contenu Livret de suivi</div>
    </div>
</section>

<footer class="PiedDePage">
    <img src="../Resources/Logo_UPHF.png" alt="Logo UPHF" width="10%">
    <a href="Redirection.php">Informations</a>
    <a href="Redirection.php">À propos</a>
</footer>

</body>
</html>