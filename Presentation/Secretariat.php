<?php
// Démarre une nouvelle session ou reprend une session existante
session_start();

// Inclusion des fichiers nécessaires pour la base de données et les objets Person
require "../Model/Database.php";
require "../Model/Person.php";

// Création d'une nouvelle instance de la classe Database
$database = new Database();

// Initialisation du nom d'utilisateur par défaut
$userName = "Guest";

// Vérifie si l'utilisateur est connecté et récupère ses données
if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    // Vérifie si l'objet déserialisé est une instance de la classe Person
    if ($person instanceof Person) {
        // Sécurise et affiche le prénom et le nom de la personne connectée
        $userName = htmlspecialchars($person->getPrenom()) . ' Secretariat.php' . htmlspecialchars($person->getNom());
    }
} else {
    // Si aucune session d'utilisateur n'est trouvée, redirige vers la page de déconnexion
    header("Location: Logout.php");
    exit();
}

// Récupère le rôle de l'utilisateur et l'ID du destinataire des messages
$userRole = $person->getRole();
$receiverId = $_POST['receiver_id'] ?? 1; // ID du destinataire, valeur par défaut à 1 si non spécifié
$senderId = $_SESSION['user_id'] ?? null; // ID de l'expéditeur récupéré de la session

// Restriction d'accès selon les rôles
$allowedRoles = [4]; // Seuls les utilisateurs avec le rôle 4 ont accès à cette page
if (!in_array($userRole, $allowedRoles)) {
    // Redirection vers la page d'accès refusé si l'utilisateur n'a pas le bon rôle
    header("Location: AccessDenied.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Petit Stage - Secrétariat</title>
    <!-- Lien vers la feuille de style CSS principale -->
    <link rel="stylesheet" href="../View/Principal/Principal.css">
    <!-- Lien vers le script JavaScript principal -->
    <script src="../View/Principal/Principal.js"></script>
</head>
<body>
<header class="navbar">
    <div class="navbar-left">
        <!-- Affichage du logo et du nom de l'application -->
        <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
        <span class="app-name">Le Petit Stage - Secrétariat</span>
    </div>
    <div class="navbar-right">
        <!-- Affichage du nom de l'utilisateur connecté et contrôles pour changer la langue et le thème -->
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
        <!-- Bouton pour ouvrir le menu des paramètres -->
        <button class="mainbtn" onclick="toggleMenu()">
            <img src="../Resources/Param.png" alt="Settings">
        </button>
        <div class="hide-list" id="settingsMenu">
            <!-- Liens vers les pages d'informations et de déconnexion -->
            <a href="Settings.php">Information</a>
            <a href="Logout.php">Deconnexion</a>
        </div>
    </div>
</header>

<!-- Section principale contenant les différents modules de l'application -->
<section class="Menus">
    <nav>
        <!-- Boutons de navigation entre les différents contenus de la section -->
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
            <!-- Messenger Contents -->
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
                        if (!$senderId) {
                            die("Erreur: ID de l'utilisateur n'est pas défini dans la session.");
                        }
                        $messages = $database->getMessages($senderId, $receiverId);
                        // Function for formatting date
                        /**
                         * @throws Exception
                         */
                        function formatTimestamp($timestamp): string
                        {
                            $date = new DateTime($timestamp);
                            $now = new DateTime();
                            $yesterday = new DateTime('yesterday');

                            // Compare the date of the message with today's date
                            if ($date->format('Y-m-d') == $now->format('Y-m-d')) {
                                return 'Today ' . $date->format('H:i');
                            }
                            //Compare message date with yesterday's date
                            elseif ($date->format('Y-m-d') == $yesterday->format('Y-m-d')) {
                                return 'Yesterday ' . $date->format('H:i');
                            } else {
                                return $date->format('d.m.Y H:i'); // Short date and time format
                            }
                        }

                        // using loop to print messages
                        foreach ($messages as $msg) {
                            $messageClass = ($msg['sender_id'] == $senderId) ? 'self' : 'other'; // Determining the class depending on the sender
                            echo "<div class='message $messageClass' data-message-id='" . htmlspecialchars($msg['id']) . "'>";
                            echo "<p>" . htmlspecialchars($msg['contenu']) . "</p>"; // XSS protection
                            if ($msg['file_path']) {
                                $fileUrl = htmlspecialchars(str_replace("../", "/", $msg['file_path']));
                                echo "<a href='" . $fileUrl . "' download>Télécharger le fichier</a>";
                            }
                            // Use the formatTimestamp function to output formatted date and time
                            echo "<div class='timestamp-container'><span class='timestamp'>" . formatTimestamp($msg['timestamp']) . "</span></div>";
                            echo "</div>";
                        }
                        ?>
                    </div>
                    <div class="chat-footer">
                        <form id="messageForm" enctype="multipart/form-data" method="POST" action="SendMessage.php">
                            <input type="file" id="file-input" name="file" style="display:none">
                            <button type="button" class="attach-button" onclick="document.getElementById('file-input').click();">📎</button>
                            <input type="hidden" name="receiver_id" value="<?php echo $receiverId; ?>"> <!-- Recipient ID -->
                            <label for="message-input"></label><input type="text" id="message-input" name="message" placeholder="Tapez un message...">
                            <button type="button" onclick="sendMessage(event)">Envoyer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section pour gérer les utilisateurs dans le système de gestion -->
        <div class="Contenu" id="content-2">
            <div class="user-management">
                <!-- Section pour les demandes d'utilisateur en attente d'approbation -->
                <div class="pending-requests">
                    <h2>Demandes en attente</h2>
                    <?php
                    // Récupération des utilisateurs en attente depuis la base de données
                    $pendingUsers = $database->getPendingUsers();
                    foreach ($pendingUsers as $user) {
                        // Affichage de chaque utilisateur en attente avec ses détails
                        echo "<div class='user-request'>";
                        echo "<p><strong>Nom:</strong> " . htmlspecialchars($user['nom']) . "</p>";
                        echo "<p><strong>Prénom:</strong> " . htmlspecialchars($user['prenom']) . "</p>";
                        echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</p>";
                        echo "<p><strong>Telephone:</strong> " . htmlspecialchars($user['telephone']) . "</p>";
                        echo "<p><strong>Activité :</strong> " . htmlspecialchars($user['activite']) . "</p>";
                        echo "<p><strong>Statut Email:</strong> " . ($user['valid_email'] ? 'Validé' : 'Non Validé') . "</p>";

                        switch (htmlspecialchars($user['role'])){
                            case 1:
                                echo "<p><strong>Rôle:</strong> " . "Etudiant" . "</p>";
                                break;
                            case 2:
                                echo "<p><strong>Rôle:</strong> " . "Professeur" . "</p>";
                                break;
                            case 3:
                                echo "<p><strong>Rôle:</strong> " . "Maitre Stage" . "</p>";
                                break;
                            case 4:
                                echo "<p><strong>Rôle:</strong> " . "Secrétariat" . "</p>";
                                break;
                            default:
                                echo "<p><strong>Rôle:</strong> " . "Inconnue" . "</p>";
                                break;
                        }
                        // Boutons pour approuver ou refuser la demande de l'utilisateur
                        echo "<button onclick='approveUser(" . $user['id'] . ")'>✅ Accepter</button>";
                        echo "<button onclick='rejectUser(" . $user['id'] . ")'>❌ Refuser</button>";
                        echo "</div>";
                    }
                    ?>
                </div>
                <!-- Section pour afficher les utilisateurs actifs dans le système -->
                <div class="active-users">
                    <h2>Utilisateurs actifs</h2>
                    <?php
                    // Récupération des utilisateurs actifs depuis la base de données
                    $activeUsers = $database->getActiveUsers();
                    foreach ($activeUsers as $user) {
                        // Affichage de chaque utilisateur actif avec ses détails
                        echo "<div class='active-user'>";
                        echo "<p><strong>Nom:</strong> " . htmlspecialchars($user['nom']) . "</p>";
                        echo "<p><strong>Prénom:</strong> " . htmlspecialchars($user['prenom']) . "</p>";
                        echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</p>";
                        echo "<p><strong>Telephone:</strong> " . htmlspecialchars($user['telephone']) . "</p>";
                        echo "<p><strong>Activité :</strong> " . htmlspecialchars($user['activite']) . "</p>";
                        switch (htmlspecialchars($user['role'])){
                            case 1:
                                echo "<p><strong>Rôle:</strong> " . "Etudiant" . "</p>";
                                break;
                            case 2:
                                echo "<p><strong>Rôle:</strong> " . "Professeur" . "</p>";
                                break;
                            case 3:
                                echo "<p><strong>Rôle:</strong> " . "Maitre Stage" . "</p>";
                                break;
                            case 4:
                                echo "<p><strong>Rôle:</strong> " . "Secrétariat" . "</p>";
                                break;
                            default:
                                echo "<p><strong>Rôle:</strong> " . "Inconnue" . "</p>";
                                break;
                        }
                        // Bouton pour supprimer l'utilisateur du système
                        echo "<button onclick='deleteUser(" . $user['id'] . ")'>🗑️ Supprimer</button>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Section pour gérer les documents -->
        <div class="Contenu" id="content-3">Contenu Documents</div>
        <!-- Section pour gérer les rapports -->
        <div class="Contenu" id="content-4">Contenu Rapports</div>
    </div>
</section>

<footer class="PiedDePage">
    <!-- Pied de page avec logo et liens -->
    <img src="../Resources/Logo_UPHF.png" alt="Logo UPHF" width="10%">
    <a href="Redirection.php">Informations</a>
    <a href="Redirection.php">À propos</a>
</footer>

<!-- Script JavaScript pour la gestion des utilisateurs -->
<script src="../View/Principal/userManagement.js"></script>
</body>
</html>