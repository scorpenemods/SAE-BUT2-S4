<?php
global $database;
session_start();
require "../Model/Database.php";
require "../Model/Person.php";

$database = (Database::getInstance());

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

$students = $database->getStudentsProf($senderId);

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

$database = Database::getInstance();
$pdo = $database->getConnection();

// Récupérer l'utilisateur connecté (vous avez déjà ce processus dans votre code)
$person = unserialize($_SESSION['user']);
$userId = $person->getUserId();


if (isset($_POST['submit_notes'])) {
    if (isset($_POST['sujet'], $_POST['appreciations'], $_POST['note'], $_POST['coeff'])) {
        // Vérification que tous les champs sont remplis
        $allFieldsFilled = true;
        $notesData = [];

        foreach ($_POST['sujet'] as $index => $sujet) {
            if (empty($_POST['sujet'][$index]) || empty($_POST['appreciations'][$index]) || empty($_POST['note'][$index]) || empty($_POST['coeff'][$index])) {
                $allFieldsFilled = false;
                break;
            }
            // Préparer chaque note à insérer dans la base de données
            $notesData[] = [
                'sujet' => $_POST['sujet'][$index],
                'appreciation' => $_POST['appreciations'][$index],
                'note' => $_POST['note'][$index],
                'coeff' => $_POST['coeff'][$index],
            ];
        }
        // Appeler la méthode addNotes pour insérer les notes dans la base de données
        $database = Database::getInstance(); // Assurez-vous que vous utilisez votre instance de base de données
        try {
            // Appeler la fonction addNotes
            $database->addNotes($userId, $notesData, $pdo); // Passez l'ID de l'utilisateur, les notes et le PDO
            // Rediriger après l'ajout
            header("Location: Professor.php");
            exit();
        } catch (PDOException $e) {
            echo "Erreur lors de l'ajout des notes : " . $e->getMessage();
        }
    } else {
        echo "Veuillez remplir tous les champs.";
    }
}

if (isset($_POST['saveNotes'])) {
    if (isset($_POST['sujet'], $_POST['appreciations'], $_POST['note'], $_POST['coeff'])) {
        // Vérification que tous les champs sont remplis
        $allFieldsFilled = true;
        $notesData = [];

        foreach ($_POST['sujet'] as $index => $sujet) {
            if (empty($_POST['sujet'][$index]) || empty($_POST['appreciations'][$index]) || empty($_POST['note'][$index]) || empty($_POST['coeff'][$index])) {
                $allFieldsFilled = false;
                break;
            }
            // Préparer chaque note à insérer dans la base de données
            $notesData[] = [
                'sujet' => $_POST['sujet'][$index],
                'appreciation' => $_POST['appreciations'][$index],
                'note' => $_POST['note'][$index],
                'coeff' => $_POST['coeff'][$index],
            ];
        }

        if ($allFieldsFilled) {
            try {
                $database->updateNotes($userId, $notesData, $pdo);
                // Rediriger après l'ajout
                header("Location: Professor.php");
                exit();
            } catch (PDOException $e) {
                echo "Erreur lors de l'ajout des notes : " . $e->getMessage();
            }
        } else {
            echo "Veuillez remplir tous les champs.";
        }
    } else {
        echo "Erreur lors de la soumission du formulaire. Veuillez réessayer.";
    }
}


if (!empty($students)) {
    $student = $students[0];
} else {
    $student = null;
}
if ($student !== null) {
    $studentName = htmlspecialchars($student->getPrenom()) . ' ' . htmlspecialchars($student->getNom());
} else {
    $studentName = "Vous n'avez pas d'étudiants";
}

$hasStudents = !empty($students);
$notes = $database->getNotes($userId);

?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Petit Stage - Professeur</title>
    <link rel="stylesheet" href="../View/Principal/Principal.css">
    <script src="../View/Principal/Principal.js"></script>
    <link rel="stylesheet" href="/View/Principal/Notifs.css">
    <script src="/View/Principal/Notif.js"></script>
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include EmojiOneArea -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/emojionearea/3.4.1/emojionearea.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/emojionearea/3.4.1/emojionearea.min.js"></script>
</head>

<body class="<?php echo $darkModeEnabled ? 'dark-mode' : ''; ?>">
<header class="navbar">



    <div class="navbar-left">
        <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
        <span class="app-name">Le Petit Stage - Professeur</span>
    </div>
    <div class="navbar-right">

        <div id="notification-icon" onclick="toggleNotificationPopup()">
            <img id="notification-icon-img" src="../Resources/Notif.png" alt="Notifications">
            <span id="notification-count" style="display: none;"></span>
        </div>

        <!-- Notification Popup -->
        <div id="notification-popup" class="notification-popup">
            <div class="notification-popup-header">
                <h3>Notifications</h3>
                <button onclick="closeNotificationPopup()">X</button>
            </div>
            <div class="notification-popup-content">
                <ul id="notification-list">
                    <!-- Notifications will be loaded here via JavaScript -->
                </ul>
            </div>
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

