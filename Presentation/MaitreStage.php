<?php
session_start();
require "../Model/Database.php";
require "../Model/Person.php";

$database = new Database();
$senderId = $_SESSION['user_id'] ?? null;

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

$userRole = $person->getRole(); // Récupération du rôle utilisateur

// Restriction d'accès selon les rôles
$allowedRoles = [3]; // Seuls les utilisateurs avec le rôle 3 (Maitre de Stage) ont accès
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
        <span onclick="window.location.href='MaitreStage.php?section=0'" class="widget-button <?php echo $activeSection == '0' ? 'Current' : ''; ?>">Accueil</span>
        <span onclick="window.location.href='MaitreStage.php?section=1'" class="widget-button <?php echo $activeSection == '1' ? 'Current' : ''; ?>">Messagerie</span>
        <span onclick="window.location.href='MaitreStage.php?section=2'" class="widget-button <?php echo $activeSection == '2' ? 'Current' : ''; ?>">Gestion Stagiaires</span>
        <span onclick="window.location.href='MaitreStage.php?section=3'" class="widget-button <?php echo $activeSection == '3' ? 'Current' : ''; ?>">Documents</span>
        <span onclick="window.location.href='MaitreStage.php?section=4'" class="widget-button <?php echo $activeSection == '4' ? 'Current' : ''; ?>">Evaluation Stages</span>
    </nav>
    <div class="Contenus">
        <!-- Accueil -->
        <div class="Contenu <?php echo $activeSection == '0' ? 'Visible' : ''; ?>" id="content-0">
            <h2>Bienvenue sur la plateforme pour les Maitres de Stage!</h2><br>
            <p>Gérez vos stagiaires, communiquez facilement et suivez l'évolution de leurs compétences.</p><br>
        </div>
        <!-- Messagerie -->
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
                        if (!$senderId) {
                            die("Erreur: ID de l'utilisateur n'est pas défini dans la session.");
                        }
                        $messages = $database->getMessages($senderId, $receiverId);

                        // Fonction pour formater l'horodatage
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
                            // Affichage de l'horodatage
                            echo "<div class='timestamp-container'><span class='timestamp'>" . formatTimestamp($msg['timestamp']) . "</span></div>";
                            echo "</div>";
                        }
                        ?>
                    </div>
                    <div class="chat-footer">
                        <form id="messageForm" enctype="multipart/form-data" method="POST" action="SendMessage.php">
                            <input type="file" id="file-input" name="file" style="display:none">
                            <button type="button" class="attach-button" onclick="document.getElementById('file-input').click();">📎</button>
                            <input type="hidden" name="receiver_id" value="<?php echo $receiverId; ?>"> <!-- ID du destinataire -->
                            <label for="message-input"></label>
                            <input type="text" id="message-input" name="message" placeholder="Tapez un message...">
                            <button type="button" onclick="sendMessage(event)">Envoyer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gestion Stagiaires -->
        <div class="Contenu <?php echo $activeSection == '2' ? 'Visible' : ''; ?>" id="content-2">Contenu Gestion Stagiaires</div>

        <!-- Documents -->
        <div class="Contenu <?php echo $activeSection == '3' ? 'Visible' : ''; ?>" id="content-3">Contenu Documents</div>

        <!-- Evaluation Stages -->
        <div class="Contenu <?php echo $activeSection == '4' ? 'Visible' : ''; ?>" id="content-4">Contenu Evaluation Stages</div>
    </div>
</section>

<footer class="PiedDePage">
    <img src="../Resources/Logo_UPHF.png" alt="Logo UPHF" width="10%">
    <a href="Redirection.php">Informations</a>
    <a href="Redirection.php">À propos</a>
</footer>
</body>
</html>
