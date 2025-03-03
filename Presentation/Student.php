<?php
// Manage student page
global $files;
date_default_timezone_set('Europe/Paris');
// Démarre la session au début du script pour gérer les informations utilisateur
session_start();
// Inclure les fichiers nécessaires pour les classes Database et Person
require_once "../Model/Database.php";
require_once "../Model/Person.php";

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

// Vérifie que l'utilisateur est connecté en regardant si une session utilisateur est active
if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']); // Récupère l'objet Person stocké en session
    if ($person instanceof Person) { // Vérifie que l'objet est bien une instance de Person
        $userName = htmlspecialchars($person->getPrenom()) . ' ' . htmlspecialchars($person->getNom()); // Définit le nom de l'utilisateur en utilisant son prénom et son nom
        $senderId = $person->getId(); // Récupère l'ID de l'utilisateur pour les requêtes de base de données
    }
} else {
    // Redirige l'utilisateur vers la page de déconnexion s'il n'est pas connecté
    header("Location: Logout.php");
    exit();
}

// Instanciation de l'objet Database (singleton pour une seule instance de connexion)
$userRole = $person->getRole();
$database = (Database::getInstance());

// Récupération des préférences de l'utilisateur depuis la base de données
$preferences = $database->getUserPreferences($senderId);
$darkModeEnabled = isset($preferences['darkmode']) && $preferences['darkmode'] == 1 ? 'checked' : ''; // Vérifie si le mode sombre est activé dans les préférences utilisateur

// Si une section est spécifiée dans l'URL, elle est stockée dans la session pour gérer l'affichage de la section active
if (isset($_GET['section'])) {
    $_SESSION['active_section'] = $_GET['section'];
}

// Définit la section active par défaut sur 'Accueil' si aucune section n'est spécifiée
$activeSection = isset($_SESSION['active_section']) ? $_SESSION['active_section'] : '0';

// Récupération des messages entre l'utilisateur actuel et un destinataire (défini dynamiquement)
$receiverId = 2; // À définir dynamiquement en fonction de l'interface utilisateur
$messages = $database->getMessages($senderId, $receiverId);

// Récupération des notes de l'utilisateur depuis la base de données
$notes = $database->getNotes($senderId);

// Récupération des différents stages de l'utilisateur depuis la base de données
$stages = $database->getStages($senderId);

if (isset($_POST['go'])) {
    $infos = $database->getUserById($_POST['go']);
    $notes = $database->getNotes($infos['id']);
}


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
    <title>Le Petit Stage</title>
    <link rel="stylesheet" href="/View/Principal/Principal.css">
    <link rel="stylesheet" href="../View/Agreement/SecretariatConsultPreAgreementForm.css">
    <script src="/View/Principal/Principal.js" defer></script>
    <script src="../View/Principal/LivretSuivi.js"></script>
    <link rel="stylesheet" href="../View/Documents/Documents.css">
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include EmojiOneArea -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/emojionearea/3.4.1/emojionearea.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/emojionearea/3.4.1/emojionearea.min.js"></script>
    <script src="../View/Livretnoah/LivretSuiviContenue.js"
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
<body class="<?php echo $darkModeEnabled ? 'dark-mode' : ''; ?>">
<?php include_once("../View/Header.php");?>
<div class="sidebar-toggle" id="sidebar-toggle" onclick="sidebar()">&#9664;</div>
<div class="sidebar" id="sidebar">
    <form method="post" action="#" class="students">
        <?php foreach ($stages as $stage): ?>
            <div class="student">
                <button type="submit" value="<?php echo $stage[1];?>" name="go"><?php echo "Stage de l'année : $stage[0]";?></button>
            </div>
        <?php endforeach; ?>
    </form>
