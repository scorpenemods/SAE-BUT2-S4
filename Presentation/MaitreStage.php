<?php
// manage internship supervisor's page
global $files;
ob_start();

// Démarre une session pour gérer les informations de l'utilisateur connecté
session_start();

// Inclusion des classes nécessaires pour la base de données et l'utilisateur
require "../Model/Database.php";
require "../Model/Person.php";

// Initialisation de la connexion à la base de données
$database = Database::getInstance();
$pdo = $database->getConnection();

require_once "../Model/Config.php";

if (isset($_SESSION['last_activity'])) {
    // Calculer le temps d'inactivité
    $inactive_time = time() - $_SESSION['last_activity'];

    // Si le temps d'inactivité dépasse le délai autorisé
    if ($inactive_time > SESSION_TIMEOUT) {
        // Détruire la session et rediriger vers la page de connexion
        session_unset();
        session_destroy();
        header("Location: Logout.php");
    }
}

$_SESSION['last_activity'] = time();

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

// Récupération de l'id de l'élève
$studentId = $_POST['stage_id'] ?? null;


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
    <title>Le Petit Stage - <?= $translations['maitre stage']?></title>
    <link rel="stylesheet" href="../View/Principal/Principal.css">
    <script src="../View/Principal/Principal.js" defer></script>
    <script src="../View/Principal/LivretSuivi.js"></script>
    <script src="/View/Principal/Note.js"></script>

    <link rel="stylesheet" href="../View/Documents/Documents.css">
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include EmojiOneArea -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/emojionearea/3.4.1/emojionearea.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/emojionearea/3.4.1/emojionearea.min.js"></script>
</head>
<script>
    function toggleDetails(meetingId) {
        let detailsDiv = document.getElementById('meeting-details-' + meetingId);
        if (detailsDiv.classList.contains('hidden')) {
            detailsDiv.classList.remove('hidden');
        } else {
            detailsDiv.classList.add('hidden');
        }
    }
</script>
<body class="<?php echo $darkModeEnabled ? 'dark-mode' : ''; ?>"> <!-- Ajout de la classe 'dark-mode' si activée -->
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
                <span><?php echo htmlspecialchars($student->getPrenom()) . ' ' . htmlspecialchars($student->getNom()); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Section contenant les différents menus -->
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
        <!-- Contenu de l'Accueil -->
        <div class="<?php echo ($activeSection == '0') ? 'Visible' : 'Contenu'; ?>" id="content-0">
            <h2><?= $translations['welcome_intsup']?></h2><br>
            <p></p><br>
        </div>

        <!-- Contenu des autres sections -->
        <div class="Contenu <?php echo ($activeSection == '1') ? 'Visible' : ''; ?>" id="content-1">
            <?php include('./MissionStage.php')?>
        </div>
        <div class="Contenu <?php echo ($activeSection == '2') ? 'Visible' : 'Contenu'; ?>" id="content-2">

            <?php include_once("StudentManagment.php") ?>

        </div>
        <div class="Contenu <?php echo ($activeSection == '3') ? 'Visible' : ''; ?>" id="content-3">
            <!-- Affichage du livret de suivi -->

            <?php include_once("LivretSuivi.php");?>


        </div>
        <div class="Contenu <?php echo ($activeSection == '4') ? 'Visible' : 'Contenu'; ?>" id="content-4">
            <h2><?= $translations['place']?></h2>

            <button id="PreAgreement"><?= $translations['consult']?></button>
            <?php
            include_once("MentorConsultPreAgreementForm.php"); ?>
            <script src="../View/Agreement/SecretariatConsultPreAgreementForm.js"></script>

            <?php include_once("Documents/Documents.php");?>
            <h2><?= $translations['manage']?></h2>
            <form class="box" method="post" action="" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="form_id" value="uploader_fichier">
                <input type="hidden" name="upload_type" value="file">
                <div class="box__input">
                    <input type="file" name="files[]" id="file-doc" multiple>
                    <button class="box__button" type="submit"><?= $translations['uploadFile']?></button>
                </div>
            </form>

            <div class="file-list">
                <h2><?= $translations['uploadedFile']?></h2>
                <div class="file-grid">
                    <?php foreach ($files as $file): ?>
                        <div class="file-card">
                            <div class="file-info">
                                <strong><?= htmlspecialchars($file['name']) ?></strong>
                                <p><?= round($file['size'] / 1024, 2) ?> KB</p>
                            </div>
                            <form method="get" action="Documents/Download.php">
                                <input type="hidden" name="file" value="<?= htmlspecialchars($file['path']) ?>">
                                <button type="submit" class="download-button"><?= $translations['download']?></button>
                            </form>
                            <form method="post" action="" class="delete-form">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="form_id" value="delete_rapport">
                                <input type="hidden" name="fileId" value="<?= $file['id'] ?>">
                                <button type="submit" class="delete-button"><?= $translations['delete']?></button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <script src="../View/Documents/Documents.js"></script>
            <a href="../View/Agreement/PreAgreementFormCompany.php"><?= $translations['accessForm']?></a>

        </div>

        <!-- Contenu de la Messagerie -->
        <div class="Contenu <?php echo ($activeSection == '5') ? 'Visible' : ''; ?>" id="content-5">
            <div class="messenger">
                <div class="contacts">
                    <div class="search-bar">
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

                <!-- Context menu for message actions -->
                <div id="context-menu" class="context-menu">
                    <ul>
                        <li id="copy-text"><?= $translations['copy']?></li>
                        <li id="delete-message"><?= $translations['delete']?></li>
                    </ul>
                </div>

                <div class="chat-window">
                    <div class="chat-header">
                        <h3 id="chat-header-title"><?= $translations['selectChat']?></h3>
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
                            <input type="text" id="message-input" name="message" placeholder="<?= $translations['tapez message']?>">
                            <button type="submit"><?= $translations['send']?></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="Contenu <?php echo ($activeSection == '6') ? 'Visible' : ' '; ?>" id="content-6">
            <?php include_once("GetNotesMaitreStage.php");?>
    </div>
        <!-- Offres Content -->
        <div class="Contenu <?php echo $activeSection == '7' ? 'Visible' : ''; ?>" id="content-5">
            <a href="../View/Offer/List.php?type=all">
                <button type="button"><?= $translations['seeOffers']?></button>
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
                <button type="button" class="btn btn-danger btn-sm reset-file-btn" id="resetFileBtn" title=<?= $translations['cancelFile']?> style="display: none;">✖️</button>
            </div>
            <button type="submit" class="btn btn-primary btn-block animated-button"><?= $translations['mess_admin']?></button>
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