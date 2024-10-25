<?php
global $database;
session_start();
require "../Model/Database.php";
require "../Model/Person.php";

$database = new Database();

$userName = "Guest";
$senderId = $_SESSION['user_id'] ?? null;
// Vérification de la session utilisateur
if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    if ($person instanceof Person) {
        $userName = htmlspecialchars($person->getPrenom()) . ' ' . htmlspecialchars($person->getNom());
        $userId = $person->getUserId(); // ID de l'utilisateur connecté
        $userRole = $person->getRole(); // Rôle de l'utilisateur
    } else {
        header("Location: Logout.php");
        exit();
    }
} else {
    header("Location: Logout.php");
    exit();
}

$userRole = $person->getRole(); // Получение роли пользователя
date_default_timezone_set('Europe/Paris');

// Vérification du rôle de l'utilisateur (ici, rôle 2 pour Professeur)
if ($userRole != 2) {
    header("Location: AccessDenied.php");
    exit();
}

$students = $database->getStudents($senderId);

// Récupérer les préférences de l'utilisateur
$preferences = $database->getUserPreferences($person->getUserId());

// Vérifier si le mode sombre est activé dans les préférences
$darkModeEnabled = isset($preferences['darkmode']) && $preferences['darkmode'] == 1 ? true : false;

if (isset($_GET['section'])) {
    $_SESSION['active_section'] = $_GET['section'];
}
// Définit la section active par défaut (Accueil) si aucune n'est spécifiée
$activeSection = isset($_SESSION['active_section']) ? $_SESSION['active_section'] : '0';

// Récupération des contacts (utilisateurs du même groupe)
$contacts = $database->getGroupContacts($userId);
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Petit Stage - Professeur</title>
    <link rel="stylesheet" href="../View/Principal/Principal.css">
    <script src="../View/Principal/Principal.js"></script>
</head>

<body class="<?php echo $darkModeEnabled ? 'dark-mode' : ''; ?>">
<header class="navbar">
    <div class="navbar-left">
        <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
        <span class="app-name">Le Petit Stage - Professeur</span>
    </div>
    <div class="navbar-right">
        <div id="notification-icon" class="notification-icon">
            <img src="../Resources/Notif.png" alt="Notifications">
            <span id="notification-count" class="notification-count"></span>
        </div>

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

<div class="sidebar-toggle" id="sidebar-toggle" onclick="sidebar()">&#11166;</div>
<div class="sidebar" id="sidebar">
    <div class="search">
        <input type="text" id="search-input" placeholder="Search" onkeyup="searchStudents()">
    </div>
    <div class="students">
        <?php foreach ($students as $student): ?>
            <div class="student" onclick="selectStudent(this)">
                <span><?php echo htmlspecialchars($student->getPrenom()) . ' ' . htmlspecialchars($student->getNom()); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<section class="Menus">
    <nav>
        <span onclick="window.location.href='Professor.php?section=0'" class="widget-button <?php echo $activeSection == '0' ? 'Current' : ''; ?>" id="content-0">Accueil</span>
        <span onclick="window.location.href='Professor.php?section=1'" class="widget-button <?php echo $activeSection == '1' ? 'Current' : ''; ?>" id="content-1">Missions de stage</span>
        <span onclick="window.location.href='Professor.php?section=2'" class="widget-button <?php echo $activeSection == '2' ? 'Current' : ''; ?>" id="content-2">Gestion Étudiants</span>
        <span onclick="window.location.href='Professor.php?section=3'" class="widget-button <?php echo $activeSection == '3' ? 'Current' : ''; ?>" id="content-3">Livret de suivi</span>
        <span onclick="window.location.href='Professor.php?section=4'" class="widget-button <?php echo $activeSection == '4' ? 'Current' : ''; ?>" id="content-4">Documents</span>
        <span onclick="window.location.href='Professor.php?section=5'" class="widget-button <?php echo $activeSection == '5' ? 'Current' : ''; ?>" id="content-5">Messagerie</span>
        <span onclick="window.location.href='Professor.php?section=6'" class="widget-button <?php echo $activeSection == '6' ? 'Current' : ''; ?>" id="content-6">Notes</span>
    </nav>
    <div class="Contenus">
        <div class="<?php echo ($activeSection == '0') ? 'Visible' : 'Contenu'; ?>" id="content-0">
            <h2>Bienvenue sur la plateforme pour Professeurs!</h2><br>
            <p>Gérez les étudiants, suivez leur progression et communiquez facilement avec eux.</p><br>
        </div>
        <div class="Contenu <?php echo ($activeSection == '1') ? 'Visible' : 'Contenu'; ?>" id="content-1">Contenu des missions de stage</div>
        <div class="Contenu <?php echo ($activeSection == '2') ? 'Visible' : 'Contenu'; ?>" id="content-2">Contenu Gestion Étudiants</div>
        <div class="Contenu <?php echo ($activeSection == '3') ? 'Visible' : 'Contenu'; ?>" id="content-3">Contenu Livret de suivi</div>
        <div class="Contenu <?php echo ($activeSection == '4') ? 'Visible' : 'Contenu'; ?>" id="content-4">Contenu Documents</div>
        <div class="Contenu <?php echo ($activeSection == '5') ? 'Visible' : 'Contenu'; ?>" id="content-5">
            <!-- Messagerie Content -->
            <div class="messenger">
                <div class="contacts">
                    <div class="search-bar">
                        <label for="search-input"></label>
                        <input type="text" id="search-input" placeholder="Rechercher des contacts..." onkeyup="searchContacts()">
                    </div>
                    <h3>Contacts</h3>
                    <ul id="contacts-list">
                        <?php
                        $roleMapping = [
                            1 => "Etudiant",
                            2 => "Professeur",
                            3 => "Maitre de stage"
                        ];

                        // Récupérer les contacts associés à l'utilisateur connecté
                        $userId = $person->getUserId();
                        $contacts = $database->getGroupContacts($userId);

                        // Sort contacts by role
                        usort($contacts, fn($a, $b) => $a['role'] <=> $b['role']);

                        // Group contacts by role
                        $groupedContacts = [];
                        foreach ($contacts as $contact) {
                            $roleName = $roleMapping[$contact['role']] ?? "Unknown Role";
                            $groupedContacts[$roleName][] = $contact;
                        }

                        // Display contacts grouped by role
                        foreach ($groupedContacts as $roleName => $contactsGroup) {
                            echo "<label><strong>$roleName :</strong></label>";
                            foreach ($contactsGroup as $contact) {
                                echo '<li data-contact-id="' . $contact['id'] . '" onclick="openChat(' . $contact['id'] . ', \'' . htmlspecialchars($contact['prenom'] . ' ' . $contact['nom']) . '\')">';
                                echo htmlspecialchars($contact['prenom'] . ' ' . $contact['nom']);
                                echo '</li>';
                            }
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
                        <!-- Les messages seront chargés dynamiquement via JavaScript -->
                    </div>

                    <div class="chat-footer">
                        <form id="messageForm" enctype="multipart/form-data" method="POST">
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
        <div class="Contenu <?php echo ($activeSection == '6') ? 'Visible' : 'Contenu'; ?>"" id="content-6">Contenu des notes</div>
    </div>
</section>
<script src="../View/Principal/deleteMessage.js"></script>
</body>

<footer class="PiedDePage">
    <img src="../Resources/Logo_UPHF.png" alt="Logo UPHF" width="10%">
    <a href="Redirection.php">Informations</a>
    <a href="Redirection.php">À propos</a>
</footer>

</html>