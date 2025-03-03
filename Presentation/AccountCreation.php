<?php
// Gère la création d'un compte

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

// LANGAGE NOAH

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
<html>
<head>
    <!-- Lien vers la feuille de style par défaut -->
    <link rel="stylesheet" href="../View/AccountCreation/AccountCreation.css">
    <script src="../View/Home/Lobby.js" defer></script>
    <title>Création du compte</title>
</head>
<style>
    #password-strength {
        width: 100%;
        height: 8px;
        background-color: #ddd;
        margin-top: 5px;
        border-radius: 4px;
        overflow: hidden;
    }

    #password-strength div {
        height: 100%;
        width: 0%;
        transition: width 0.3s ease-in-out;
    }

    .weak { background-color: red; }
    .medium { background-color: orange; }
    .strong { background-color: green; }

    #password-message {
        font-size: 14px;
        margin-top: 5px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const phoneInput = document.getElementById('phone');
        const submitButton = document.getElementById('submit-button');
        const strengthBar = document.getElementById('password-strength');
        const strengthIndicator = document.createElement('div');
        strengthBar.appendChild(strengthIndicator);
        const passwordMessage = document.createElement('p');
        passwordMessage.id = 'password-message';
        strengthBar.after(passwordMessage);
        const phoneError = document.getElementById('phone-error');

        function checkPasswordStrength() {
            const password = passwordInput.value;
            let strength = 0;
            let criteria = [];
            if (password.length >= 8) { strength++; criteria.push('Longueur ≥ 8 caractères'); }
            if (/[A-Z]/.test(password)) { strength++; criteria.push('Une majuscule'); }
            if (/[a-z]/.test(password)) { strength++; criteria.push('Une minuscule'); }
            if (/[0-9]/.test(password)) { strength++; criteria.push('Un chiffre'); }
            if (/[\W_]/.test(password)) { strength++; criteria.push('Un caractère spécial'); }

            let strengthText = '';
            let strengthClass = '';
            let strengthPercentage = "0%";

            switch (strength) {
                case 1:
                case 2:
                    strengthText = "Mot de passe faible ❌";
                    strengthClass = "weak";
                    strengthPercentage = "20%";
                    break;
                case 3:
                    strengthText = "Mot de passe moyen ⚠️";
                    strengthClass = "medium";
                    strengthPercentage = "50%";
                    break;
                case 4:
                    strengthText = "Bon mot de passe ✅";
                    strengthClass = "strong";
                    strengthPercentage = "80%";
                    break;
                case 5:
                    strengthText = "Mot de passe sécurisé 🔒";
                    strengthClass = "strong";
                    strengthPercentage = "100%";
                    break;
                default:
                    strengthText = "";
            }

            strengthIndicator.className = strengthClass;
            strengthIndicator.style.width = strengthPercentage;
            passwordMessage.innerHTML = `${strengthText} <br> Critères remplis : ${criteria.join(', ')}`;

            validateForm();
        }

        function validatePhoneNumber() {
            const phone = phoneInput.value.trim();
            const phoneRegex = /^[0-9]{10,15}$/; // Accepte entre 10 et 15 chiffres
            if (!phoneRegex.test(phone)) {
                phoneError.textContent = "Numéro de téléphone invalide. Il doit contenir entre 10 et 15 chiffres.";
                return false;
            } else {
                phoneError.textContent = "";
                return true;
            }
        }

        function validateForm() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const isPhoneValid = validatePhoneNumber();
            const isPasswordStrong = strengthIndicator.style.width === "100%";
            const isPasswordMatching = password === confirmPassword;

            if (isPhoneValid && isPasswordStrong && isPasswordMatching) {
                submitButton.disabled = false;
            } else {
                submitButton.disabled = true;
            }
        }

        function handleSubmit(event) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const isPhoneValid = validatePhoneNumber();
            const isPasswordStrong = strengthIndicator.style.width === "100%";
            const isPasswordMatching = password === confirmPassword;

            if (!isPhoneValid) {
                alert("Le numéro de téléphone est invalide. Il doit contenir entre 10 et 15 chiffres.");
                event.preventDefault();
                return;
            }

            if (!isPasswordStrong) {
                alert("Votre mot de passe n'est pas assez sécurisé. Veuillez suivre les recommandations.");
                event.preventDefault();
                return;
            }

            if (!isPasswordMatching) {
                alert("Les mots de passe ne correspondent pas !");
                event.preventDefault();
                return;
            }
        }

        // Vérifier en temps réel
        passwordInput.addEventListener('input', checkPasswordStrength);
        confirmPasswordInput.addEventListener('input', checkPasswordStrength);
        phoneInput.addEventListener('input', function() {
            validatePhoneNumber();
            validateForm();
        });

        // Bloquer l'envoi du formulaire si les conditions ne sont pas remplies
        document.querySelector("form").addEventListener("submit", handleSubmit);
    });
</script>




