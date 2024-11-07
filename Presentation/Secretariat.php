<?php
// Démarre une nouvelle session ou reprend une session existante
session_start();

// Inclusion des fichiers nécessaires pour la base de données et les objets Person
require "../Model/Database.php";
require "../Model/Person.php";

// Création d'une nouvelle instance de la classe Database
$database = (Database::getInstance());

// Initialisation du nom d'utilisateur par défaut
$userName = "Guest";

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
$allowedRoles = [4]; // Seuls les utilisateurs avec le rôle 4 ont accès à cette page
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
$preferences = $database->getUserPreferences($person->getUserId());

// Vérifier si le mode sombre est activé dans les préférences
$darkModeEnabled = isset($preferences['darkmode']) && $preferences['darkmode'] == 1 ? true : false;

// Creation des groupes recup les utilisateurs
$students = $database->getAllStudents() ?? [];
$professors = $database->getProfessor() ?? [];
$maitres = $database->getTutor() ?? [];


//------------------------------- Création de compte secrétaire -------------------------------//
$conn = $database->getConnection();

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Initialise l'activité du user
    $function = isset($_POST['function']) ? htmlspecialchars(trim($_POST['function'])) : '';

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $name = htmlspecialchars(trim($_POST['name']));
    $firstname = htmlspecialchars(trim($_POST['firstname']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Valide l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Adresse email invalide.';
    }

    // Valide mdp
    if ($password !== $confirmPassword) {
        $errorMessage = 'Les mots de passe ne correspondent pas!';
    }

    // Check si l'email exist déjà
    $queryCheckEmail = "SELECT COUNT(*) FROM User WHERE email = :email";
    $stmtCheck = $conn->prepare($queryCheckEmail);
    $stmtCheck->bindParam(':email', $email);
    $stmtCheck->execute();
    $emailExists = $stmtCheck->fetchColumn();

    if ($emailExists > 0) {
        $errorMessage = 'Cet email est déjà enregistré. Veuillez utiliser un autre email.';
    }

    if (!$errorMessage) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Inserer user
        $query = "INSERT INTO User (nom, prenom, email, telephone, role, activite, valid_email, status_user, last_connexion, account_creation) 
                  VALUES (:nom, :prenom, :email, :telephone, 4, :activite, 0, 1, NOW(), NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':nom', $name);
        $stmt->bindValue(':prenom', $firstname);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':telephone', $phone);
        $stmt->bindValue(':activite', $function);

        if ($stmt->execute()) {
            $userID = $conn->lastInsertId();

            // Inserer mdp
            $queryPass = "INSERT INTO Password (user_id, password_hash, actif) VALUES (:user_id, :password_hash, 1)";
            $stmtPass = $conn->prepare($queryPass);
            $stmtPass->bindValue(':user_id', $userID);
            $stmtPass->bindValue(':password_hash', $hashedPassword);

            if ($stmtPass->execute()) {
                $_SESSION['user_email'] = $email;
                $_SESSION['user_id'] = $userID;
                $_SESSION['user_name'] = $name . " " . $firstname;

                // Ajout de log pour vérifier que l'étape est atteinte
                echo "<script>window.location.reload();</script>";
                exit();
            } else {
                $errorMessage = 'Erreur lors de l\'insertion du mot de passe.';
            }
        } else {
            $errorMessage = 'Erreur lors de la création de l\'utilisateur.';
        }
    }
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
<body class="<?php echo $darkModeEnabled ? 'dark-mode' : ''; ?>">
<header class="navbar">
    <div class="navbar-left">
        <!-- Affichage du logo et du nom de l'application -->
        <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
        <span class="app-name">Le Petit Stage - Secrétariat</span>
    </div>
    <div class="navbar-right">
        <button class="mainbtn" >
            <img src="../Resources/Notif.png" alt="Settings">
        </button>
        <!-- Affichage du nom de l'utilisateur connecté et contrôles pour changer la langue et le thème -->
        <p><?php echo $userName; ?></p>
        <label class="switch">
            <input type="checkbox" id="language-switch" onchange="toggleLanguage()">
            <span class="slider round">
                <span class="switch-sticker">🇫🇷</span>
                <span class="switch-sticker switch-sticker-right">🇬🇧</span>
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
        <span onclick="window.location.href='Secretariat.php?section=0'" class="widget-button <?php echo $activeSection == '0' ? 'Current' : ''; ?>" id="content-0">Accueil</span>
        <span onclick="window.location.href='Secretariat.php?section=1'" class="widget-button <?php echo $activeSection == '1' ? 'Current' : ''; ?>" id="content-1">Gestion Secrétariat</span>
        <span onclick="window.location.href='Secretariat.php?section=2'" class="widget-button <?php echo $activeSection == '2' ? 'Current' : ''; ?>" id="content-2">Gestion Utilisateurs</span>
        <span onclick="window.location.href='Secretariat.php?section=3'" class="widget-button <?php echo $activeSection == '3' ? 'Current' : ''; ?>" id="content-3">Rapports</span>
        <span onclick="window.location.href='Secretariat.php?section=4'" class="widget-button <?php echo $activeSection == '4' ? 'Current' : ''; ?>" id="content-4">Documents</span>
        <span onclick="window.location.href='Secretariat.php?section=5'" class="widget-button <?php echo $activeSection == '5' ? 'Current' : ''; ?>" id="content-5">Messagerie</span>
        <span onclick="window.location.href='Secretariat.php?section=6'" class="widget-button <?php echo $activeSection == '6' ? 'Current' : ''; ?>" id="content-6">Groupes</span>

    </nav>
    <div class="Contenus">
        <!-- Contenu de la section Accueil -->
        <div class="Contenu <?php echo $activeSection == '0' ? 'Visible' : ''; ?>" id="content-0">
            <h2>Bienvenue sur la plateforme Secrétariat!</h2><br>
            <p>Gérez les utilisateurs, consultez les documents et accédez aux rapports des stages.</p><br>
        </div>


        <!-- Section Gestion des secrétaires -->
        <div class="Contenu <?php echo $activeSection == '1' ? 'Visible' : ''; ?>" id="content-1">
            <div class="user-management">
                <!-- Section pour la création de nouveau secrétaire -->
                <div class="pending-requests">
                    <button id="showButton" onclick="showForm()"">Nouveau secrétaire</button>
                    <!-- Form -->
                    <div id="secretariatCreation" style="display: none;">
                        <form action="Secretariat.php" method="POST">
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
        <div class="Contenu <?php echo $activeSection == '2' ? 'Visible' : ''; ?>" id="content-2">
            <div class="user-management">
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
                        if ($user['role'] != 4) {
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
                <!-- Section pour déposer un fichier CSV -->
                <div class="csv-upload">
                    <h2>Importer des utilisateurs via CSV</h2>
                    <form action="Batch.php" method="post" enctype="multipart/form-data">
                        <label for="csvFile">Sélectionner un fichier CSV:</label>
                        <input type="file" name="csv_file" id="csvFile" accept=".csv" required>
                        <button type="submit">📂 Importer le CSV</button>
                    </form>

                    <p>Le fichier CSV doit contenir les colonnes suivantes : Nom, Prénom, Email, Rôle, Activité, Téléphone.</p>
                </div>
            </div>
        </div>
        <!-- Section Rapports -->
        <div class="Contenu <?php echo $activeSection == '3' ? 'Visible' : ''; ?>" id="content-3">
            Contenu Rapports
        </div>
        <!-- Section Documents -->
        <div class="Contenu <?php echo $activeSection == '4' ? 'Visible' : ''; ?>" id="content-4">
            Contenu Documents
        </div>



        <!-- Contenu de la Messagerie -->
        <div class="Contenu <?php echo $activeSection == '5' ? 'Visible' : ''; ?>" id="content-5">
            <!-- Messenger Contents -->
            <div class="messenger">
                <div class="contacts">
                    <div class="search-bar">
                        <label for="search-input"></label>
                        <input type="text" id="search-input" placeholder="Rechercher des contacts..." onkeyup="searchContacts()">
                    </div>
                    <h3>Contacts</h3>
                    <ul id="contacts-list">
                        <?php
                        $roleMapping = [
                            1 => "Etudiant",
                            2 => "Professeur",
                            3 => "Maitre de stage"
                        ];

                        // Récupérer les contacts associés à l'utilisateur connecté
                        $userId = $person->getUserId();
                        $contacts = $database->getGroupContacts($userId);

                        // Sort contacts by role
                        usort($contacts, fn($a, $b) => $a['role'] <=> $b['role']);

                        // Group contacts by role
                        $groupedContacts = [];
                        foreach ($contacts as $contact) {
                            $roleName = $roleMapping[$contact['role']] ?? "Unknown Role";
                            $groupedContacts[$roleName][] = $contact;
                        }

                        // Display contacts grouped by role
                        foreach ($groupedContacts as $roleName => $contactsGroup) {
                            echo "<label><strong>$roleName :</strong></label>";
                            foreach ($contactsGroup as $contact) {
                                echo '<li data-contact-id="' . $contact['id'] . '" onclick="openChat(' . $contact['id'] . ', \'' . htmlspecialchars($contact['prenom'] . ' ' . $contact['nom']) . '\')">';
                                echo htmlspecialchars($contact['prenom'] . ' ' . $contact['nom']);
                                echo '</li>';
                            }
                        }
                        ?>
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
                        <!-- JS messages dynamic -->
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

        <!-- Section Groupes -->
        <div class="Contenu <?php echo $activeSection == '6' ? 'Visible' : ''; ?>" id="content-6">
            <!-- Code pour le widget de création de groupes -->

            <!-- Bouton pour ouvrir la fenêtre modale de création de groupe -->
            <button class="open-create-group-modal">Créer un nouveau groupe</button>

            <!-- Fenêtre modale pour créer un nouveau groupe -->
            <div id="createGroupModal" class="modal">
                <div class="modal-content">
                    <h2>Créer un nouveau groupe</h2>

                    <!-- Formulaire pour la création du groupe -->
                    <form id="createGroupForm" method="POST" action="#">
                        <!-- Sélection de l'étudiant -->
                        <label for="student-select">Étudiant :</label>
                        <select id="student-select" name="student_id" required>
                            <option value="">Sélectionnez un étudiant</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student->getUserId(); ?>"><?php echo htmlspecialchars($student->getPrenom()) . ' ' . htmlspecialchars($student->getNom()); ?></option>
                            <?php endforeach; ?>
                        </select>

                        <!-- Sélection du professeur -->
                        <label for="professor-select">Professeur :</label>
                        <select id="professor-select" name="professor_id" required>
                            <option value="">Sélectionnez un professeur</option>
                            <?php foreach ($professors as $professor): ?>
                                <option value="<?php echo $professor->getUserId(); ?>"><?php echo htmlspecialchars($professor->getPrenom()) . ' ' . htmlspecialchars($professor->getNom()); ?></option>
                            <?php endforeach; ?>
                        </select>

                        <!-- Sélection du maître de stage -->
                        <label for="maitre-select">Maître de stage :</label>
                        <select id="maitre-select" name="maitre_id" required>
                            <option value="">Sélectionnez un maître de stage</option>
                            <?php foreach ($maitres as $maitre): ?>
                                <option value="<?php echo $maitre->getUserId(); ?>"><?php echo htmlspecialchars($maitre->getPrenom()) . ' ' . htmlspecialchars($maitre->getNom()); ?></option>
                            <?php endforeach; ?>
                        </select>

                        <!-- Bouton pour soumettre le formulaire et créer le groupe -->
                        <button type="submit" class="submit-group-button">Créer le groupe</button>
                    </form>

                    <!-- Zone pour afficher le message de résultat -->
                    <div id="resultMessage"></div>
                    <!-- Bouton pour fermer la fenêtre modale -->
                    <span class="close-modal">&times;</span>
                </div>
            </div>
        </div>
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
<script src="../View/Principal/GroupCreation.js"></script>
</body>
</html>

<?php
// Récupère les profs
$professors = $database -> getProfessor();

// Récupère les profs
$tutors = $database -> getTutor();
?>
<div id="popup-box" class="popup">
    <div class="content">
        <h1>Changer les groupes</h1>
        <br>
        <div class="lists">
            <select name="professeurResp">
                <option value="">Aucun</option>
                <?php foreach ($professors as $professor): ?>
                    <option><?php echo htmlspecialchars($professor->getPrenom()) . ' ' . htmlspecialchars($professor->getNom()); ?></option>
                <?php endforeach; ?>
            </select>
            <select name="Tuteur">
                <option value="">Aucun</option>
                <?php foreach ($tutors as $tutor): ?>
                    <option><?php echo htmlspecialchars($tutor->getPrenom()) . ' ' . htmlspecialchars($tutor->getNom()); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <br>
        <a href="#" class="popupvalide"><button>Valider</button></a>
        <a href="#" class="cross">&times;</a>
    </div>
</div>