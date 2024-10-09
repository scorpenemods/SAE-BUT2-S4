<?php

// Démarre la session au début du script
session_start();

// Inclure les fichiers nécessaires pour les classes Database et Person
require_once "../Model/Database.php"; // On suppose que votre classe Person se trouve ici ou est incluse dans Database.php
require_once "../Model/Person.php"; // Assurez-vous que la classe Person est correctement incluse

// Initialiser le nom d'utilisateur comme 'Guest' au cas où aucun utilisateur n'est connecté
$userName = "Guest";
// Définir le fuseau horaire sur Paris
date_default_timezone_set('Europe/Paris');

// Vérifie que l'utilisateur est connecté
if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    if ($person instanceof Person) {
        $userName = htmlspecialchars($person->getPrenom()) . ' ' . htmlspecialchars($person->getNom());
        $senderId = $person->getUserId(); // ID utilisateur pour les messages
    }
} else {
    header("Location: Logout.php");
    exit();
}

// Récupération des données de l'utilisateur depuis la session
$person = unserialize($_SESSION['user']); // Désérialise l'objet utilisateur stocké dans la session
$userName = $person->getPrenom() . ' ' . $person->getNom(); // Construit le nom complet de l'utilisateur
$senderId = $person->getUserId();  // Récupère l'ID de l'utilisateur courant
$userRole = $person->getRole(); // Récupère le rôle de l'utilisateur

// Restreindre l'accès en fonction des rôles
$allowedRoles = [1]; // Définir les rôles autorisés à accéder à cette page
if (!in_array($userRole, $allowedRoles)) {
    // Rediriger vers la page de refus d'accès si le rôle de l'utilisateur n'est pas autorisé
    header("Location: AccessDenied.php");
    exit();
}

// Si un paramètre de section est passé dans l'URL, mettez à jour la session
if (isset($_GET['section'])) {
    $_SESSION['active_section'] = $_GET['section'];
}

// Définissez une section par défaut si aucune section n'est sélectionnée
$activeSection = isset($_SESSION['active_section']) ? $_SESSION['active_section'] : '0';

// Instanciation de l'objet Database
$database = new Database();

// Configuration du destinataire de la messagerie (dynamique)
$receiverId = 2; // À modifier dynamiquement

// Récupération des messages entre l'utilisateur actuel et le destinataire
$messages = $database->getMessages($senderId, $receiverId);

// Fonction pour formater l'horodatage
require_once '../Model/utils.php';
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
        <span onclick="window.location.href='Student.php?section=0'" class="widget-button <?php echo $activeSection == '0' ? 'Current' : ''; ?>">Accueil</span>
        <span onclick="window.location.href='Student.php?section=1'" class="widget-button <?php echo $activeSection == '1' ? 'Current' : ''; ?>">Messagerie</span>
        <span onclick="window.location.href='Student.php?section=2'" class="widget-button <?php echo $activeSection == '2' ? 'Current' : ''; ?>">Offres</span>
        <span onclick="window.location.href='Student.php?section=3'" class="widget-button <?php echo $activeSection == '3' ? 'Current' : ''; ?>">Documents</span>
        <span onclick="window.location.href='Student.php?section=4'" class="widget-button <?php echo $activeSection == '4' ? 'Current' : ''; ?>">Livret de suivi</span>
    </nav>
    <div class="Contenus">
        <!-- Accueil Content -->
        <div class="Contenu <?php echo $activeSection == '0' ? 'Visible' : ''; ?>" id="content-0">
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
            </ul>
        </div>

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
                        // using loop to print messages
                        foreach ($messages as $msg) {
                            // Détermine la classe CSS en fonction de l'expéditeur du message
                            $messageClass = ($msg['sender_id'] == $senderId) ? 'self' : 'other';

                            echo "<div class='message $messageClass' data-message-id='" . htmlspecialchars($msg['id']) . "'>";
                            echo "<p>" . htmlspecialchars($msg['contenu']) . "</p>";

                            if ($msg['file_path']) {
                                $fileUrl = htmlspecialchars(str_replace("../", "/", $msg['file_path']));
                                echo "<a href='" . $fileUrl . "' download>Télécharger le fichier</a>";
                            }

                            echo "<div class='timestamp-container'><span class='timestamp'>" . formatTimestamp($msg['timestamp']) . "</span></div>";
                            echo "</div>";
                        }
                        ?>
                    </div>
                    <div class="chat-footer">
                        <form id="messageForm" enctype="multipart/form-data" method="POST" action="SendMessage.php">
                            <input type="file" id="file-input" name="file" style="display:none">
                            <button type="button" class="attach-button" onclick="document.getElementById('file-input').click();">📎</button>
                            <input type="hidden" name="receiver_id" value="2"> <!-- need to change on dynamic ID -->
                            <label for="message-input"></label>
                            <input type="text" id="message-input" name="message" placeholder="Tapez un message...">
                            <button type="button" onclick="sendMessage(event)">Envoyer</button> <!-- dynamic messages sending -->
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Offres Content -->
        <div class="Contenu <?php echo $activeSection == '2' ? 'Visible' : ''; ?>" id="content-2">
            Contenu Offres
        </div>

        <!-- Documents Content -->
        <div class="Contenu <?php echo $activeSection == '3' ? 'Visible' : ''; ?>" id="content-3">
            Contenu Documents
        </div>

        <!-- Livret de suivi Content -->
        <div class="Contenu <?php echo $activeSection == '4' ? 'Visible' : ''; ?>" id="content-4">
            Contenu Livret de suivi
        </div>
    </div>
</section>

<footer class="PiedDePage">
    <img src="../Resources/Logo_UPHF.png" alt="Logo UPHF" width="10%">
    <a href="Redirection.php">Informations</a>
    <a href="Redirection.php">À propos</a>
</footer>

</body>
</html>