<section class="Menus" id="Menus">
    <nav>
        <span onclick="widget(0)" class="widget-button Current">Accueil</span>
        <span onclick="widget(1)" class="widget-button">Mission de stage</span>
        <span onclick="widget(2)" class="widget-button">Gestion Étudiants</span>
        <span onclick="widget(3)" class="widget-button">Livret de suivi</span>
        <span onclick="widget(4)" class="widget-button">Documents</span>
        <span onclick="widget(5)" class="widget-button">Messagerie</span>
        <span onclick="widget(6)" class="widget-button">Notes</span>
    </nav>
    <div class="Contenus">
        <div class="<?php echo ($activeSection == '0') ? 'Visible' : 'Contenu'; ?>" id="content-0">
            <h2>Bienvenue sur la plateforme pour Professeurs!</h2><br>
            <p>Gérez les étudiants, suivez leur progression et communiquez facilement avec eux.</p><br>
        </div>
        <div class="Contenu <?php echo ($activeSection == '1') ? 'Visible' : 'Contenu'; ?>" id="content-1">Contenu des missions de stage</div>
        <div class="Contenu <?php echo ($activeSection == '2') ? 'Visible' : 'Contenu'; ?>" id="content-2">Contenu Gestion Étudiants</div>
        <div class="Contenu <?php echo ($activeSection == '3') ? 'Visible' : 'Contenu'; ?>" id="content-3">
            <!-- Affichage des participants -->

            <div class="livret-header" style="margin-bottom: 10px">
                <h2 style="text-align: center">Participants</h2>
            </div><br>
            <div style="display: flex; gap: 10%; justify-content: center;">
                <div class="participants">
                    <h3>Etudiant :</h3><br>
                    <p>Nom prénom : <label id="student-name"><?php echo $studentName; ?></label></p>
                    <p>Formation : <label><?php echo htmlspecialchars($student->getActivite()); ?></label></p>
                    <p>Email : <label><?php echo htmlspecialchars($student->getEmail()); ?></label></p>
                    <?php if ($student->getTelephone() != 0){?>
                        <p>Téléphone : <label><?php echo htmlspecialchars($student->getTelephone()); ?></label></p>
                    <?php }?>
                </div>

                <div class="participants">
                    <h3>Professeur :</h3><br>
                    <p>Nom prénom : <label><?php echo $userName; ?></label></p>
                    <p>Spécialité : <label><?php echo htmlspecialchars($person->getActivite()); ?></label></p>
                    <p>Email : <label><?php echo htmlspecialchars($person->getEmail()); ?></label></p>
                    <?php if ($person->getTelephone() != 0){?>
                        <p>Téléphone : <label><?php echo htmlspecialchars($person->getTelephone()); ?></label></p>
                    <?php }?>
                </div>

                <div class="participants">
                    <h3>Maitre de stage :</h3><br>
                    <p>Nom prénom : <label><?php echo '(MdS)'; ?></label></p>
                    <p>Spécialité : <label><?php echo '(Spécialité)' ?></label></p>
                    <p>Email : <label><?php echo '(mds@email.com)' ?></label></p>
                    <?php if (0==0){?>
                        <p>Téléphone : <label><?php echo '(téléphone)' ?></label></p>
                    <?php }?>
                </div>
            </div><br>

            <!-- Affichage du livret de suivi -->

            <?php include_once("LivretSuivi.php");?>
        </div>
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
                        <?php include_once("ContactList.php");?>
                        <?php include_once("GroupContactList.php");?>
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
                            <!-- Hidden fields for receiver_id and group_id -->
                            <input type="hidden" name="receiver_id" id="receiver_id" value="">
                            <input type="hidden" name="group_id" id="group_id" value="">
                            <input type="text" id="message-input" name="message" placeholder="Tapez un message...">
                            <button type="submit">Envoyer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="Contenu <?php echo ($activeSection == '6') ? 'Visible' : 'Contenu'; ?>" id="content-6">
            <h2 id="student-name"><?php echo $studentName; ?></h2>
            <form method="post" action="">
                <div class="notes-container">
                    <table id="notesTable" class="notes-table">
                        <thead>
                        <tr>
                            <th>Sujet</th>
                            <th>Appréciations</th>
                            <th>Note /20</th>
                            <th>Coefficient</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($notes as $note): ?>
                            <tr>
                                <td><textarea name="sujet[]" placeholder="Sujet" disabled oninput="autoExpand(this)"><?php echo htmlspecialchars($note->getSujet()); ?></textarea></td>
                                <td><textarea name="appreciations[]" placeholder="Appréciations" oninput="autoExpand(this)" disabled><?php echo htmlspecialchars($note->getAppreciation()); ?></textarea></td>
                                <td><input type="number" name="note[]" placeholder="Note" value="<?php echo htmlspecialchars($note->getNote()); ?>" disabled></td>
                                <td><input type="number" name="coeff[]" placeholder="Coefficient" value="<?php echo htmlspecialchars($note->getCoeff()); ?>" disabled></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div id="validationMessage" class="validation-message"></div>
                </div>

                <div class="notes-buttons">
                    <button type="button"  id="addNoteButton" class="mainbtn" onclick="addNoteRow()"   <?php echo !$hasStudents ? 'disabled' : ''; ?>>Ajouter une note</button>
                    <button type="button" id="editNotesButton" class="mainbtn" onclick="enableNotes()">Modifier les notes</button>
                    <button type="submit" name="saveNotes" class="mainbtn" onclick="saveNote()" >Sauvegarder les notes</button>
                    <button type="submit" name="submit_notes" class="mainbtn" onclick="validateNotes()"  id="validateBtn">Valider les notes</button>
                    <button type="button" class="mainbtn" onclick="cancelNotes()"  id="cancelBtn">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</section>

<footer class="PiedDePage">
    <img src="../Resources/Logo_UPHF.png" alt="Logo UPHF" width="10%">
    <a href="Redirection.php">Informations</a>
    <a href="Redirection.php">À propos</a>
</footer>
<script src="../View/Principal/deleteMessage.js"></script>
<script src="/View/Principal/GroupMessenger.js"></script>
</body>
</html>