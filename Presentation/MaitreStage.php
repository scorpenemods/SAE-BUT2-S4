<?php
// Démarre une session pour gérer les informations de l'utilisateur connecté
session_start();

// Inclusion des classes nécessaires pour la base de données et l'utilisateur
require "../Model/Database.php";
require "../Model/Person.php";

// Initialisation de la connexion à la base de données
$database = Database::getInstance();
$pdo = $database->getConnection();

// Initialisation du nom de l'utilisateur par défaut (Guest) si non connecté
$userName = "Guest";
$senderId = $_SESSION['user_id'] ?? null;
$studentId = $_POST['student_id'] ?? null;

$person = unserialize($_SESSION['user']);
$userId = $person->getId();
$students = $database->getStudentsProf($senderId);


// Vérification si les informations de l'utilisateur existent dans la session
if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']); // Désérialise les données de session pour obtenir un objet `Person`

    // Vérifie si l'objet désérialisé est bien une instance de la classe `Person`
    if ($person instanceof Person) {
        // Récupère le prénom et le nom de l'utilisateur en protégeant contre les attaques XSS (Cross-Site Scripting)
        $userName = htmlspecialchars($person->getPrenom()) . ' ' . htmlspecialchars($person->getNom());
        $senderId = $person->getId(); // Récupération de l'ID de l'utilisateur connecté
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
$preferences = $database->getUserPreferences($person->getId());

// Vérification si le mode sombre est activé dans les préférences de l'utilisateur
$darkModeEnabled = isset($preferences['darkmode']) && $preferences['darkmode'] == 1 ? true : false;

if (isset($_GET['section'])) {
    $_SESSION['active_section'] = $_GET['section'];
}
// Définit la section active par défaut (Accueil) si aucune n'est spécifiée
$activeSection = isset($_SESSION['active_section']) ? $_SESSION['active_section'] : '0';

if (isset($_POST['submit_notes'])) {
    if (isset($_POST['sujet'], $_POST['appreciations'], $_POST['note'], $_POST['coeff'])) {
        $allFieldsFilled = true;
        $notesData = [];

        foreach ($_POST['sujet'] as $index => $sujet) {
            if (empty($_POST['sujet'][$index]) || empty($_POST['appreciations'][$index]) || empty($_POST['note'][$index]) || empty($_POST['coeff'][$index])) {
                $allFieldsFilled = false;
                break;
            }
            $notesData[] = [
                'sujet' => $_POST['sujet'][$index],
                'appreciation' => $_POST['appreciations'][$index],
                'note' => $_POST['note'][$index],
                'coeff' => $_POST['coeff'][$index],
            ];
        }

        if ($allFieldsFilled) {
            try {
                $database->addNotes($studentId, $notesData, $pdo);
                header("Location: MaitreStage.php");
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

if (isset($_POST['saveNote'])) {
    if (isset($_POST['note_id'], $_POST['sujet'], $_POST['appreciations'], $_POST['note'], $_POST['coeff'])) {
        $noteId = $_POST['note_id'];
        $sujet = $_POST['sujet'];
        $appreciation = $_POST['appreciations'];
        $note = $_POST['note'];
        $coeff = $_POST['coeff'];

        // Vérifier que les valeurs sont correctes avant de continuer
        if (!empty($sujet) && !empty($appreciation) && is_numeric($note) && is_numeric($coeff)) {
            try {
                $result = $database->updateNote(
                    $noteId,
                    $userId,
                    $sujet,
                    $appreciation,
                    $note,
                    $coeff,
                    $pdo
                );

                if ($result) {
                    echo "success";
                } else {
                    echo "Aucune ligne modifiée. Vérifiez l'ID ou les permissions.";
                }
                exit();
            } catch (PDOException $e) {
                echo "Erreur lors de la mise à jour des notes : " . $e->getMessage();
                exit();
            }
        } else {
            echo "Veuillez remplir tous les champs correctement.";
            exit();
        }
    } else {
        echo "Erreur lors de la soumission du formulaire. Veuillez réessayer.";
        exit();
    }
}



if (!empty($students)) {
    $student = $students[0];
    $studentId = htmlspecialchars($student->getId());
    $studentName = htmlspecialchars($student->getPrenom()) . ' ' . htmlspecialchars($student->getNom());
} else {
    $student = null;
    $studentId = null;
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
    <title>Le Petit Stage - Maitre de Stage</title>
    <link rel="stylesheet" href="../View/Principal/Principal.css">
    <script src="../View/Principal/Principal.js" defer></script>
    <link rel="stylesheet" href="/View/Principal/Notifs.css">
    <link rel="stylesheet" href="/View/css/Footer.css">
    <link rel="stylesheet" href="../View/Documents/Documents.css">
    <script src="/View/Principal/Notif.js"></script>
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include EmojiOneArea -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/emojionearea/3.4.1/emojionearea.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/emojionearea/3.4.1/emojionearea.min.js"></script>
</head>
<body class="<?php echo $darkModeEnabled ? 'dark-mode' : ''; ?>"> <!-- Ajout de la classe 'dark-mode' si activée -->

<!-- Barre de navigation -->
<header class="navbar">
    <div class="navbar-left">
        <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
        <span class="app-name">Le Petit Stage - Maitre de Stage</span>
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
            <?php ($student->getId()); ?>
            <div class="student" data-student-id="<?php echo htmlspecialchars($student->getId()); ?>" onclick="selectStudent(this)">
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
        <span onclick="widget(7)" class="widget-button">Offres</span>

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
            <!-- Affichage du livret de suivi -->

            <?php include_once("LivretSuivi.php");?>


        </div>
        <div class="Contenu <?php echo ($activeSection == '4') ? 'Visible' : 'Contenu'; ?>" id="content-4">
            <?php include_once("../View/Documents/Documents.php");?>
            <?php $uploadDir = '../uploads/'; ?>
            <script src="../View/Documents/Documents.js"></script>
        </div>

        <!-- Contenu de la Messagerie -->
        <div class="Contenu <?php echo ($activeSection == '5') ? 'Visible' : 'Contenu'; ?>" id="content-5">
            <div class="messenger">
                <div class="contacts">
                    <div class="search-bar">
                        <input type="text" id="search-input" placeholder="Search contacts..." onkeyup="searchContacts()">
                    </div>
                    <h3>Contacts</h3>
                    <!-- Bouton pour contacter le secrétariat -->
                    <button id="contact-secretariat-btn" class="contact-secretariat-btn">Contacter le secrétariat</button>
                    <ul id="contacts-list">
                        <?php include_once("ContactList.php");?>
                        <?php include_once("GroupContactList.php");?>
                    </ul>
                </div>

                <!-- Context menu for message actions -->
                <div id="context-menu" class="context-menu">
                    <ul>
                        <li id="copy-text">Copy</li>
                        <li id="delete-message">Delete</li>
                    </ul>
                </div>

                <div class="chat-window">
                    <div class="chat-header">
                        <h3 id="chat-header-title">Select a chat to start messaging.</h3>
                    </div>
                    <div class="chat-body" id="chat-body">
                        <!-- Messages will be loaded here dynamically via JavaScript -->
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
            <div id="confirmation-message" class="confirmation-message" style="display: none;"></div>
            <h2 id="selected-student-name"><?php echo isset($studentName) && !empty($studentName) ? htmlspecialchars($studentName) : 'Sélectionnez un étudiant'; ?></h2>
            <form method="POST" action="MaitreStage.php">
                <input type="hidden" id="student-id" name="student_id" value="<?php echo $studentId; ?>">
                <div class="notes-container">
                    <table id="notesTable" class="notes-table">
                        <thead>
                        <tr>
                            <th>Sujet</th>
                            <th>Appréciations</th>
                            <th>Note /20</th>
                            <th>Coefficient</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>

                <!-- Boutons pour la gestion des notes -->
                <div class="notes-buttons">
                    <button type="button" id="addNoteButton" class="mainbtn" onclick="addNoteRow()" disabled>Ajouter une note</button>
                    <button type="submit" name="submit_notes" class="mainbtn" onclick="validateNotes()" id="validateBtn" disabled>Valider les notes</button>
                    <button type="button" class="mainbtn" onclick="cancelNotes()" id="cancelBtn" disabled>Annuler</button>
                </div>
            </form>
            <div id="validationMessage" class="validation-message"></div>
            <div id="confirmation-message" class="confirmation-message" style="display: none;"></div>
        </div>
    </div>
    <!-- Offres Content -->
    <div class="Contenu <?php echo $activeSection == '7' ? 'Visible' : ''; ?>" id="content-7">
        Contenu Offres
        <a href="../View/List.php?type=all">
            <button type="button">Voir les offres</button>
        </a>
    </div>
</section>

<!-- Fenêtre modale pour contacter le secrétariat -->
<div id="contact-secretariat-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Envoyer un message au secrétariat</h3>
        <form id="contactSecretariatForm" enctype="multipart/form-data" method="POST" action="ContactSecretariat.php">
            <div class="form-group">
                <label for="subject">Sujet :</label>
                <input type="text" class="form-control animated-input" id="subject" name="subject" placeholder="Sujet de votre message">
            </div>
            <div class="form-group">
                <label for="message">Message :</label>
                <textarea class="form-control animated-input" id="message" name="message" rows="5" placeholder="Écrivez votre message ici..." required></textarea>
            </div>
            <div class="form-group position-relative">
                <label for="file" class="form-label">Joindre un fichier :</label>
                <input type="file" class="form-control-file animated-file-input" id="file" name="file">
                <button type="button" class="btn btn-danger btn-sm reset-file-btn" id="resetFileBtn" title="Annuler le fichier sélectionné" style="display: none;">✖️</button>
            </div>
            <button type="submit" class="btn btn-primary btn-block animated-button">Envoyer au secrétariat</button>
        </form>
    </div>
</div>

<!-- Pied de page -->
<footer>
    <?php include "../View/Footer.php"; ?>
</footer>

<script src="../View/Principal/deleteMessage.js"></script>
<script src="/View/Principal/GroupMessenger.js"></script>
<script>
    // Obtenir la modale
    var modal = document.getElementById("contact-secretariat-modal");

    // Obtenir le bouton qui ouvre la modale
    var btn = document.getElementById("contact-secretariat-btn");

    // Obtenir l'élément <span> qui ferme la modale
    var span = document.getElementsByClassName("close")[0];

    // Quand l'utilisateur clique sur le bouton, ouvrir la modale
    btn.onclick = function() {
        modal.style.display = "block";
    }

    // Quand l'utilisateur clique sur <span> (x), fermer la modale
    span.onclick = function() {
        modal.style.display = "none";
    }

    // Quand l'utilisateur clique en dehors de la modale, fermer la modale
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Animation du gradient sur le champ de saisie
    document.querySelectorAll('.form-control.animated-input').forEach(element => {
        element.addEventListener('focus', () => {
            element.classList.add('gradient-border');
        });

        element.addEventListener('blur', () => {
            element.classList.remove('gradient-border');
        });
    });

    // Gestion du bouton d'annulation du fichier
    document.getElementById('file').addEventListener('change', function() {
        if (this.files.length > 0) {
            // Afficher le bouton d'annulation
            document.getElementById('resetFileBtn').style.display = 'block';
        } else {
            document.getElementById('resetFileBtn').style.display = 'none';
        }
    });

    document.getElementById('resetFileBtn').addEventListener('click', function() {
        const fileInput = document.getElementById('file');
        fileInput.value = ''; // Réinitialise le champ de fichier
        this.style.display = 'none'; // Cache le bouton d'annulation
    });
</script>
</body>


</html>