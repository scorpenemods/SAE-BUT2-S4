<?php
// manage secretariat's page
// Démarre une nouvelle session ou reprend une session existante
global $files;
session_start();

// Inclusion des fichiers nécessaires pour la base de données et les objets Person
require "../Model/Database.php";
require "../Model/Person.php";


// Création d'une nouvelle instance de la classe Database
$database = (Database::getInstance());

// Initialisation du nom d'utilisateur par défaut
$userName = "Guest";
$session_timeout = getenv('SESSION_TIMEOUT');
require_once "../Model/Config.php";

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

// Vérifie si l'utilisateur est connecté et récupère ses données
if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    // Vérifie si l'objet déserialisé est une instance de la classe Person
    if ($person instanceof Person) {
        // Sécurise et affiche le prénom et le nom de la personne connectée
        $userName = htmlspecialchars($person->getPrenom()) . ' ' . htmlspecialchars($person->getNom());
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
$allowedRoles = [4, 5]; // Seuls les utilisateurs avec le rôle 4 et 5 ont accès à cette page
if (!in_array($userRole, $allowedRoles)) {
    // Redirection vers la page d'accès refusé si l'utilisateur n'a pas le bon rôle
    header("Location: AccessDenied.php");
    exit();
}
if (isset($_GET['section'])) {
    $_SESSION['active_section'] = $_GET['section'];
}
// Définit la section active par défaut (Accueil) si aucune n'est spécifiée
$activeSection = isset($_SESSION['active_section']) ? $_SESSION['active_section'] : '0';

// Récupérer les préférences de l'utilisateur
$preferences = $database->getUserPreferences($person->getId());

// Vérifier si le mode sombre est activé dans les préférences
$darkModeEnabled = isset($preferences['darkmode']) && $preferences['darkmode'] == 1 ? true : false;

// Creation des groupes recup les utilisateurs
$studentsGroupe = $database->getAllStudents(true);
$professorsGroupe = $database->getProfessor(true);
$maitresGroupe = $database->getTutor(true);


//------------------------------- Création de compte secrétaire -------------------------------//
$conn = $database->getConnection();

$errorMessage = '';

// Fetch all groups with their members
$groupsWithMembers = $database->getAllGroupsWithMembers();

// Vérifier si une langue est définie dans l'URL, sinon utiliser la session ou le français par défaut
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang; // Enregistrer la langue en session
} else {
    $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr'; // Langue par défaut
}

