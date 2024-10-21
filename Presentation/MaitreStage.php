<?php
// Démarre une session pour gérer les informations de l'utilisateur connecté
session_start();

// Inclusion des classes nécessaires pour la base de données et l'utilisateur
require "../Model/Database.php";
require "../Model/Person.php";

// Initialisation de la connexion à la base de données
$database = new Database();

// Récupération de l'ID de l'utilisateur à partir de la session
$senderId = $_SESSION['user_id'] ?? null;

// Initialisation du nom de l'utilisateur par défaut (Guest) si non connecté
$userName = "Guest";

// Vérification si les informations de l'utilisateur existent dans la session
if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']); // Désérialise les données de session pour obtenir un objet `Person`

    // Vérifie si l'objet désérialisé est bien une instance de la classe `Person`
    if ($person instanceof Person) {
        // Récupère le prénom et le nom de l'utilisateur en protégeant contre les attaques XSS (Cross-Site Scripting)
        $userName = htmlspecialchars($person->getPrenom()) . ' ' . htmlspecialchars($person->getNom());
    }
} else {
    // Redirection vers la page de déconnexion si l'utilisateur n'est pas trouvé dans la session
    header("Location: Logout.php");
    exit();
}

// Récupération du rôle de l'utilisateur
$userRole = $person->getRole();

// Restriction d'accès basée sur les rôles d'utilisateur (ici, seuls certains rôles peuvent accéder à cette page)
$allowedRoles = [3]; // Par exemple, seul le rôle avec l'ID 3 est autorisé à accéder
if (!in_array($userRole, $allowedRoles)) {
    // Redirection vers une page d'accès refusé si l'utilisateur n'a pas le rôle autorisé
    header("Location: AccessDenied.php");
    exit();
}

// Récupération de l'ID du destinataire (statiquement défini ici à 1 pour l'exemple)
$receiverId = $_POST['receiver_id'] ?? 1;

// Récupération des préférences de l'utilisateur à partir de la base de données
$preferences = $database->getUserPreferences($person->getUserId());

// Vérification si le mode sombre est activé dans les préférences de l'utilisateur
$darkModeEnabled = isset($preferences['darkmode']) && $preferences['darkmode'] == 1 ? true : false;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Petit Stage - Maitre de Stage</title>
    <!-- Inclusion du fichier CSS pour la mise en page -->
    <link rel="stylesheet" href="../View/Principal/Principal.css">
    <!-- Inclusion du fichier JS pour les interactions -->
    <script src="../View/Principal/Principal.js" defer></script>
</head>
<body class="<?php echo $darkModeEnabled ? 'dark-mode' : ''; ?>"> <!-- Ajout de la classe 'dark-mode' si activée -->

<!-- Barre de navigation -->
<header class="navbar">
    <div class="navbar-left">
        <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
        <span class="app-name">Le Petit Stage - Maitre de Stage</span>
    </div>
    <div class="navbar-right">
        <button class="mainbtn">
            <img src="../Resources/Notif.png" alt="Settings">
        </button>
        <p><?php echo $userName; ?></p> <!-- Affichage du nom de l'utilisateur -->

        <!-- Commutateur de langue -->
        <label class="switch">
            <input type="checkbox" id="language-switch" onchange="toggleLanguage()">
            <span class="slider round">
                    <span class="switch-sticker">🇫🇷</span>
                    <span class="switch-sticker switch-sticker-right">🇬🇧</span>
                </span>
        </label>

        <!-- Bouton de paramètres -->
        <button class="mainbtn" onclick="toggleMenu()">
            <img src="../Resources/Param.png" alt="Settings">
        </button>

        <!-- Menu des paramètres caché par défaut -->
        <div class="hide-list" id="settingsMenu">
            <a href="Settings.php">Information</a>
            <a href="Logout.php">Déconnexion</a>
        </div>
    </div>
</header>