<body>
<header class="navbar">
    <div class="navbar-left">
        <!-- Logo de l'application -->
        <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
        <span class="app-name"><?= $translations['titre_appli'] ?></span> <!-- Nom de l'application -->
    </div>

    <div class="navbar-right">
        <!-- Language Switch -->
        <?php
        include '../Model/LanguageSelection.php';
        ?>

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
    <h1><?= $translations['create_account'] ?></h1>

    <!-- Formulaire pour la création de compte -->
    <form action="" method="post">
        <p>
            <!-- Options pour le type de compte à créer -->
            <input type="radio" name="choice" value="student" id="student" required />
            <label for="student"><?= $translations['etu'] ?><span class="required">*</span></label>

            <input type="radio" name="choice" value="tutorprofessor" id="tutorprofessor" required />
            <label for="tutorprofessor"><?= $translations['prof_refe'] ?><span class="required">*</span></label>

            <input type="radio" name="choice" value="tutorcompany" id="tutorcompany" required />

            <label for="tutorcompany"><?= $translations['prof_pro'] ?><span class="required">*</span></label>

            <input type="radio" name="choice" value="secretariat" id="secretariat" required />
            <label for="secretariat">Secrétariat <span class="required">*</span></label>

        </p>

        <!-- Champ pour la fonction professionnelle/universitaire -->
        <p id="activity-field">
            <label for="function"><?= $translations['acti'] ?><span class="required">*</span></label>
            <!-- Champ de saisie pour Tuteur professionnel -->
            <input name="function" id="function-input" type="text" style="display: none;" />
            <!-- Liste déroulante pour Étudiant -->
            <select name="function_student" id="function-student" style="display: none;">
                <option value=""><?= $translations['selection_formation'] ?></option>
                <option value="Informatique"><?= $translations['info'] ?></option>
                <option value="Mesures Physiques"><?= $translations['mesure_physique'] ?></option>
                <!-- Ajoutez d'autres options si nécessaire -->
            </select>
            <!-- Liste déroulante pour Professeur référant -->
            <select name="function_professor" id="function-professor" style="display: none;">
                <option value=""><?= $translations['selection_spe'] ?></option>
                <option value="Programmation Web"><?= $translations['prog_web'] ?></option>
                <option value="Programmation Java"><?= $translations['prog_java'] ?></option>
                <option value="Programmation Python"><?= $translations['prog_python'] ?></option>
                <option value="Professeur d'Anglais"><?= $translations['prof_anglais'] ?></option>
                <option value="SQL"><?= $translations['sql'] ?></option>
                <option value="Mathématiques"><?= $translations['math'] ?></option>
                <!-- Ajoutez d'autres options si nécessaire -->
            </select>
        </p>

        <!-- Champ pour l'adresse e-mail -->
        <p>
            <label for="email"><?= $translations['email'] ?><span class="required">*</span></label>
            <input name="email" id="email" type="email" required/>
        </p>

        <!-- Champ pour le nom de famille -->
        <p>
            <label for="name"><?= $translations['nom_register'] ?><span class="required">*</span></label>
            <input name="name" id="name" type="text" required/>
        </p>

        <!-- Champ pour le prénom -->
        <p>
            <label for="firstname"><?= $translations['prenom_register'] ?><span class="required">*</span></label>
            <input name="firstname" id="firstname" type="text" required/>
        </p>

        <!-- Champ pour le numéro de téléphone -->
        <p>
            <label for="phone"><?= $translations['telephone_register'] ?><span class="required">*</span></label>
            <input name="phone" id="phone" type="text" required/>
        <div id="phone-error" style="color: red; font-size: 14px; margin-top: 5px;"></div>
        </p>


        <!-- Champ pour le mot de passe -->
        <ul>
            <li id="length" > Minimum 8 caractères</li>
            <li id="uppercase" > Une majuscule</li>
            <li id="lowercase" class="invalid"> Une minuscule</li>
            <li id="number" > Un chiffre</li>
            <li id="special" > Un caractère spécial (@$!%*?&)</li>
        </ul>
        <p>
            <label for="password"><?= $translations['mdp_register'] ?><span class="required">*</span></label>
            <input name="password" id="password" type="password" required/>
        </p>

        <!-- Champ pour confirmer le mot de passe -->
        <p>
            <label for="confirm_password"><?= $translations['confirmed_mdp_register'] ?><span class="required">*</span></label>
            <input name="confirm_password" id="confirm_password" type="password" required/>
        </p>
        <div id="password-strength"></div>


        <!-- Bouton de validation -->
        <button type="submit" id="submit-button" disabled><?= $translations['validate'] ?></button>



        <a href="../index.php"><?= $translations['annuler'] ?></a>
    </form>
</div>

<!-- Pied de page avec logo et liens vers des pages d'informations -->
<?php include '../View/Footer.php'; ?>

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
                    activityLabel.innerHTML = '<?= $translations['formation_register'] ?><span class="required">*</span> :';
                } else if (this.value === 'tutorprofessor') {
                    functionProfessor.style.display = 'block';
                    functionProfessor.required = true;
                    activityLabel.innerHTML = '<?= $translations['specialite_register'] ?><span class="required">*</span> :';
                } else {
                    functionInput.style.display = 'block';
                    functionInput.required = false; // Champ non obligatoire
                    activityLabel.innerHTML = '<?= $translations['acti'] ?>';
                    if (this.value === 'tutorcompany') {
                        functionInput.placeholder = '<?= $translations['write_name_enterprise'] ?>';
                    }
                }
            });
        });
    });
</script>
</body>

</html>


