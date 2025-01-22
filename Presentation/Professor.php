<?php
// manage professor's page
global $files;
ob_start();
global $database;
session_start();
require "../Model/Database.php";
require "../Model/Person.php";

$database = (Database::getInstance());

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

// -- Actions AJAX de gestion des rencontres --
if (isset($_GET['action']) && $_GET['action'] === 'get_meetings') {
    // On récupère le followUpId
    $fid = isset($_GET['followup_id']) ? (int)$_GET['followup_id'] : 0;
    if (!$fid) {
        echo json_encode(['status'=>'error','message'=>'NoFollowUpID']);
        exit();
    }
    $followUpBook = $database->getFollowUpBook($fid);
    if (!$followUpBook) {
        echo json_encode(['status'=>'error','message'=>'NoFollowUpID']);
        exit();
    }
    $meetings = $database->getMeetingsByFollowUp($fid);
    $result = [];
    foreach($meetings as $m) {
        $mId = $m['id'];
        $qcm = $database->getQCMByMeeting($mId);
        $txt = $database->getTextsByMeeting($mId);
        $result[] = [
            'id'           => $mId,
            'name'         => $m['name'],
            'meeting_date' => $m['meeting_date'],
            'end_date'     => $m['end_date'],
            'qcm'          => $qcm,
            'texts'        => $txt
        ];
    }
    echo json_encode(['status'=>'success','meetings'=>$result]);
    exit();
}

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'save_meeting') {
        $followUpId  = isset($_POST['followup_id']) ? (int)$_POST['followup_id'] : 0;
        if (!$followUpId) {
            echo json_encode(['status'=>'error','message'=>'NoFollowUpID']);
            exit();
        }
        $meetingDate  = $_POST['meeting'] ?? null;
        $endMeeting   = $_POST['end_meeting'] ?? null;
        $meetingName  = $_POST['meeting_name'] ?? 'Nouvelle rencontre';
        $lieu         = $_POST['Lieu'] ?? '';
        $validation   = 0;

        $followUpBook = $database->getFollowUpBook($followUpId);
        if (!$followUpBook) {
            echo json_encode(['status'=>'error','message'=>'NoFollowUpID']);
            exit();
        }
        $startDate = $followUpBook['start_date'];
        $endDate   = $endMeeting ? $endMeeting : $followUpBook['end_date'];

        // Insert MeetingBook
        $meetingId = $database->insertMeetingBook($followUpId, $meetingName, $startDate, $endDate, $meetingDate, $validation);

        // Lieu => MeetingQCM
        if (!empty($lieu)) {
            $database->insertMeetingQCM($meetingId, 'Lieu', '', $lieu);
        }
        // QCM dynamiques
        if (isset($_POST['qcm']) && is_array($_POST['qcm'])) {
            foreach($_POST['qcm'] as $q) {
                $title   = $q['title'] ?? '';
                $choices = $q['choices'] ?? '';
                $other   = $q['other_choice'] ?? '';
                if (!empty($title)) {
                    $database->insertMeetingQCM($meetingId, $title, $choices, $other);
                }
            }
        }
        // Textes
        if (isset($_POST['texts']) && is_array($_POST['texts'])) {
            foreach($_POST['texts'] as $t) {
                $tTitle = $t['title'] ?? '';
                $tResp  = $t['response'] ?? '';
                if (!empty($tTitle)) {
                    $database->insertMeetingText($meetingId, $tTitle, $tResp);
                }
            }
        }
        // Commentaires
        if (isset($_POST['commentaires']) && is_array($_POST['commentaires'])) {
            foreach($_POST['commentaires'] as $c) {
                $cTitle = $c['title'] ?? '';
                $cResp  = $c['response'] ?? '';
                if (!empty($cTitle)) {
                    $database->insertMeetingText($meetingId, $cTitle, $cResp);
                }
            }
        }
        // Questions/Réponses
        if (isset($_POST['questrep']) && is_array($_POST['questrep'])) {
            foreach($_POST['questrep'] as $qr) {
                $qrTitle = $qr['title'] ?? '';
                $qrResp  = $qr['response'] ?? '';
                if (!empty($qrTitle)) {
                    $database->insertMeetingText($meetingId, $qrTitle, $qrResp);
                }
            }
        }

        echo json_encode(['status'=>'success','message'=>'Rencontre sauvegardée avec succès']);
        exit();
    }

    if ($action === 'save_bilan') {
        $followUpId = isset($_POST['followup_id']) ? (int)$_POST['followup_id'] : 0;
        if (!$followUpId) {
            echo json_encode(['status'=>'error','message'=>'NoFollowUpID']);
            exit();
        }
        $followUpBook = $database->getFollowUpBook($followUpId);
        if (!$followUpBook) {
            echo json_encode(['status'=>'error','message'=>'NoFollowUpID']);
            exit();
        }
        $meetings = $database->getMeetingsByFollowUp($followUpId);
        $bilanMeetingId = null;
        foreach($meetings as $m) {
            if ($m['name']==='Finalisation du livret') {
                $bilanMeetingId = $m['id'];
                break;
            }
        }
        if(!$bilanMeetingId) {
            $bilanMeetingId = $database->insertMeetingBook(
                $followUpId,
                'Finalisation du livret',
                $followUpBook['start_date'],
                $followUpBook['end_date'],
                date('Y-m-d'),
                0
            );
        }
        // competences
        $competences = [
            ["nom"=>"Adaptation à l'entreprise", "option"=>"option1", "comment"=>"table1"],
            ["nom"=>"Ponctualité", "option"=>"option2", "comment"=>"table2"],
            ["nom"=>"Motivation pour le travail", "option"=>"option3", "comment"=>"table3"],
            ["nom"=>"Initiatives personnelles", "option"=>"option4", "comment"=>"table4"],
            ["nom"=>"Qualité du travail", "option"=>"option5", "comment"=>"table5"],
            ["nom"=>"Intérêt pour la découverte de l'entreprise", "option"=>"option6", "comment"=>"table6"]
        ];
        $bilanTexts = $database->getTextsByMeeting($bilanMeetingId);
        $existingTexts = [];
        foreach($bilanTexts as $bt) {
            $existingTexts[$bt['title']] = $bt;
        }
        foreach($competences as $comp) {
            $compName    = $comp['nom'];
            $niveau      = $_POST[$comp['option']] ?? '';
            $commentaire = $_POST[$comp['comment']] ?? '';
            $response    = "Niveau: $niveau | Commentaire: $commentaire";
            if (isset($existingTexts[$compName])) {
                $tid = $existingTexts[$compName]['id'];
                $database->updateMeetingText($tid, $response);
            } else {
                $database->insertMeetingText($bilanMeetingId, $compName, $response);
            }
        }
        echo json_encode(['status'=>'success','message'=>'Bilan sauvegardé avec succès.']);
        exit();
    }
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

        fetch('livretnoah.php', {
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
        <div class="Contenu <?php echo ($activeSection == '1') ? 'Visible' : 'Contenu'; ?>" id="content-1">Contenu des missions de stage</div>
        <div class="Contenu <?php echo ($activeSection == '2') ? 'Visible' : 'Contenu'; ?>" id="content-2">

            <?php include_once("StudentManagment.php") ?>

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
<?php
ob_end_flush();
?>