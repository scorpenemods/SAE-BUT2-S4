<?php
session_start();
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once "../Model/Database.php"; // Include the Database class

$database = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve selected role and initialize function variable
    $role = $_POST['choice'];
    $function = '';

    // Determine function based on role
    if ($role === 'student') {
        if (!empty($_POST['function_student'])) {
            $function = htmlspecialchars(trim($_POST['function_student']));
        } else {
            echo "<script>alert('Veuillez sélectionner votre formation.');</script>";
            exit();
        }
    } elseif ($role === 'tutorprofessor') {
        if (!empty($_POST['function_professor'])) {
            $function = htmlspecialchars(trim($_POST['function_professor']));
        } else {
            echo "<script>alert('Veuillez sélectionner votre spécialité.');</script>";
            exit();
        }
    } else {
        if (isset($_POST['function'])) {
            $function = htmlspecialchars(trim($_POST['function']));
        }
        // Field is not mandatory for 'tutorcompany' and 'Secretariat'
    }

    // Sanitize input data
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $name = htmlspecialchars(trim($_POST['name']));
    $firstname = htmlspecialchars(trim($_POST['firstname']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validate email and password
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Adresse email invalide.');</script>";
        exit();
    }

    if ($password !== $confirmPassword) {
        echo "<script>alert('Les mots de passe ne correspondent pas!');</script>";
        exit();
    }

    // Check if email already exists using Database method
    if ($database->emailExists($email)) {
        echo "<script>alert('Cet email est déjà enregistré. Veuillez utiliser un autre email.');</script>";
        exit();
    }

    // Map role to role ID
    $roleMapping = [
        'student' => 1,
        'tutorprofessor' => 2,
        'tutorcompany' => 3,
        'Secretariat' => 4,
    ];
    $roleID = $roleMapping[$role] ?? null;

    if (!$roleID) {
        echo "<script>alert('Rôle invalide.');</script>";
        exit();
    }

    // Attempt to add user with the Database class's addUser method
    if ($database->addUser($email, $password, $phone, $firstname, $function, $roleID, $name, 0)) {
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $name . " " . $firstname;

        // Get new user's ID
        $user = $database->getUserByEmail($email);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
        } else {
            echo "<script>alert('Erreur lors de la récupération de l\'ID utilisateur.');</script>";
            exit();
        }

        // Redirect to email validation notice
        header("Location: EmailValidationNotice.php");
        exit();
    } else {
        echo "<script>alert('Erreur lors de la création de l\'utilisateur.');</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <!-- Lien vers la feuille de style par défaut -->
    <link rel="stylesheet" href="../View/AccountCreation/AccountCreation.css">
    <script src="../View/Home/Lobby.js" defer></script>
    <title>Création du compte</title>
</head>
<body>
<header class="navbar">
    <div class="navbar-left">
        <!-- Logo de l'application -->
        <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
        <span class="app-name">Le Petit Stage</span> <!-- Nom de l'application -->
    </div>

    <div class="navbar-right">
        <!-- Commutateur pour changer la langue -->
        <label class="switch">
            <input type="checkbox" id="language-switch" onchange="toggleLanguage()">
            <span class="slider round">
                <span class="switch-sticker">🇫🇷</span> <!-- Sticker pour la langue française -->
                <span class="switch-sticker switch-sticker-right">🇬🇧</span> <!-- Sticker pour la langue anglaise -->
            </span>
        </label>

        <!-- Commutateur pour changer le thème (clair/sombre) -->
        <label class="switch">
            <input type="checkbox" id="theme-switch" onchange="toggleTheme()">
            <span class="slider round">
                <span class="switch-sticker switch-sticker-right">🌙</span> <!-- Sticker pour mode sombre -->
                <span class="switch-sticker">☀️</span> <!-- Sticker pour mode clair -->
            </span>
        </label>
    </div>
</header>

<div class="container">
    <h1>Création du compte</h1>

    <!-- Formulaire pour la création de compte -->
    <form action="" method="post">
        <p>
            <!-- Options pour le type de compte à créer -->
            <input type="radio" name="choice" value="student" id="student" required />
            <label for="student">Étudiant <span class="required">*</span></label>

            <input type="radio" name="choice" value="tutorprofessor" id="tutorprofessor" required />
            <label for="tutorprofessor">Professeur référant <span class="required">*</span></label>

            <input type="radio" name="choice" value="tutorcompany" id="tutorcompany" required />
            <label for="tutorcompany">Tuteur professionnel <span class="required">*</span></label>


            <input type="radio" name="choice" value="secretariat" id="secretariat" required />
            <label for="secretariat">Secrétariat <span class="required">*</span></label>
        </p>

        <!-- Champ pour la fonction professionnelle/universitaire -->
        <p id="activity-field">
            <label for="function">Activité professionnelle/universitaire <span class="required">*</span></label>
            <!-- Champ de saisie pour Tuteur professionnel et Secrétariat -->
            <input name="function" id="function-input" type="text" style="display: none;" />
            <!-- Liste déroulante pour Étudiant -->
            <select name="function_student" id="function-student" style="display: none;">
                <option value="">Sélectionnez votre formation</option>
                <option value="Informatique">Informatique</option>
                <option value="Mesures Physiques">Mesures Physiques</option>
                <!-- Ajoutez d'autres options si nécessaire -->
            </select>
            <!-- Liste déroulante pour Professeur référant -->
            <select name="function_professor" id="function-professor" style="display: none;">
                <option value="">Sélectionnez votre spécialité</option>
                <option value="Programmation Web">Programmation Web</option>
                <option value="Programmation Java">Programmation Java</option>
                <option value="Programmation Python">Programmation Python</option>
                <option value="Professeur d'Anglais">Professeur d'Anglais</option>
                <option value="SQL">SQL</option>
                <option value="Mathématiques">Mathématiques</option>
                <!-- Ajoutez d'autres options si nécessaire -->
            </select>
        </p>

        <!-- Champ pour l'adresse e-mail -->
        <p>
            <label for="email">E-mail : <span class="required">*</span></label>
            <input name="email" id="email" type="email" required/>
        </p>

        <!-- Champ pour le nom de famille -->
        <p>
            <label for="name">Nom : <span class="required">*</span></label>
            <input name="name" id="name" type="text" required/>
        </p>

        <!-- Champ pour le prénom -->
        <p>
            <label for="firstname">Prénom : <span class="required">*</span></label>
            <input name="firstname" id="firstname" type="text" required/>
        </p>

        <!-- Champ pour le numéro de téléphone -->
        <p>
            <label for="phone">Téléphone :</label>
            <input name="phone" id="phone" type="text"/>
        </p>

        <!-- Champ pour le mot de passe -->
        <p>
            <label for="password">Mot de passe : <span class="required">*</span></label>
            <input name="password" id="password" type="password" required/>
        </p>

        <!-- Champ pour confirmer le mot de passe -->
        <p>
            <label for="confirm_password">Confirmer le mot de passe : <span class="required">*</span></label>
            <input name="confirm_password" id="confirm_password" type="password" required/>
        </p>

        <!-- Bouton de validation -->
        <button type="submit">Valider</button>

        <a href="../Index.php">Annuler</a>
    </form>
</div>

<!-- Pied de page avec logo et liens vers des pages d'informations -->
<footer class="PiedDePage">
    <img src="../Resources/Logo_UPHF.png" alt="Logo UPHF" width="9%"> <!-- Logo UPHF -->
    <a href="Redirection.php">Informations</a> <!-- Lien vers une page d'informations -->
    <a href="Redirection.php">À propos</a> <!-- Lien vers une page "À propos" -->
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Obtenir les éléments des boutons radio
        const roleRadios = document.querySelectorAll('input[name="choice"]');
        // Obtenir les champs d'activité
        const functionInput = document.getElementById('function-input');
        const functionStudent = document.getElementById('function-student');
        const functionProfessor = document.getElementById('function-professor');
        // Obtenir le label
        const activityLabel = document.querySelector('#activity-field label');

        roleRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                // Réinitialiser l'affichage des champs
                functionInput.style.display = 'none';
                functionStudent.style.display = 'none';
                functionProfessor.style.display = 'none';
                functionInput.required = false;
                functionStudent.required = false;
                functionProfessor.required = false;
                functionInput.placeholder = ''; // Réinitialiser le placeholder

                if (this.value === 'student') {
                    functionStudent.style.display = 'block';
                    functionStudent.required = true;
                    activityLabel.innerHTML = 'Formation <span class="required">*</span> :';
                } else if (this.value === 'tutorprofessor') {
                    functionProfessor.style.display = 'block';
                    functionProfessor.required = true;
                    activityLabel.innerHTML = 'Spécialité <span class="required">*</span> :';
                } else {
                    functionInput.style.display = 'block';
                    functionInput.required = false; // Champ non obligatoire
                    activityLabel.innerHTML = 'Activité professionnelle/universitaire :';
                    if (this.value === 'tutorcompany') {
                        functionInput.placeholder = 'Écrire le nom de votre entreprise';
                    }
                }
            });
        });
    });
</script>
</body>

</html>