<!-- Section contenant les différents menus -->
<section class="Menus">
    <nav>
        <span onclick="widget(0)" class="widget-button Current" id="content-0">Accueil</span>
        <span onclick="widget(1)" class="widget-button" id="content-1">Missions de stage</span>
        <span onclick="widget(2)" class="widget-button" id="content-2">Gestion Stagiaires</span>
        <span onclick="widget(3)" class="widget-button" id="content-3">Evaluation Stages</span>
        <span onclick="widget(4)" class="widget-button" id="content-4">Documents</span>
        <span onclick="widget(5)" class="widget-button" id="content-5">Messagerie</span>
        <span onclick="widget(6)" class="widget-button" id="content-6">Notes</span>



    </nav>

    <div class="Contenus">
        <!-- Contenu de l'Accueil -->
        <div class="Visible" id="content-0">
            <h2>Bienvenue sur la plateforme pour les Maitres de Stage!</h2><br>
            <p>Gérez vos stagiaires, communiquez facilement et suivez l'évolution de leurs compétences.</p><br>
        </div>

        <!-- Contenu des autres sections -->
        <div class="Contenu" id="content-1">Missions de stage</div>
        <div class="Contenu" id="content-2">Contenu Gestion Stagiaires</div>
        <div class="Contenu" id="content-3">Contenu Evaluation Stages</div>
        <div class="Contenu" id="content-4">Contenu Documents</div>

        <!-- Contenu de la Messagerie -->
        <div class="Contenu" id="content-5">
            <div class="messenger">
                <!-- Barre de recherche de contacts -->
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

                <!-- Menu contextuel pour copier ou supprimer un message -->
                <div id="context-menu" class="context-menu">
                    <ul>
                        <li id="copy-text">Copier</li>
                        <li id="delete-message">Supprimer</li>
                    </ul>
                </div>

                <!-- Fenêtre de chat -->
                <div class="chat-window">
                    <div class="chat-header">
                        <h3 id="chat-header-title">Chat avec Contact </h3>
                    </div>

                    <div class="chat-body" id="chat-body">
                        <?php
                        // Si l'ID de l'utilisateur n'est pas défini, afficher une erreur et arrêter le script
                        if (!$senderId) {
                            die("Erreur: ID de l'utilisateur n'est pas défini dans la session.");
                        }

                        // Récupère les messages entre l'utilisateur et le destinataire
                        $messages = $database->getMessages($senderId, $receiverId);

                        // Fonction pour formater la date d'un message
                        require_once '../Model/utils.php';

                        // Boucle pour afficher les messages
                        foreach ($messages as $msg) {
                            // Détermine si le message a été envoyé par l'utilisateur ou reçu (classe CSS différente)
                            $messageClass = ($msg['sender_id'] == $senderId) ? 'self' : 'other';

                            // Affiche le message avec la protection contre les attaques XSS
                            echo "<div class='message $messageClass' data-message-id='" . htmlspecialchars($msg['id']) . "'>";
                            echo "<p>" . htmlspecialchars($msg['contenu']) . "</p>";

                            // Si le message contient un fichier joint, affiche un lien de téléchargement
                            if ($msg['file_path']) {
                                $fileUrl = htmlspecialchars(str_replace("../", "/", $msg['file_path']));
                                echo "<a href='" . $fileUrl . "' download>Télécharger le fichier</a>";
                            }

                            // Affiche la date et l'heure formatées du message
                            echo "<div class='timestamp-container'><span class='timestamp'>" . formatTimestamp($msg['timestamp']) . "</span></div>";
                            echo "</div>";
                        }
                        ?>
                    </div>

                    <!-- Zone de saisie pour envoyer un nouveau message -->
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

        <div class="Contenu" id="content-6">Contenu des notes</div>



    </div>
</section>

<!-- Pied de page -->
<footer class="PiedDePage">
    <img src="../Resources/Logo_UPHF.png" alt="Logo UPHF" width="10%">
    <a href="Redirection.php">Informations</a>
    <a href="Redirection.php">À propos</a>
</footer>
</body>
</html>
