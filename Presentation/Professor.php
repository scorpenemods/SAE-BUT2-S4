<?php
// manage professor's page
/*
 * Ce script gère la page du professeur.
 * Il vérifie la session et le rôle de l'utilisateur,
 * récupère les étudiants associés,
 * gère les préférences utilisateur comme le mode sombre,
 * charge les traductions selon la langue sélectionnée,
 * et contrôle l'accès aux différentes sections de la page.
 * Il inclut également les fonctionnalités de messagerie et de gestion de fichiers.
 */

global $files;
ob_start();
global $database;
session_start();
require "../Model/Database.php";
require "../Model/Person.php";

$database = (Database::getInstance());

// init .env variables

require __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Initialiser le nom d'utilisateur comme 'Guest' au cas où aucun utilisateur n'est connecté
    $userName = "Guest";
$session_timeout = $_ENV["SESSION_TIMEOUT"];
if (isset($_SESSION['last_activity'])) {
    // Calculer le temps d'inactivité
    $inactive_time = time() - $_SESSION['last_activity'];

    // Si le temps d'inactivité dépasse le délai autorisé
    if ($inactive_time > $session_timeout) {
        // Détruire la session et rediriger vers la page de connexion
        session_unset();
        session_destroy();
        header("Location: Logout.php");
    }
}

$_SESSION['last_activity'] = time();

$userName = "Guest";
$senderId = $_SESSION['user_id'] ?? null;

$studentId = $_POST['student_id'] ?? null;

// Vérification de la session utilisateur
if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    if ($person instanceof Person) {
        $userName = htmlspecialchars($person->getPrenom()) . ' ' . htmlspecialchars($person->getNom());
        $userId = $person->getId(); // ID de l'utilisateur connecté
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
$preferences = $database->getUserPreferences($person->getId());

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
$userId = $person->getId();

// get actual section for Livret Suivi
$section = $_GET['section'] ?? '0';
//TRADUCTION

// Vérifier si une langue est définie dans l'URL, sinon utiliser la session ou le français par défaut
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang; // Enregistrer la langue en session
} else {
    $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr'; // Langue par défaut
}

// Vérification si le fichier de langue existe, sinon charger le français par défaut
$langFile = "../Locales/{$lang}.php";
if (!file_exists($langFile)) {
    $langFile = "../Locales/fr.php";
}

// Charger les traductions
$translations = include $langFile;

?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Petit Stage - <?= $translations['professeur']?></title>
    <link rel="stylesheet" href="../View/Principal/Principal.css">
    <script src="../View/Principal/Principal.js" defer></script>
    <script src="../View/Principal/LivretSuivi.js"></script>
    <script src="/View/Principal/Note.js"></script>
    <link rel="stylesheet" href="../View/Agreement/SecretariatConsultPreAgreementForm.css">
    <link rel="stylesheet" href="../View/Documents/Documents.css">
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include EmojiOneArea -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/emojionearea/3.4.1/emojionearea.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/emojionearea/3.4.1/emojionearea.min.js"></script>
</head>

<script>
    document.querySelector("#addMeetingForm form").addEventListener("submit", function(event) {
        event.preventDefault(); // Empêche le rechargement de la page

        const formData = new FormData(this);

        fetch('Livretnoah.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(result => {
                alert("Rencontre ajoutée avec succès !");
                document.getElementById('addMeetingForm').style.display = 'none';
                window.location.reload(); // Recharger la page après succès
            })
            .catch(error => console.error('Erreur:', error));
    });
    function toggleDetails(meetingId) {
        let detailsDiv = document.getElementById('meeting-details-' + meetingId);
        if (detailsDiv.classList.contains('hidden')) {
            detailsDiv.classList.remove('hidden');
        } else {
            detailsDiv.classList.add('hidden');
        }
    }
</script>