// Vérification si le fichier de langue existe, sinon charger le français par défaut
$langFile = "./Locales/{$lang}.php";
if (!file_exists($langFile)) {
    $langFile = "../Locales/fr.php";
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

$files = [];

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
    <link rel="stylesheet" href="../View/Principal/Modals.css">
    <link rel="stylesheet" href="../View/Documents/Documents.css">
    <link rel="stylesheet" href="../View/Livretnoah/livretnoah.css">
    <link rel="stylesheet" href="../View/Agreement/SecretariatConsultPreAgreementForm.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include EmojiOneArea -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/emojionearea/3.4.1/emojionearea.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/emojionearea/3.4.1/emojionearea.min.js"></script>

    <!-- Test styles from bootstrap | delete or adjust  -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script JavaScript pour la gestion des utilisateurs -->
    <script src="../View/Principal/userManagement.js"></script>
    <script src="../View/Principal/GroupCreation.js"></script>
    <script src="/View/Principal/GroupMessenger.js"></script>

</head>
<body class="<?php echo $darkModeEnabled ? 'dark-mode' : ''; ?>">
<?php include_once("../View/Header.php");?>

<!-- Section principale contenant les différents modules de l'application -->
<section class="Menus">
    <nav>

        <!-- Boutons de navigation entre les différents contenus de la section -->
        <span onclick="widget(0)" class="widget-button Current"><?= $translations['accueil']?></span>
        <span onclick="widget(1)" class="widget-button"><?= $translations['rapports']?></span>
        <span onclick="widget(2)" class="widget-button"><?= $translations['documents']?></span>
        <span onclick="widget(3)" class="widget-button"><?= $translations['messagerie']?></span>
        <span onclick="widget(4)" class="widget-button"><?= $translations['groupes']?></span>
        <span onclick="widget(5)" class="widget-button"><?= $translations['offres']?></span>
        <span onclick="widget(6)" class="widget-button">Logs</span>
        <?php if ($userRole == 5) { ?>
            <span onclick="widget(7)" class="widget-button"><?= $translations['gestion secrétariat']?></span>
            <span onclick="widget(8)" class="widget-button"><?= $translations['gestion utilisateurs']?></span>
        <?php } ?>

    </nav>
    <div class="Contenus">
        <!-- Contenu de la section Accueil -->
        <div class="Contenu <?php echo $activeSection == '0' ? 'Visible' : ''; ?>" id="content-0">
            <h2><?= $translations['welcome_admin']?></h2><br>
            <p><?= $translations['info_admin']?></p><br>

        </div>


        <!-- Section Rapports -->
        <div class="Contenu <?php echo $activeSection == '1' ? 'Visible' : ''; ?>" id="content-1">
            Contenu Rapports
        </div>

        <!-- Section Documents -->
        <div class="Contenu <?php echo $activeSection == '2' ? 'Visible' : ''; ?>" id="content-2" data-section="documents">
            <h2>Espace conventions :</h2>


            <button id="PreAgreement">Consulter un formulaire de pré-convention</button>
            <button id="PreAgreementToValidate">Consulter les pré-conventions à valider</button>

            <?php //premier bouton
            include_once("SecretariatConsultValidPreAgreementForm.php");
            //second bouton
            include_once("SecretariatConsultInvalidPreAgreementForm.php");
            ?>
            <script src="../View/Agreement/SecretariatConsultPreAgreementForm.js"></script>



            <h2>Vos documents :</h2>
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

        <!-- Contenu de la Messagerie -->
        <div class="Contenu <?php echo $activeSection == '3' ? 'Visible' : ''; ?> animate__animated animate__fadeIn" id="content-3">
            <!-- Messenger Contents -->
            <div class="messenger">
                <div class="container mt-5">
                    <h2 class="text-center mb-4 animate__animated animate__bounceIn">Envoyer un message à tous les utilisateurs</h2>
                    <form id="broadcastMessageForm" enctype="multipart/form-data" method="POST" action="SendMessageToAll.php" class="animate__animated animate__fadeInUp">
                        <div class="form-group">
                            <label for="message" class="form-label">Message :</label>
                            <textarea class="form-control animated-input" id="message" name="message" rows="5" placeholder="Écrivez votre message ici..." required></textarea>
                        </div>
                        <div class="form-group position-relative">
                            <label for="file" class="form-label">Joindre un fichier :</label>
                            <input type="file" class="form-control-file animated-file-input" id="file" name="file">
                            <button type="button" class="btn btn-danger btn-sm reset-file-btn" id="resetFileBtn" title="Annuler le fichier sélectionné">✖️</button>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block animated-button">Envoyer à tous les utilisateurs</button>
                    </form>
                </div>
            </div>
        </div>



        <!-- Section Groupes -->
        <div class="Contenu <?php echo $activeSection == '4' ? 'Visible' : ''; ?>" id="content-4">
            <!-- List of existing groups -->
            <div class="group-list">
                <h3>Groupes existants</h3>
                <?php if (!empty($groupsWithMembers)): ?>
                    <ul class="group-list-ul">
                        <?php foreach ($groupsWithMembers as $group): ?>
                            <li class="group-item">
                                <div class="group-header">
                                    <strong><?php echo htmlspecialchars($group['group_name']); ?></strong>
                                    <!-- Buttons to modify or delete the group -->
                                    <div class="group-actions">
                                        <!-- Fonction Desactiver <button onclick="openEditGroupModal(<?php echo $group['group_id']; ?>)">Modifier</button>-->
                                        <button onclick="deleteGroup(<?php echo $group['group_id']; ?>)">Supprimer</button>
                                        <button onclick="endStage(<?php echo $group['group_id']; ?>)">Terminer</button>
                                    </div>
                                </div>
                                <ul class="member-list">
                                    <?php foreach ($group['members'] as $member): ?>
                                        <li><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Aucun groupe n'a été créé pour le moment.</p>
                <?php endif; ?>
            </div>

            <!-- Bouton pour ouvrir la fenêtre modale de création de groupe -->
            <button class="open-create-group-modal">Créer un nouveau groupe</button>
        </div>

        <!-- Offres Content -->
        <div class="Contenu <?php echo $activeSection == '5' ? 'Visible' : ''; ?>" id="content-5">
            Contenu Offres
            <a href="../View/Offer/List.php?type=all">
                <button type="button">Voir les offres</button>
            </a>
        </div>

        <!-- Logs Section -->
        <div class="Contenu <?php echo $activeSection == '6' ? 'Visible' : ''; ?>" id="content-6">
            <h2>Journal des activités</h2>
            <div class="logs-container">
                <?php
                // Fetch logs from the database
                $conn = $database->getConnection();
                $logsQuery = "SELECT Logs.*, User.nom, User.prenom FROM Logs JOIN User ON Logs.user_id = User.id ORDER BY Logs.date DESC";
                $stmt = $conn->prepare($logsQuery);
                $stmt->execute();
                $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($logs)) {
                    echo '<ul class="timeline">';
                    foreach ($logs as $log) {
                        echo '<li class="timeline-item">';
                        echo '<div class="timeline-content">';
                        echo '<h3 class="timeline-title">' . htmlspecialchars($log['prenom'] . ' ' . $log['nom']) . '</h3>';
                        echo '<p class="timeline-description">' . htmlspecialchars($log['description']) . '</p>';
                        echo '<span class="timeline-date">' . htmlspecialchars(date("d/m/Y H:i", strtotime($log['date']))) . '</span>';
                        echo '</div>';
                        echo '</li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p>Aucune activité enregistrée.</p>';
                }
                ?>
            </div>
        </div>

        <!-- Section Gestion des secrétaires -->
        <div class="Contenu <?php echo $activeSection == '7' ? 'Visible' : ''; ?>" id="content-7">
            <div class="user-management">
                <!-- Section pour la création de nouveau secrétaire -->
                <div class="pending-requests">
                    <button id="showButton" onclick="showForm()"><?= $translations['nouveau_secretaire']?></button>
                    <!-- Form -->
                    <div id="secretariatCreation" style="display: none;">
                        <form action="NewSecretaraitGestion.php" method="POST">
                            <input type="hidden" name="redirect_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                            <input type="hidden" name="form_id" value="create_secretary">

                            <!-- Hidden role input field (for secretariat role) -->
                            <input type="hidden" name="choice" value="secretariat">

                            <label for="activite">Fonction :</label> <label style="color: red"> *</label>
                            <input type="text" id="activite" name="function" required><br><br>

                            <label for="nom">Nom :</label> <label style="color: red"> *</label>
                            <input type="text" id="nom" name="name" required><br><br>

                            <label for="surname">Prénom :</label> <label style="color: red"> *</label>
                            <input type="text" id="surname" name="firstname" required><br><br>

                            <label for="email">Email :</label> <label style="color: red"> *</label>
                            <input type="email" id="email" name="email" required><br><br>

                            <label for="phone">Téléphone :</label>
                            <input type="text" id="phone" name="phone"><br><br>

                            <label for="mdp">Mot de passe :</label> <label style="color: red"> *</label>
                            <input type="password" id="mdp" name="password" required><br><br>

                            <label for="mdpconfirm">Confirmation mot de passe:</label> <label style="color: red"> *</label>
                            <input type="password" id="mdpconfirm" name="confirm_password" required><br><br>

                            <button type="submit">Enregistrer</button>
                            <a href="#" onclick="hideForm()" style="text-decoration: none"> Annuler</a>
                        </form>
                    </div>

                </div>
                <!-- Section pour afficher les secrétaires actifs dans le système -->
                <div class="active-users">
                    <h2>Secrétaires actifs</h2>
                    <?php
                    // Récupération des secrétaires actifs depuis la base de données
                    $activeUsers = $database->getActiveUsers();
                    foreach ($activeUsers as $user) {
                        // Affichage de chaque secrétaire actif avec ses détails
                        if ($user['role'] == 4) {
                            echo "<div class='active-user'>";
                            echo "<p><strong>Nom:</strong> " . htmlspecialchars($user['nom']) . "</p>";
                            echo "<p><strong>Prénom:</strong> " . htmlspecialchars($user['prenom']) . "</p>";
                            echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</p>";
                            echo "<p><strong>Telephone:</strong> " . htmlspecialchars($user['telephone']) . "</p>";
                            echo "<p><strong>Activité :</strong> " . htmlspecialchars($user['activite']) . "</p>";
                            // Bouton pour supprimer le secrétaire du système
                            echo "<button onclick='deleteUser(" . $user['id'] . ")'>🗑️ Supprimer</button>";
                            echo "</div>";
                        }
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Section Gestion des utilisateurs -->
        <div class="Contenu <?php echo $activeSection == '8' ? 'Visible' : ''; ?>" id="content-8">
            <div class="user-management">
                <!-- Section pour déposer un fichier CSV -->
                <div class="csv-upload" style="padding-top: 25px">
                    <h2>Importer des utilisateurs via CSV</h2>
                    <form action="Batch.php" method="post" enctype="multipart/form-data">
                        <label for="csvFile">Sélectionner un fichier CSV:</label>
                        <input type="file" name="csv_file" id="csvFile" accept=".csv" required>
                        <button type="submit">📂 Importer le CSV</button>
                    </form>

                    <p>Télécharger un fichier CSV vide : <a href="../Model/Generer_CSV.php">📥 Télécharger le modèle</a> </p>
                </div>

                <!-- Section pour les demandes d'utilisateur en attente d'approbation -->
                <div class="pending-requests">
                    <h2>Demandes en attente</h2>
                    <?php
                    // Récupération des utilisateurs en attente depuis la base de données
                    $pendingUsers = $database->getPendingUsers();
                    foreach ($pendingUsers as $user) {
                        // Affichage de chaque utilisateur en attente avec ses détails
                        if ($user['role'] != 4) {
                            echo "<div class='user-request'>";
                            echo "<p><strong>Nom:</strong> " . htmlspecialchars($user['nom']) . "</p>";
                            echo "<p><strong>Prénom:</strong> " . htmlspecialchars($user['prenom']) . "</p>";
                            echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</p>";
                            echo "<p><strong>Telephone:</strong> " . htmlspecialchars($user['telephone']) . "</p>";
                            echo "<p><strong>Activité :</strong> " . htmlspecialchars($user['activite']) . "</p>";
                            echo "<p><strong>Statut Email:</strong> " . ($user['valid_email'] ? 'Validé' : 'Non Validé') . "</p>";

                            switch (htmlspecialchars($user['role'])) {
                                case 1:
                                    echo "<p><strong>Rôle:</strong> " . "Etudiant" . "</p>";
                                    break;
                                case 2:
                                    echo "<p><strong>Rôle:</strong> " . "Professeur" . "</p>";
                                    break;
                                case 3:
                                    echo "<p><strong>Rôle:</strong> " . "Maitre Stage" . "</p>";
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
                        if ($user['role'] != 4 && $user['role'] != 5) {
                            echo "<div class='active-user'>";
                            echo "<p><strong>Nom:</strong> " . htmlspecialchars($user['nom']) . "</p>";
                            echo "<p><strong>Prénom:</strong> " . htmlspecialchars($user['prenom']) . "</p>";
                            echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</p>";
                            echo "<p><strong>Telephone:</strong> " . htmlspecialchars($user['telephone']) . "</p>";
                            echo "<p><strong>Activité :</strong> " . htmlspecialchars($user['activite']) . "</p>";
                            switch (htmlspecialchars($user['role'])) {
                                case 1:
                                    echo "<p><strong>Rôle:</strong> " . "Etudiant" . "</p>";
                                    break;
                                case 2:
                                    echo "<p><strong>Rôle:</strong> " . "Professeur" . "</p>";
                                    break;
                                case 3:
                                    echo "<p><strong>Rôle:</strong> " . "Maitre Stage" . "</p>";
                                    break;
                                default:
                                    echo "<p><strong>Rôle:</strong> " . "Inconnue" . "</p>";
                                    break;
                            }
                            // Bouton pour supprimer l'utilisateur du système
                            echo "<button onclick='deleteUser(" . $user['id'] . ")'>🗑️ Supprimer</button>";
                            echo "</div>";
                        }
                    }
                    ?>
                </div>

            </div>
        </div>

    </div>
</section>

<!-- ------------------------------ ! Modal windows out of the page section ! -------------------------------------------------------------------  -->

<!-- Fenêtre modale pour créer un nouveau groupe -->
<div id="createGroupModal" class="modal">
    <div class="modal-content">
        <h2>Créer un nouveau groupe</h2>

        <!-- Form for creating the group -->
        <form id="createGroupForm" method="POST" action="#">
            <!-- Hidden input to identify the form -->
            <input type="hidden" name="create_group" value="1">

            <!-- Selection of students -->
            <label for="student-select">Étudiant(s):</label>
            <select id="student-select" name="student_ids[]" multiple required>
                <?php foreach ($studentsGroupe as $studentGroupe): ?>
                    <option value="<?php echo $studentGroupe->getId(); ?>"><?php echo htmlspecialchars($studentGroupe->getPrenom()) . ' ' . htmlspecialchars($studentGroupe->getNom()); ?></option>
                <?php endforeach; ?>
            </select>

            <!-- Selection of professor -->
            <label for="professor-select">Professeur :</label>
            <select id="professor-select" name="professor_id" required>
                <option value="">Sélectionnez un professeur</option>
                <?php foreach ($professorsGroupe as $professorGroupe): ?>
                    <option value="<?php echo $professorGroupe->getId(); ?>"><?php echo htmlspecialchars($professorGroupe->getPrenom()) . ' ' . htmlspecialchars($professorGroupe->getNom()); ?></option>
                <?php endforeach; ?>
            </select>

            <!-- Selection of internship supervisor -->
            <label for="maitre-select">Maître de stage :</label>
            <select id="maitre-select" name="maitre_id" required>
                <option value="">Sélectionnez un maître de stage</option>
                <?php foreach ($maitresGroupe as $maitreGroupe): ?>
                    <option value="<?php echo $maitreGroupe->getId(); ?>"><?php echo htmlspecialchars($maitreGroupe->getPrenom()) . ' ' . htmlspecialchars($maitreGroupe->getNom()); ?></option>
                <?php endforeach; ?>
            </select>

            <!-- Button to submit the form and create the group -->
            <button type="submit" class="submit-group-button">Créer le groupe</button>
        </form>

        <!-- Zone pour afficher le message de résultat -->
        <div id="resultMessage"></div>
        <!-- Bouton pour fermer la fenêtre modale -->
        <span class="close-modal">&times;</span>

    </div>
</div>


<!-- Modal for editing a group -->
<div id="editGroupModal" class="modal">
    <div class="modal-content">
        <h2>Modifier le groupe</h2>
        <form id="editGroupForm" method="POST" action="#">
            <!-- Hidden fields -->
            <input type="hidden" name="edit_group" value="1">
            <input type="hidden" name="group_id" id="edit-group-id">
            <!-- Fields for selecting new members -->
            <label for="edit-student-select">Étudiant(s):</label>
            <select id="edit-student-select" name="student_ids[]" multiple required>
                <?php foreach ($studentsGroupe as $studentGroupe): ?>
                    <option value="<?php echo $studentGroupe->getId(); ?>"><?php echo htmlspecialchars($studentGroupe->getPrenom()) . ' ' . htmlspecialchars($studentGroupe->getNom()); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="edit-professor-select">Professeur :</label>
            <select id="edit-professor-select" name="professor_id" required>
                <option value="">Sélectionnez un professeur</option>
                <?php foreach ($professorsGroupe as $professorGroupe): ?>
                    <option value="<?php echo $professorGroupe->getId(); ?>"><?php echo htmlspecialchars($professorGroupe->getPrenom()) . ' ' . htmlspecialchars($professorGroupe->getNom()); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="edit-maitre-select">Maître de stage :</label>
            <select id="edit-maitre-select" name="maitre_id" required>
                <option value="">Sélectionnez un maître de stage</option>
                <?php foreach ($maitresGroupe as $maitreGroupe): ?>
                    <option value="<?php echo $maitreGroupe->getId(); ?>"><?php echo htmlspecialchars($maitreGroupe->getPrenom()) . ' ' . htmlspecialchars($maitreGroupe->getNom()); ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="submit-group-button">Enregistrer les modifications</button>
        </form>
        <div id="editResultMessage"></div>
        <!-- Button to close the modal window -->
        <span class="close-modal">&times;</span>
    </div>
</div>
<footer>
    <?php include '../View/Footer.php'; ?>
</footer>

<script>

    // Ajouter une classe d'animation
    document.querySelectorAll('.form-control, .form-control-file').forEach(element => {
        element.addEventListener('focus', () => {
            element.classList.add('animated-border');
        });

        element.addEventListener('blur', () => {
            element.classList.remove('animated-border');
        });
    });

    // Animation de validation du fichier lors de la sélection
    document.getElementById('file').addEventListener('change', function() {
        if (this.files.length > 0) {
            // Afficher le bouton d'annulation
            document.getElementById('resetFileBtn').style.display = 'block';
        } else {
            document.getElementById('resetFileBtn').style.display = 'none';
        }
    });

    // Fonction pour réinitialiser le champ de fichier lorsque le bouton d'annulation est cliqué
    document.getElementById('resetFileBtn').addEventListener('click', function() {
        const fileInput = document.getElementById('file');
        fileInput.value = ''; // Réinitialise le champ de fichier
        this.style.display = 'none'; // Cache le bouton d'annulation
    });

    // Animation lors de la saisie du texte
    const messageInput = document.getElementById('message');
    messageInput.addEventListener('input', () => {
        messageInput.classList.add('typing-animation');
        clearTimeout(messageInput.typingTimer);
        messageInput.typingTimer = setTimeout(() => {
            messageInput.classList.remove('typing-animation');
        }, 500);
    });
</script>
</body>
</html>

