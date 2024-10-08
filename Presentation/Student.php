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
if (!isset($_SESSION['user'])) {
    // Redirige vers la page de déconnexion si aucun utilisateur n'est connecté
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

// Mettez en place l'ID du destinataire dynamiquement, basé sur le contact sélectionné dans le messager
$receiverId = 2; // À définir dynamiquement

// Instanciation de l'objet Database
$database = new Database();
// Récupération des messages entre l'utilisateur actuel et le destinataire
$messages = $database->getMessages($senderId, $receiverId);
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
                        <label for="search-input"></label><input type="text" id="search-input" placeholder="Rechercher des contacts..." onkeyup="searchContacts()">
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
                        function formatTimestamp($timestamp) {
                            $date = new DateTime($timestamp); // Crée un objet DateTime à partir du timestamp
                            $now = new DateTime(); // Crée un objet DateTime pour la date actuelle
                            $yesterday = new DateTime('yesterday'); // Crée un objet DateTime pour la date d'hier

                            // Compare la date du message avec la date d'aujourd'hui
                            if ($date->format('Y-m-d') == $now->format('Y-m-d')) {
                                return 'Today ' . $date->format('H:i'); // Si c'est aujourd'hui, retourne "Today" avec l'heure
                            }
                            // Compare la date du message avec celle d'hier
                            elseif ($date->format('Y-m-d') == $yesterday->format('Y-m-d')) {
                                return 'Yesterday ' . $date->format('H:i'); // Si c'était hier, retourne "Yesterday" avec l'heure
                            } else {
                                return $date->format('d.m.Y H:i'); // Sinon, retourne la date complète au format jour/mois/année heure:minutes
                            }
                        }


                        // using loop to print messages
                        foreach ($messages as $msg) {
                            // Détermine la classe CSS en fonction de l'expéditeur du message
                            $messageClass = ($msg['sender_id'] == $senderId) ? 'self' : 'other'; // Utilise 'self' si l'utilisateur actuel est l'expéditeur, sinon 'other'

                            // Début de la construction du bloc de message
                            echo "<div class='message $messageClass' data-message-id='" . htmlspecialchars($msg['id']) . "'>";
                            echo "<p>" . htmlspecialchars($msg['contenu']) . "</p>"; // Affiche le contenu du message, sécurisé contre les attaques XSS

                            // Vérifie si un fichier est associé au message et crée un lien pour le télécharger
                            if ($msg['file_path']) {
                                $fileUrl = htmlspecialchars(str_replace("../", "/", $msg['file_path'])); // Nettoie le chemin du fichier
                                echo "<a href='" . $fileUrl . "' download>Télécharger le fichier</a>";
                            }

                            // Utilise la fonction formatTimestamp pour afficher la date et l'heure du message
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
                            <label for="message-input"></label><input type="text" id="message-input" name="message" placeholder="Tapez un message...">
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