</div>
<section class="Menus" id="Menus">
    <nav>

        <span onclick="widget(0)" class="widget-button Current"><?= $translations['accueil']?></span>
        <span onclick="widget(1)" class="widget-button"><?= $translations['mission stage']?></span>
        <span onclick="widget(2)" class="widget-button"><?= $translations['livret suivi']?></span>
        <span onclick="widget(3)" class="widget-button"><?= $translations['offres']?></span>
        <span onclick="widget(4)" class="widget-button"><?= $translations['documents']?></span>
        <span onclick="widget(5)" class="widget-button"><?= $translations['messagerie']?></span>
        <span onclick="widget(6)" class="widget-button"><?= $translations['notes']?></span>
    </nav>
    <div class="Contenus">
        <!-- Accueil Content -->
        <div class="Contenu <?php echo $activeSection == '0' ? 'Visible' : ''; ?>" id="content-0">
            <h2><?= $translations['welcome_message']?></h2><br>
            <p>
                <?= $translations['info_stud']?>
            </p><br>
            <ul>
                <li><strong><?= $translations['livret suivi']?>:</strong> <?= $translations['livret_info']?></li><br>
                <li><strong><?= $translations['offres']?>:</strong> <?= $translations['offres_info']?></li><br>
                <li><strong><?= $translations['documents']?>:</strong> <?= $translations['documents_info']?></li><br>
                <li><strong><?= $translations['messagerie']?>:</strong> <?= $translations['messagerie_info']?></li><br>
            </ul><br>
        </div>


        <!-- Missions Content -->
        <div class="Contenu <?php echo $activeSection == '1' ? 'Visible' : ''; ?>" id="content-1">
            <?php include('./MissionStage.php')?>
        </div>

        <!-- Livret de suivi Content -->
        <div class="Contenu <?php echo $activeSection == '2' ? 'Visible' : ''; ?>" id="content-5">
            <!-- Affichage du livret de suivi -->

            <?php
            include_once("LivretSuivi.php");
            ?>
        </div>

        <!-- Offres Content -->
        <div class="Contenu <?php echo $activeSection == '3' ? 'Visible' : ''; ?>" id="content-3">
            <?= $translations['contenu offres']?>
            <a href="../View/Offer/List.php?type=all">
                <button type="button"><?= $translations['voir offres']?></button>
            </a>
        </div>

        <!-- Documents Content -->
        <div class="Contenu <?php echo $activeSection == '4' ? 'Visible' : ''; ?>" id="content-4">
            <h2>Espace convention :</h2>
            <!-- Bouton qui affiche la fenêtre modale -->
            <button id="PreAgreement">Consulter vos formulaire de pré-convention</button>

            <?php //premier bouton
            include_once("StudentConsultPreAgreement.php");
            ?>
            <script src="../View/Agreement/SecretariatConsultPreAgreementForm.js"></script>

            <form action="PreAgreementFormStudent.php" method="post">
                <button type="submit">Demander un nouveau formulaire de pré-convention</button>
            </form>


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


        <!-- Messagerie Content -->
        <div class="Contenu <?php echo $activeSection == '5' ? 'Visible' : ''; ?>" id="content-2">
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

        <!-- Notes Content -->
        <div class="Contenu <?php echo $activeSection == '6' ? 'Visible' : ''; ?>" id="content-6">
            <div class="notes-container">
                <table class="notes-table">
                    <?php
                    $noter = "";
                    foreach ($notes as $note):
                        $noter = $note->getNote();
                    endforeach;
                    if($noter != ""){
                        echo '<tr class="lsttitlenotes">';
                        echo '<th>'.$translations['sujet'].'</th>';
                        echo '<th>'.$translations['note'].'</th>';
                        echo '<th>'.$translations['coef'].'</th>';
                        echo '</tr>';
                        foreach ($notes as $note):
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($note->getSujet()); '</td>';
                            echo '<td>' . htmlspecialchars($note->getNote()) . " / 20"; '</td>';
                            echo '<td>' . htmlspecialchars($note->getCoeff()); '</td>';
                            echo '</tr>';
                        endforeach;

                    }
                    else {
                        echo '<p class="noNotes">' . $translations['aucune note'] . '</p>';
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>
</section>
<?php include '../View/Footer.php'; ?>

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

<script src="/View/Principal/deleteMessage.js"></script>
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