<body class="<?php echo $darkModeEnabled ? 'dark-mode' : ''; ?>">
<?php include_once("../View/Header.php");?>
<div class="sidebar-toggle" id="sidebar-toggle" onclick="sidebar()">&#9664;</div>
<div class="sidebar" id="sidebar">
    <div class="search">
        <input type="text" id="search-input-sidebar" placeholder="Search" onkeyup="searchStudents()">
    </div>
    <div class="students">
        <?php foreach ($students as $student): ?>
            <div class="student" data-student-id="<?php echo htmlspecialchars($student->getId()); ?>"
                 onclick="selectStudent(this)">
                <span><?php echo htmlspecialchars($student->getPrenom()) . ' ' . htmlspecialchars($student->getNom());?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<section class="Menus" id="Menus">
    <nav>
        <span onclick="widget(0)" class="widget-button Current"><?= $translations['accueil']?></span>
        <span onclick="widget(1)" class="widget-button"><?= $translations['mission stage']?></span>
        <span onclick="widget(2)" class="widget-button"><?= $translations['gestion étudiants']?></span>
        <span onclick="widget(3)" class="widget-button"><?= $translations['livret suivi']?></span>
        <span onclick="widget(4)" class="widget-button"><?= $translations['documents']?></span>
        <span onclick="widget(5)" class="widget-button"><?= $translations['messagerie']?></span>
        <span onclick="widget(6)" class="widget-button"><?= $translations['notes']?></span>
        <span onclick="widget(7)" class="widget-button"><?= $translations['offres']?></span>
    </nav>


    <div class="Contenus">
        <div class="<?php echo ($activeSection == '0') ? 'Visible' : 'Contenu'; ?>" id="content-0">
            <h2><?= $translations['welcome_prof']?></h2><br>
            <p><?= $translations['info_prof']?></p><br>
        </div>
        <div class="Contenu <?php echo ($activeSection == '1') ? 'Visible' : 'Contenu'; ?>" id="content-1">
            <?php include('./MissionStage.php')?>
        </div>
        <div class="Contenu <?php echo ($activeSection == '2') ? 'Visible' : 'Contenu'; ?>" id="content-2">

            <?php include_once("StudentManagment.php") ?>
            <script src="../View/Principal/GroupCreation.js"></script>

        </div>
        <div class="Contenu <?php echo ($activeSection == '3') ? 'Visible' : 'Contenu'; ?>" id="content-3">
            <!-- Affichage du livret de suivi -->
            <?php include_once("LivretSuivi.php");?>
        </div>
        <div class="Contenu <?php echo ($activeSection == '4') ? 'Visible' : 'Contenu'; ?>" id="content-4">
            <h2>Espace conventions :</h2>

            <button id="PreAgreement">Consulter un formulaire de pré-convention</button>
            <?php
            include_once("ProfessorConsultPreAgreementForm.php"); ?>
            <script src="../View/Agreement/SecretariatConsultPreAgreementForm.js"></script>

            <?php include_once("Documents/Documents.php");?>

            <h2>Gestion des Fichiers</h2>
            <form class="box" method="post" action="" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="form_id" value="uploader_fichier">
                <input type="hidden" name="upload_type" value="file">
                <div class="box__input">
                    <input type="file" name="files[]" id="file-doc" multiple>
                    <button class="box__button" type="submit">Uploader Fichier</button>
                </div>
            </form>

            <div class="file-list">
                <h2>Fichiers Uploadés</h2>
                <div class="file-grid">
                    <?php foreach ($files as $file): ?>
                        <div class="file-card">
                            <div class="file-info">
                                <strong><?= htmlspecialchars($file['name']) ?></strong>
                                <p><?= round($file['size'] / 1024, 2) ?> KB</p>
                            </div>
                            <form method="get" action="Documents/Download.php">
                                <input type="hidden" name="file" value="<?= htmlspecialchars($file['path']) ?>">
                                <button type="submit" class="download-button">Télécharger</button>
                            </form>
                            <form method="post" action="" class="delete-form">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="form_id" value="delete_rapport">
                                <input type="hidden" name="fileId" value="<?= $file['id'] ?>">
                                <button type="submit" class="delete-button">Supprimer</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
        <div class="Contenu <?php echo ($activeSection == '5') ? 'Visible' : 'Contenu'; ?>" id="content-5">
            <!-- Messagerie Content -->
            <div class="messenger">
                <div class="contacts">
                    <div class="search-bar">
                        <label for="search-input"></label>
                        <input type="text" id="search-input" placeholder="<?= $translations['search_contact']?>" onkeyup="searchContacts()">
                    </div>
                    <h3><?= $translations['contacts']?></h3>
                    <!-- Bouton pour contacter le secrétariat -->
                    <button id="contact-secretariat-btn" class="contact-secretariat-btn"><?= $translations['contacter secrétariat']?></button>
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
                            <input type="text" id="message-input" name="message" placeholder="<?= $translations['tapez message']?>">
                            <button type="submit"><?= $translations['send']?></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="Contenu <?php echo ($activeSection == '6') ? 'Visible' : 'Contenu'; ?>" id="content-6">
            <?php include_once "GetNotesProf.php" ?>
        </div>

    <!-- Offres Content -->
    <div class="Contenu <?php echo $activeSection == '7' ? 'Visible' : ''; ?>" id="content-7">
        <?= $translations['contenu offres']?>
        <a href="../View/Offer/List.php?type=all">
            <button type="button"><?= $translations['voir offres']?></button>
        </a>
    </div>
