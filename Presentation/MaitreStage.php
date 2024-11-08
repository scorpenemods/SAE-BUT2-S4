<?php
// Démarre une session pour gérer les informations de l'utilisateur connecté
session_start();

// Inclusion des classes nécessaires pour la base de données et l'utilisateur
require "../Model/Database.php";
require "../Model/Person.php";

// Initialisation de la connexion à la base de données
$database = (Database::getInstance());

// Initialisation du nom de l'utilisateur par défaut (Guest) si non connecté
$userName = "Guest";

// Vérification si les informations de l'utilisateur existent dans la session
if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']); // Désérialise les données de session pour obtenir un objet `Person`

    // Vérifie si l'objet désérialisé est bien une instance de la classe `Person`
    if ($person instanceof Person) {
        // Récupère le prénom et le nom de l'utilisateur en protégeant contre les attaques XSS (Cross-Site Scripting)
        $userName = htmlspecialchars($person->getPrenom()) . ' ' . htmlspecialchars($person->getNom());
        $senderId = $person->getUserId(); // Récupération de l'ID de l'utilisateur connecté
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


// Récupération de la liste des étudiants associés au maître de stage
$students = $database->getStudentsMaitreDeStage($senderId);

// Récupération des préférences de l'utilisateur à partir de la base de données
$preferences = $database->getUserPreferences($person->getUserId());

// Vérification si le mode sombre est activé dans les préférences de l'utilisateur
$darkModeEnabled = isset($preferences['darkmode']) && $preferences['darkmode'] == 1 ? true : false;

if (isset($_GET['section'])) {
    $_SESSION['active_section'] = $_GET['section'];
}
// Définit la section active par défaut (Accueil) si aucune n'est spécifiée
$activeSection = isset($_SESSION['active_section']) ? $_SESSION['active_section'] : '0';

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
    <script src="../View/Principal/Principal.js"></script>

</head>
<body class="<?php echo $darkModeEnabled ? 'dark-mode' : ''; ?>"> <!-- Ajout de la classe 'dark-mode' si activée -->

<!-- Barre de navigation -->
<header class="navbar">
    <div class="navbar-left">
        <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
        <span class="app-name">Le Petit Stage - Maitre de Stage</span>
    </div>
    <div class="navbar-right">
        <div id="notification-icon" class="notification-icon">
            <img src="../Resources/Notif.png" alt="Notifications">
            <span id="notification-count" class="notification-count"></span>
        </div>
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

<div class="sidebar-toggle" id="sidebar-toggle" onclick="sidebar()">&#9664;</div>
<div class="sidebar" id="sidebar">
    <div class="search">
        <input type="text" id="search-input-sidebar" placeholder="Search" onkeyup="searchStudents()">
    </div>
    <div class="students">
        <?php foreach ($students as $student): ?>
            <div class="student" onclick="selectStudent(this)">
                <span><?php echo htmlspecialchars($student->getPrenom()) . ' ' . htmlspecialchars($student->getNom()); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Section contenant les différents menus -->
<section class="Menus" id="Menus">
    <nav>
        <span onclick="widget(0)" class="widget-button Current">Accueil</span>
        <span onclick="widget(1)" class="widget-button">Mission de stage</span>
        <span onclick="widget(2)" class="widget-button">Gestion Stagiaire</span>
        <span onclick="widget(3)" class="widget-button">Livret de Suivi</span>
        <span onclick="widget(4)" class="widget-button">Documents</span>
        <span onclick="widget(5)" class="widget-button">Messagerie</span>
        <span onclick="widget(6)" class="widget-button">Notes</span>
    </nav>

    <div class="Contenus">
        <!-- Contenu de l'Accueil -->
        <div class="<?php echo ($activeSection == '0') ? 'Visible' : 'Contenu'; ?>" id="content-0">
            <h2>Bienvenue sur la plateforme pour les Maitres de Stage!</h2><br>
            <p>Gérez vos stagiaires, communiquez facilement et suivez l'évolution de leurs compétences.</p><br>
        </div>

        <!-- Contenu des autres sections -->
        <div class="Contenu <?php echo ($activeSection == '1') ? 'Visible' : 'Contenu'; ?>" id="content-1">Missions de stage</div>
        <div class="Contenu <?php echo ($activeSection == '2') ? 'Visible' : 'Contenu'; ?>" id="content-2">Contenu Gestion Stagiaires</div>
        <div class="Contenu <?php echo ($activeSection == '3') ? 'Visible' : 'Contenu'; ?>" id="content-3">
            <?php include_once("LivretSuivi.php");?>


        </div>
        <div class="Contenu <?php echo ($activeSection == '4') ? 'Visible' : 'Contenu'; ?>" id="content-4">Contenu Documents</div>

        <!-- Contenu de la Messagerie -->
        <div class="Contenu <?php echo ($activeSection == '5') ? 'Visible' : 'Contenu'; ?>" id="content-5">
            <div class="messenger">
                <!-- Barre de recherche de contacts -->
                <div class="contacts">
                    <div class="search-bar">
                        <label for="search-input"></label>
                        <input type="text" id="search-input" placeholder="Rechercher des contacts..." onkeyup="searchContacts()">
                    </div>
                    <h3>Contacts</h3>
                    <ul id="contacts-list">
                        <?php include_once("ContactList.php");?>

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
                        <!-- Les messages seront chargés dynamiquement via JavaScript -->
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

        <div class="Contenu <?php echo ($activeSection == '6') ? 'Visible' : 'Contenu'; ?>"</div>
        <h2 id="student-name"><?php echo htmlspecialchars($student->getPrenom()) . ' ' . htmlspecialchars($student->getNom()); ?></h2>
        <div class="notes-container">
            <table class="notes-table">
                <thead>
                <tr>
                    <th>Sujet</th>
                    <th>Appréciations</th>
                    <th>Note /20</th>
                    <th>Coefficient</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><textarea name="sujet[]" placeholder="Sujet" disabled oninput="autoExpand(this)" ></textarea></td>
                    <td><textarea name="appreciations[]" placeholder="Appréciations" oninput="autoExpand(this)" disabled></textarea></td>
                    <td><input type="number" name="note[]" placeholder="Note" disabled></td>
                    <td><input type="number" name="coeff[]" placeholder="Coefficient" disabled </td>
                </tr>
                <tr>
                    <td><textarea name="sujet[]" placeholder="Sujet" disabled oninput="autoExpand(this)" ></textarea></td>
                    <td><textarea name="appreciations[]" placeholder="Appréciations" oninput="autoExpand(this)" disabled></textarea></td>
                    <td><input type="number" name="note[]" placeholder="Note" disabled></td>
                    <td><input type="number" name="coeff[]" placeholder="Coefficient" disabled </td>
                </tr>
                <tr>
                    <td><textarea name="sujet[]" placeholder="Sujet" oninput="autoExpand(this)" disabled></textarea></td>
                    <td><textarea name="appreciations[]" placeholder="Appréciations" oninput="autoExpand(this)" disabled ></textarea></td>
                    <td><input type="number" name="note[]" placeholder="Note" disabled></td>
                    <td><input type="number" name="coeff[]" placeholder="Coefficient" disabled </td>
                </tr>
                <tr>
                    <td><textarea name="sujet[]" placeholder="Sujet" oninput="autoExpand(this)" disabled></textarea></td>
                    <td><textarea name="appreciations[]" placeholder="Appréciations" oninput="autoExpand(this)" disabled ></textarea></td>
                    <td><input type="number" name="note[]" placeholder="Note" disabled></td>
                    <td><input type="number" name="coeff[]" placeholder="Coefficient" disabled </td>
                </tr>
                </tbody>
            </table>
            <div id="validationMessage" class="validation-message"></div>
        </div>
        <div class="notes-buttons">
            <button class="mainbtn" onclick="enableNotes()">Ajouter les notes</button>
            <button class="mainbtn" onclick="validateNotes()" disabled id="validateBtn">Valider les notes</button>
            <button id="cancelBtn" onclick="cancelNotes()">Annuler</button>
        </div>
    </div>
    </div>
</section>

<!-- Pied de page -->
<footer class="PiedDePage">
    <img src="../Resources/Logo_UPHF.png" alt="Logo UPHF" width="10%">
    <a href="Redirection.php">Informations</a>
    <a href="Redirection.php">À propos</a>
</footer>
<script src="../View/Principal/deleteMessage.js"></script>
</body>
</html>
