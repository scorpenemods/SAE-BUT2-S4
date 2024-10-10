<?php
global $database;
session_start();
require "../Model/Database.php";
require "../Model/Person.php";

$database = new Database();

// Vérification de l'objet Person
$userName = "Guest";
$senderId = $_SESSION['user_id'] ?? null;
if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    if ($person instanceof Person) {
        $userName = htmlspecialchars($person->getPrenom()) . ' ' . htmlspecialchars($person->getNom());
        $senderId = $person->getUserId(); // Récupération de l'ID utilisateur pour l'envoi de messages
    }
} else {
    header("Location: Logout.php");
    exit();
}

$userRole = $person->getRole(); // Récupération du rôle utilisateur
date_default_timezone_set('Europe/Paris');

// Restriction d'accès selon les rôles (ici pour les professeurs)
$allowedRoles = [2]; // Rôle 2 correspond aux professeurs
if (!in_array($userRole, $allowedRoles)) {
    header("Location: AccessDenied.php");  // Redirection vers une page de refus d'accès
    exit();
}

// Gestion des sections actives via l'URL et la session
if (isset($_GET['section'])) {
    $_SESSION['active_section'] = $_GET['section'];
}

// Définit la section active par défaut (Accueil) si aucune n'est spécifiée
$activeSection = isset($_SESSION['active_section']) ? $_SESSION['active_section'] : '0';

// ID du destinataire (à ajuster dynamiquement selon le contact)
$receiverId = $_POST['receiver_id'] ?? 1;
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
        <span onclick="window.location.href='Professor.php?section=0'" class="widget-button <?php echo $activeSection == '0' ? 'Current' : ''; ?>">Accueil</span>
        <span onclick="window.location.href='Professor.php?section=1'" class="widget-button <?php echo $activeSection == '1' ? 'Current' : ''; ?>">Messagerie</span>
        <span onclick="window.location.href='Professor.php?section=2'" class="widget-button <?php echo $activeSection == '2' ? 'Current' : ''; ?>">Gestion Étudiants</span>
        <span onclick="window.location.href='Professor.php?section=3'" class="widget-button <?php echo $activeSection == '3' ? 'Current' : ''; ?>">Documents</span>
        <span onclick="window.location.href='Professor.php?section=4'" class="widget-button <?php echo $activeSection == '4' ? 'Current' : ''; ?>">Livret de suivi</span>
    </nav>
    <div class="Contenus">
        <!-- Contenu Accueil -->
        <div class="Contenu <?php echo $activeSection == '0' ? 'Visible' : ''; ?>" id="content-0">
            <h2>Bienvenue sur la plateforme pour Professeurs!</h2><br>
            <p>Gérez les étudiants, suivez leur progression et communiquez facilement avec eux.</p><br>
        </div>

        <!-- Contenu Messagerie -->
        <div class="Contenu <?php echo $activeSection == '1' ? 'Visible' : ''; ?>" id="content-1">
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

                        // Fonction pour formater les horodatages
                        require_once '../Model/utils.php';

                        // Affichage des messages
                        foreach ($messages as $msg) {
                            $messageClass = ($msg['sender_id'] == $senderId) ? 'self' : 'other'; // Détermination de la classe en fonction de l'expéditeur
                            echo "<div class='message $messageClass' data-message-id='" . htmlspecialchars($msg['id']) . "'>";
                            echo "<p>" . htmlspecialchars($msg['contenu']) . "</p>"; // Protection XSS
                            if ($msg['file_path']) {
                                $fileUrl = htmlspecialchars(str_replace("../", "/", $msg['file_path']));
                                echo "<a href='" . $fileUrl . "' download>Télécharger le fichier</a>";
                            }
                            // Affichage du timestamp formaté
                            echo "<div class='timestamp-container'><span class='timestamp'>" . formatTimestamp($msg['timestamp']) . "</span></div>";
                            echo "</div>";
                        }
                        ?>
                    </div>
                    <div class="chat-footer">
                        <form id="messageForm" enctype="multipart/form-data" method="POST" action="SendMessage.php">
                            <input type="file" id="file-input" name="file" style="display:none">
                            <button type="button" class="attach-button" onclick="document.getElementById('file-input').click();">📎</button>
                            <input type="hidden" name="receiver_id" value="<?php echo $receiverId; ?>">
                            <input type="text" id="message-input" name="message" placeholder="Tapez un message...">
                            <button type="button" onclick="sendMessage(event)">Envoyer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenu Gestion Étudiants -->
        <div class="Contenu <?php echo $activeSection == '2' ? 'Visible' : ''; ?>" id="content-2">Contenu Gestion Étudiants</div>

        <!-- Contenu Documents -->
        <div class="Contenu <?php echo $activeSection == '3' ? 'Visible' : ''; ?>" id="content-3">Contenu Documents</div>

        <!-- Contenu Livret de suivi -->
        <div class="Contenu <?php echo $activeSection == '4' ? 'Visible' : ''; ?>" id="content-4">Contenu Livret de suivi</div>
    </div>
</section>

<footer class="PiedDePage">
    <img src="../Resources/Logo_UPHF.png" alt="Logo UPHF" width="10%">
    <a href="Redirection.php">Informations</a>
    <a href="Redirection.php">À propos</a>
</footer>

</body>
</html>