</section>

<!-- Fenêtre modale pour contacter le secrétariat -->
<div id="contact-secretariat-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3><?= $translations['send_admin']?></h3>
        <form id="contactSecretariatForm" enctype="multipart/form-data" method="POST" action="ContactSecretariat.php">
            <div class="form-group">
                <label for="subject"><?= $translations['sujet']?> :</label>
                <input type="text" class="form-control animated-input" id="subject" name="subject" placeholder="<?= $translations['sujet_message']?>">
            </div>
            <div class="form-group">
                <label for="message"><?= $translations['message']?> :</label>
                <textarea class="form-control animated-input" id="message" name="message" rows="5" placeholder="<?= $translations['write_mess']?>" required></textarea>
            </div>
            <div class="form-group position-relative">
                <label for="file" class="form-label"><?= $translations['joindre fichier']?> :</label>
                <input type="file" class="form-control-file animated-file-input" id="file" name="file">
                <button type="button" class="btn btn-danger btn-sm reset-file-btn" id="resetFileBtn" title="Annuler le fichier sélectionné" style="display: none;">✖️</button>
            </div>
            <button type="submit" class="btn btn-primary btn-block animated-button"><?= $translations['mess_admin']?></button>
        </form>
    </div>
</div>

<footer>
    <?php include_once '../View/Footer.php'; ?>
</footer>
<script src="../View/Principal/deleteMessage.js"></script>
<script src="/View/Principal/GroupMessenger.js"></script>
<script>
    // Gestion de la modale "contact-secretariat"
    (function () {
        const modal = document.getElementById("contact-secretariat-modal");
        const btn = document.getElementById("contact-secretariat-btn");
        const span = document.querySelector(".close");

        // Ouvrir la modale au clic sur le bouton
        btn.addEventListener("click", () => {
            modal.style.display = "block";
        });

        // Fermer la modale au clic sur le bouton de fermeture (X)
        span.addEventListener("click", () => {
            modal.style.display = "none";
        });

        // Fermer la modale en cliquant en dehors du contenu
        window.addEventListener("click", (event) => {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        });
    })();

    // Animation du gradient sur les champs de saisie
    (function () {
        document.querySelectorAll('.form-control.animated-input').forEach(element => {
            element.addEventListener('focus', () => {
                element.classList.add('gradient-border');
            });

            element.addEventListener('blur', () => {
                element.classList.remove('gradient-border');
            });
        });
    })();

    // Gestion du bouton d'annulation du fichier
    (function () {
        const fileInput = document.getElementById('file');
        const resetFileBtn = document.getElementById('resetFileBtn');

        fileInput.addEventListener('change', () => {
            resetFileBtn.style.display = fileInput.files.length > 0 ? 'block' : 'none';
        });

        resetFileBtn.addEventListener('click', () => {
            fileInput.value = '';  // Réinitialisation du champ de fichier
            resetFileBtn.style.display = 'none';  // Cache le bouton d'annulation
        });
    })();

</script>
</body>
</html>
<?php
ob_end_flush();
?>