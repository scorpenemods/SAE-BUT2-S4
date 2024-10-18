<?php
global $database;
session_start();
require "../Model/Database.php";
require "../Model/Person.php";

$database = new Database();

// Убедитесь, что объект Person загружен
$userName = "Guest";
$senderId = $_SESSION['user_id'] ?? null;
if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    if ($person instanceof Person) {
        $userName = htmlspecialchars($person->getPrenom()) . ' ' . htmlspecialchars($person->getNom());
        $senderId = $person->getUserId(); // Получаем ID пользователя для отправки сообщений
    }
} else {
    header("Location: Logout.php");
    exit();
}

$userRole = $person->getRole(); // Получение роли пользователя
date_default_timezone_set('Europe/Paris');

// Ограничение доступа по ролям (настройте в зависимости от ролей)
$allowedRoles = [2]; // Здесь указаны роли, которым разрешен доступ к странице. Например, роль 2 — преподаватель.
if (!in_array($userRole, $allowedRoles)) {
    header("Location: AccessDenied.php");  // Перенаправление на страницу отказа в доступе
    exit();
}

// Предполагаемый ID получателя (настроить динамически в зависимости от контакта)
$receiverId = $_POST['receiver_id'] ?? 1; // Замените на динамическое значение

$students = $database->getStudents($senderId);


// Récupérer les préférences de l'utilisateur
$preferences = $database->getUserPreferences($person->getUserId());

// Vérifier si le mode sombre est activé dans les préférences
$darkModeEnabled = isset($preferences['darkmode']) && $preferences['darkmode'] == 1 ? true : false;
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Petit Stage - Professeur</title>
    <link rel="stylesheet" href="../View/Principal/Principal.css">
    <script src="../View/Principal/Principal.js" defer></script>
    <script src="../View/Principal/deleteMessage.js" defer></script>
</head>

<body class="<?php echo $darkModeEnabled ? 'dark-mode' : ''; ?>">
<header class="navbar">
    <div class="navbar-left">
        <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
        <span class="app-name">Le Petit Stage - Professeur</span>
    </div>
    <div class="navbar-right">
        <button class="mainbtn" >
            <img src="../Resources/Notif.png" alt="Settings">
        </button>
        <p><?php echo $userName; ?></p>
        <label class="switch">
            <input type="checkbox" id="language-switch" onchange="toggleLanguage()">
            <span class="slider round">
                <span class="switch-sticker">🇫🇷</span>
                <span class="switch-sticker switch-sticker-right">🇬🇧</span>
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

<div class="sidebar-toggle" id="sidebar-toggle">&#11166;</div>
<div class="sidebar" id="sidebar">
    <div class="search">
        <input type="text" id="search-input" placeholder="Search" onkeyup="searchStudents()">
    </div>
    <div class="students">
        <?php foreach ($students as $student): ?>
            <div class="student">
                <span><?php echo htmlspecialchars($student->getPrenom()) . ' ' . htmlspecialchars($student->getNom()); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<section class="Menus">
    <nav>
        <span onclick="widget(0)" class="widget-button Current" id="content-0">Accueil</span>
        <span onclick="widget(1)" class="widget-button" id="content-1">Gestion Étudiants</span>
        <span onclick="widget(2)" class="widget-button" id="content-2">Livret de suivi</span>
        <span onclick="widget(3)" class="widget-button" id="content-3">Documents</span>
        <span onclick="widget(4)" class="widget-button" id="content-4">Messagerie</span>



    </nav>
    <div class="Contenus">
        <div class="Visible" id="content-0">
            <h2>Bienvenue sur la plateforme pour Professeurs!</h2><br>
            <p>Gérez les étudiants, suivez leur progression et communiquez facilement avec eux.</p><br>
        </div>
        <div class="Contenu" id="content-1">Contenu Gestion Étudiants</div>
        <div class="Contenu" id="content-2">Contenu Livret de suivi</div>
        <div class="Contenu" id="content-3">Contenu Documents</div>
        <div class="Contenu" id="content-4">
            <!-- Содержимое мессенджера -->
            <div class="messenger">
                <div class="contacts">
                    <div class="search-bar">
                        <label for="search-input"></label><input type="text" id="search-input" placeholder="Rechercher des contacts..." onkeyup="searchContacts()">
                    </div>
                    <h3>Contacts</h3>
                    <ul id="contacts-list">
                        <?php
                        // Récupérer les contacts associés à l'utilisateur connecté
                        $userId = $person->getUserId();
                        $contacts = $database->getGroupContacts($userId);

                        foreach ($contacts as $contact) {
                            echo '<li data-contact-id="' . $contact['id'] . '" onclick="openChat(' . $contact['id'] . ', \'' . htmlspecialchars($contact['prenom'] . ' ' . $contact['nom']) . '\')">';
                            echo htmlspecialchars($contact['prenom'] . ' ' . $contact['nom']);
                            echo '</li>';
                        }
                        ?>
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
                        <h3 id="chat-header-title">Chat avec Contact </h3>
                    </div>
                    <div class="chat-body" id="chat-body">
                        <?php
                        if (!$senderId) {
                            die("Erreur: ID de l'utilisateur n'est pas défini dans la session.");
                        }
                        $messages = $database->getMessages($senderId, $receiverId);
                        // Функция для форматирования даты
                        require_once "../Model/utils.php";

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
                        <form id="messageForm" enctype="multipart/form-data" method="POST" action="SendMessage.php">
                            <input type="file" id="file-input" name="file" style="display:none">
                            <button type="button" class="attach-button" onclick="document.getElementById('file-input').click();">📎</button>
                            <!-- Champ caché pour le destinataire -->
                            <input type="hidden" name="receiver_id" id="receiver_id" value=""> <!-- Ce champ sera mis à jour dynamiquement -->
                            <label for="message-input"></label><input type="text" id="message-input" name="message" placeholder="Tapez un message...">
                            <button type="button" onclick="sendMessage(event)">Envoyer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
</body>

<footer class="PiedDePage">
    <img src="../Resources/Logo_UPHF.png" alt="Logo UPHF" width="10%">
    <a href="Redirection.php">Informations</a>
    <a href="Redirection.php">À propos</a>
</footer>

</html>