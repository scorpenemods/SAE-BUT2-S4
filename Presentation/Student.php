<?php

// Démarre la session au début du script
session_start();

// Inclure les fichiers nécessaires pour les classes Database et Person
require_once "../Model/Database.php";
require_once "../Model/Person.php";

// Initialiser le nom d'utilisateur comme 'Guest' au cas où aucun utilisateur n'est connecté
$userName = "Guest";
// Définir le fuseau horaire sur Paris
date_default_timezone_set('Europe/Paris');

// Vérifie que l'utilisateur est connecté
if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    if ($person instanceof Person) {
        $userName = htmlspecialchars($person->getPrenom()) . ' ' . htmlspecialchars($person->getNom());
        $senderId = $person->getUserId(); // Récupère l'ID de l'utilisateur
    }
} else {
    header("Location: Logout.php");
    exit();
}

// Instanciation de l'objet Database
$database = (Database::getInstance());

// Récupération des préférences de l'utilisateur
$preferences = $database->getUserPreferences($senderId);
$darkmode = isset($preferences['darkmode']) && $preferences['darkmode'] == 1 ? 'checked' : ''; // Gestion du mode sombre

if (isset($_GET['section'])) {
    $_SESSION['active_section'] = $_GET['section'];
}
// Définit la section active par défaut (Accueil) si aucune n'est spécifiée
$activeSection = isset($_SESSION['active_section']) ? $_SESSION['active_section'] : '0';

// Récupération des messages entre l'utilisateur actuel et le destinataire
$receiverId = 2; // À définir dynamiquement
$messages = $database->getMessages($senderId, $receiverId);

$students = $database->getStudents(7);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Petit Stage</title>
    <link rel="stylesheet" href="/View/Principal/Principal.css">
    <script src="/View/Principal/Principal.js"></script>
    <style>
        /* Mode sombre dynamiquement */
        body.dark-mode {
            background-color: #121212;
            color: white;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Appliquer le mode sombre si activé dans les préférences
            let darkModeEnabled = "<?php echo $darkmode; ?>" === 'checked';
            if (darkModeEnabled) {
                document.body.classList.add('dark-mode');
                document.getElementById('theme-switch').checked = true; // Coche le switch pour le mode sombre
            }

            // Gestion du toggle du mode sombre
            document.getElementById('theme-switch').addEventListener('change', function () {
                if (this.checked) {
                    document.body.classList.add('dark-mode');
                } else {
                    document.body.classList.remove('dark-mode');
                }
            });
        });

    </script>

</head>
<body>
<header class="navbar">
    <div class="navbar-left">
        <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
        <span class="app-name">Le Petit Stage</span>
    </div>
    <div class="navbar-right">
        <div id="notification-icon" class="notification-icon">
            <img src="../Resources/Notif.png" alt="Notifications">
            <span id="notification-count" class="notification-count"></span>
        </div>
        <button class="mainbtn">
            <p><?php echo $userName; ?></p>
        </button>
        <!-- Language Switch -->
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

<section class="Menus">
    <nav>
        <span onclick="window.location.href='Student.php?section=0'" class="widget-button <?php echo $activeSection == '0' ? 'Current' : '0'; ?>">Accueil</span>
        <span onclick="window.location.href='Student.php?section=6'" class="widget-button <?php echo $activeSection == '' ? 'Current' : '1'; ?>">Missions de stage</span>
        <span onclick="window.location.href='Student.php?section=4'" class="widget-button <?php echo $activeSection == '' ? 'Current' : '2'; ?>">Livret de suivi</span>
        <span onclick="window.location.href='Student.php?section=2'" class="widget-button <?php echo $activeSection == '' ? 'Current' : '3'; ?>">Offres</span>
        <span onclick="window.location.href='Student.php?section=3'" class="widget-button <?php echo $activeSection == '' ? 'Current' : '4'; ?>">Documents</span>
        <span onclick="window.location.href='Student.php?section=1'" class="widget-button <?php echo $activeSection == '' ? 'Current' : '5'; ?>">Messagerie</span>
        <span onclick="window.location.href='Student.php?section=5'" class="widget-button <?php echo $activeSection == '' ? 'Current' : '6'; ?>">Notes</span>
    </nav>
    <div class="Contenus">
        <!-- Accueil Content -->
        <div class="Contenu <?php echo $activeSection == '0' ? 'Visible' : ''; ?>" id="content-0">
            <h2>Bienvenue à Le Petit Stage!</h2><br>
            <p>
                Cette application est conçue pour faciliter la gestion des stages pour les étudiants de l'UPHF, les enseignants, les tuteurs et le secrétariat.
            </p><br>
            <ul>
                <li><strong>Livret de suivi:</strong> Suivez votre progression et recevez des retours de votre tuteur ou enseignant.</li><br>
                <li><strong>Offres de stage:</strong> Consultez les offres de stage disponibles et postulez directement.</li><br>
                <li><strong>Documents:</strong> Téléchargez et partagez des documents nécessaires pour votre stage.</li><br>
                <li><strong>Messagerie:</strong> Communiquez facilement avec votre tuteur, enseignant, ou autres contacts.</li><br>
            </ul><br>
        </div>


        <!-- Missions Content -->
        <div class="Contenu <?php echo $activeSection == '6' ? 'Visible' : ''; ?>" id="content-6">Contenu Missions</div>

        <!-- Messagerie Content -->
        <div class="Contenu <?php echo $activeSection == '1' ? 'Visible' : ''; ?>" id="content-1">
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

        <!-- Offres Content -->
        <div class="Contenu <?php echo $activeSection == '2' ? 'Visible' : ''; ?>" id="content-2">Contenu Offres</div>

        <!-- Documents Content -->
        <div class="Contenu <?php echo $activeSection == '3' ? 'Visible' : ''; ?>" id="content-3">Contenu Documents</div>

        <!-- Livret de suivi Content -->
        <div class="Contenu <?php echo $activeSection == '4' ? 'Visible' : ''; ?>" id="content-4">Contenu Livret de suivi</div>
        <!-- Notes Content -->
        <div class="Contenu <?php echo $activeSection == '5' ? 'Visible' : ''; ?>" id="content-5">
            <div class="notes-container">
                <table class="notes-table">
                    <tr class="lsttitlenotes">
                        <th>Sujet</th>
                        <th>Appréciation</th>
                        <th>Note</th>
                    </tr>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student->getPrenom()); ?></td>
                        <td><?php echo htmlspecialchars($student->getPrenom()); ?></td>
                        <td><?php echo htmlspecialchars($student->getPrenom()); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</section>

<footer class="PiedDePage">
    <img src="../Resources/Logo_UPHF.png" alt="Logo UPHF" width="10%">
    <a href="Redirection.php">Informations</a>
    <a href="Redirection.php">À propos</a>
</footer>
<script src="/View/Principal/deleteMessage.js"></script>
</body>
</html>
