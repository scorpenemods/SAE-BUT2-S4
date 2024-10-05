<?php
session_start();
require "../Model/Database.php";
require "../Model/Person.php";

$database = new Database();

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
$receiverId = $_POST['receiver_id'] ?? 1; // Замените на динамическое значение
$senderId = $_SESSION['user_id'] ?? null;

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
    <title>Le Petit Stage - Secrétariat</title>
    <link rel="stylesheet" href="../View/Principal/Principal.css">
    <script src="../View/Principal/Principal.js"></script>
</head>
<body>
<header class="navbar">
    <div class="navbar-left">
        <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
        <span class="app-name">Le Petit Stage - Secrétariat</span>
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

<section class="Menus">
    <nav>
        <span onclick="widget(0)" class="widget-button Current">Accueil</span>
        <span onclick="widget(1)" class="widget-button">Messagerie</span>
        <span onclick="widget(2)" class="widget-button">Gestion Utilisateurs</span>
        <span onclick="widget(3)" class="widget-button">Documents</span>
        <span onclick="widget(4)" class="widget-button">Rapports</span>
    </nav>
    <div class="Contenus">
        <div class="Visible" id="content-0">
            <h2>Bienvenue sur la plateforme Secrétariat!</h2><br>
            <p>Gérez les utilisateurs, consultez les documents et accédez aux rapports des stages.</p><br>
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
                        if (!$senderId) {
                            die("Erreur: ID de l'utilisateur n'est pas défini dans la session.");
                        }
                        $messages = $database->getMessages($senderId, $receiverId);
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
                            <input type="hidden" name="receiver_id" value="<?php echo $receiverId; ?>"> <!-- ID получателя -->
                            <input type="text" id="message-input" name="message" placeholder="Tapez un message...">
                            <button type="button" onclick="sendMessage(event)">Envoyer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="Contenu" id="content-2">
            <div class="user-management">
                <div class="pending-requests">
                    <h2>Demandes en attente</h2>
                    <!-- Здесь будут отображаться заявки на регистрацию -->
                    <?php
                    $pendingUsers = $database->getPendingUsers();
                    foreach ($pendingUsers as $user) {
                        echo "<div class='user-request'>";
                        echo "<p><strong>Nom:</strong> " . htmlspecialchars($user['nom']) . "</p>";
                        echo "<p><strong>Prénom:</strong> " . htmlspecialchars($user['prenom']) . "</p>";
                        echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</p>";
                        echo "<p><strong>Rôle:</strong> " . htmlspecialchars($user['role']) . "</p>";
                        echo "<button onclick='approveUser(" . $user['id'] . ")'>✅ Accepter</button>";
                        echo "<button onclick='rejectUser(" . $user['id'] . ")'>❌ Refuser</button>";
                        echo "</div>";
                    }
                    ?>
                </div>
                <div class="active-users">
                    <h2>Utilisateurs actifs</h2>
                    <!-- Здесь будет список активных пользователей -->
                    <?php
                    $activeUsers = $database->getActiveUsers();
                    foreach ($activeUsers as $user) {
                        echo "<div class='active-user'>";
                        echo "<p><strong>Nom:</strong> " . htmlspecialchars($user['nom']) . "</p>";
                        echo "<p><strong>Prénom:</strong> " . htmlspecialchars($user['prenom']) . "</p>";
                        echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</p>";
                        echo "<p><strong>Rôle:</strong> " . htmlspecialchars($user['role']) . "</p>";
                        echo "<button onclick='deleteUser(" . $user['id'] . ")'>🗑️ Supprimer</button>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="Contenu" id="content-3">Contenu Documents</div>
        <div class="Contenu" id="content-4">Contenu Rapports</div>
    </div>
</section>

<footer class="PiedDePage">
    <img src="../Resources/Logo_UPHF.png" alt="Logo UPHF" width="10%">
    <a href="Redirection.php">Informations</a>
    <a href="Redirection.php">À propos</a>
</footer>

<script src="../View/Principal/userManagement.js"></script>
</body>
</html>