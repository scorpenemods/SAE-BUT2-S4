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
    <h1>Création du compte</h1> <!-- Titre de la page de création de compte -->

    <!-- Formulaire pour la création de compte -->
    <form action="" method="post">
        <p>
            <!-- Options pour le type de compte à créer -->
            <input type="radio" name="choice" value="student" id="student" required />
            <label for="student">Étudiant</label>

            <input type="radio" name="choice" value="tutorprofessor" id="tutorprofessor" required />
            <label for="tutorprofessor">Professeur référant</label>

            <input type="radio" name="choice" value="tutorcompany" id="tutorcompany" required />
            <label for="tutorcompany">Tuteur professionnel</label>

            <input type="radio" name="choice" value="secretariat" id="secretariat" required />
            <label for="secritariat">Secrétariat</label>
        </p>

        <!-- Champ pour la fonction professionnelle/universitaire -->
        <p>
            <label for="function">Activité professionnelle/universitaire :</label>
            <input name="function" id="function" type="text" required/>
        </p>

        <!-- Champ pour l'adresse e-mail -->
        <p>
            <label for="email">E-mail :</label>
            <input name="email" id="email" type="email" required/>
        </p>

        <!-- Champ pour le nom de famille -->
        <p>
            <label for="name">Nom :</label>
            <input name="name" id="name" type="text" required/>
        </p>

        <!-- Champ pour le prénom -->
        <p>
            <label for="firstname">Prénom :</label>
            <input name="firstname" id="firstname" type="text" required/>
        </p>

        <!-- Champ pour le numéro de téléphone -->
        <p>
            <label for="phone">Téléphone :</label>
            <input name="phone" id="phone" type="text" required/>
        </p>

        <!-- Champ pour le mot de passe -->
        <p>
            <label for="password">Mot de passe :</label>
            <input name="password" id="password" type="password" required/>
        </p>

        <!-- Champ pour confirmer le mot de passe -->
        <p>
            <label for="confirm_password">Confirmer le mot de passe :</label>
            <input name="confirm_password" id="confirm_password" type="password" required/>
        </p>

        <!-- Bouton de validation -->
        <button type="submit">Valider</button>
    </form>
</div>

<!-- Pied de page avec logo et liens vers des pages d'informations -->
<footer class="PiedDePage">
    <img src="../Resources/Logo_UPHF.png" alt="Logo UPHF" width="10%"> <!-- Logo UPHF -->
    <a href="Redirection.php">Informations</a> <!-- Lien vers une page d'informations -->
    <a href="Redirection.php">À propos</a> <!-- Lien vers une page "À propos" -->
</footer>

</body>
</html>

<?php
session_start();

require_once "../Model/Database.php"; // db connect

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    echo "<script>console.error('Erreur de connexion à la base de données.');</script>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et sanitization des données du formulaire d'inscription
    $role = $_POST['choice'];
    $function = htmlspecialchars(trim($_POST['function']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $name = htmlspecialchars(trim($_POST['name']));
    $firstname = htmlspecialchars(trim($_POST['firstname']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validation de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Adresse email invalide.');</script>";
        exit();
    }

    // Vérification de la correspondance des mots de passe
    if ($password !== $confirmPassword) {
        echo "<script>alert('Les mots de passe ne correspondent pas!');</script>";
        exit();
    }

    // Vérification si l'email existe déjà dans la base de données
    $queryCheckEmail = "SELECT COUNT(*) FROM User WHERE email = :email";
    $stmtCheck = $conn->prepare($queryCheckEmail);
    $stmtCheck->bindParam(':email', $email);
    $stmtCheck->execute();
    $emailExists = $stmtCheck->fetchColumn();

    if ($emailExists > 0) {
        // Si l'email existe déjà
        echo "<script>alert('Cet email est déjà enregistré. Veuillez utiliser un autre email.');</script>";
        exit();
    }

    // Hachage du mot de passe
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Mapping des rôles
    $roleMapping = [
        'student' => 1,
        'tutorprofessor' => 2,
        'tutorcompany' => 3,
        'secretariat' => 4,
    ];
    $roleID = isset($roleMapping[$role]) ? $roleMapping[$role] : null;

    if (!$roleID) {
        echo "<script>alert('Rôle invalide.');</script>";
        exit();
    }

    // Insertion de l'utilisateur dans la base de données
    $query = "INSERT INTO User (nom, prenom, email, telephone, role, activite, valid_email, status_user, last_connexion, account_creation) 
              VALUES (:nom, :prenom, :email, :telephone, :role, :activite, 0, 0, NOW(), NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':nom', $name);
    $stmt->bindValue(':prenom', $firstname);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':telephone', $phone);
    $stmt->bindValue(':role', $roleID);
    $stmt->bindValue(':activite', $function);

    if ($stmt->execute()) {
        $userID = $conn->lastInsertId();

        // Insertion du mot de passe dans la table Password
        $queryPass = "INSERT INTO Password (user_id, password_hash, actif) VALUES (:user_id, :password_hash, 1)";
        $stmtPass = $conn->prepare($queryPass);
        $stmtPass->bindValue(':user_id', $userID);
        $stmtPass->bindValue(':password_hash', $hashedPassword);

        if ($stmtPass->execute()) {
            // Sauvegarde de l'email dans la session
            $_SESSION['user_email'] = $email;
            $_SESSION['user_id'] = $userID;
            $_SESSION['user_name'] = $name . " " . $firstname;

            // Redirection vers la page de validation de l'email
            header("Location: EmailValidationNotice.php");
            exit();
        } else {
            echo "<script>alert('Erreur lors de l\'insertion du mot de passe.');</script>";
        }
    } else {
        echo "<script>alert('Erreur lors de la création de l\'utilisateur.');</script>";
    }
}
?>

