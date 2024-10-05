<?php
// Démarrage d'une nouvelle session ou reprise d'une session existante
session_start();

// Inclusion des fichiers nécessaires pour accéder à la base de données et à la définition de la classe Person
require_once 'Model/Database.php';
require_once 'Model/Person.php';

// Création d'une nouvelle instance de la classe Database pour interagir avec la base de données
$database = new Database();
$errorMessage = '';

// Vérification si la méthode de la requête HTTP est POST, ce qui indique que le formulaire de connexion a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Appel de la méthode verifyLogin pour vérifier les identifiants de l'utilisateur
    $user = $database->verifyLogin($username, $password);

    // Si la vérification est réussie et que $user est un tableau (signifiant un utilisateur valide), exécute le bloc suivant
    if ($user && is_array($user)) {
        // Création d'un nouvel objet Person avec les données de l'utilisateur
        $person = new Person(
            $user['nom'],
            $user['prenom'],
            $user['telephone'],
            $user['login'],
            $user['role'],
            $user['activite'],
            $user['email'],
            $user['id'] // Utilisation de l'ID de l'utilisateur récupéré de la base de données
        );

        // Stockage de l'ID de l'utilisateur, de l'objet Person serialisé, du rôle et du nom complet dans la session
        $_SESSION['user_id'] = $person->getUserId();
        $_SESSION['user'] = serialize($person);
        $_SESSION['user_role'] = $person->getRole();
        $_SESSION['user_name'] = $person->getPrenom() . ' ' . $person->getNom();

        // Redirection de l'utilisateur vers une page spécifique selon son rôle
        switch ($_SESSION['user_role']) {
            case 1: // Étudiant
                header("Location: Presentation/Student.php");
                break;
            case 2: // Professeur
                header("Location: Presentation/Professor.php");
                break;
            case 3: // Mentor professionnel
                header("Location: Presentation/maitreStage.php");
                break;
            case 4: // Secrétariat
                header("Location: Presentation/secritariat.php");
                break;
            default: // Redirection par défaut si le rôle n'est pas géré
                header("Location: Presentation/Redirection.php");
                break;
        }
    } elseif ($user === 'pending') {
        // Si l'utilisateur existe mais son compte n'est pas encore activé
        $errorMessage = "Votre compte n'est pas encore activé.";
    } else {
        // Si les identifiants sont incorrects
        $errorMessage = 'Identifiants incorrects. Veuillez réessayer.';
    }

    // Fermeture de la connexion à la base de données
    $database->closeConnection();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Petit Stage</title>
    <!-- Liens vers les feuilles de style CSS et les icônes FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./View/Home/Lobby.css">
    <link rel="stylesheet" href="./View/Home/Login.css">
    <!-- Script JavaScript différé pour des interactions dynamiques -->
    <script src="./View/Home/Lobby.js" defer></script>
</head>
<body>
<!-- Navigation principale avec logo et interrupteurs pour les paramètres de langue et de thème -->
<nav class="navbar">
    <!-- Partie gauche avec logo et nom de l'application -->
    <div class="navbar-left">
        <img src="Resources/LPS 1.0.png" alt="Logo" class="logo"/>
        <span class="app-name">Le Petit Stage</span>
    </div>
    <!-- Partie droite avec contrôles pour les préférences de l'utilisateur -->
    <div class="navbar-right">
        <label class="switch">
            <input type="checkbox" id="language-switch" onchange="toggleLanguage()">
            <span class="slider round">
                <span class="switch-sticker">🇫🇷</span>
                <span class="switch-sticker switch-sticker-right">🇬🇧</span>
            </span>
        </label>
        <label class="switch">
            <input type="checkbox" id="theme-switch" onchange="toggleTheme()">
            <span class="slider round">
                <span class="switch-sticker switch-sticker-right">🌙</span> <!-- Sticker Dark Mode -->
                <span class="switch-sticker">☀️</span>
            </span>
        </label>
    </div>
</nav>

<article>
    <!-- Contenu principal avec une introduction et formulaire de connexion -->
    <div class="main-content">
        <h1 class="main-heading">Vous êtes un étudiant en stage à UPHF?<br> Nous avons la solution!</h1>
        <p class="sub-text">
            Une application innovante pour les étudiants, enseignants et personnel de l'UPHF. Gérez vos stages et restez connectés avec toutes les parties prenantes facilement et efficacement.
        </p>
        <!-- Formulaire de connexion -->
        <div class="login-container">
            <h2>Connexion</h2>
            <?php if (!empty($errorMessage)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur :</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe :</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button class="primary-button" type="submit">Se connecter</button>
                <p>Un problème pour se connecter ?</p>
                <a href="rebase/Modely/ForgotPassword/ForgotPasswordMail.php">Changer le mot de passe</a>
            </form>
        </div>
        <!-- Liens pour les utilisateurs non connectés -->
        <div class="button-group">
            <p style="font-size: large"><b>ou</b></p>
            <button class="secondary-button"><a class="login-link" href="Presentation/AccountCreation.php">S’enregistrer</a></button>
        </div>
    </div>
</article>

<footer class="PiedDePage">
    <!-- Pied de page avec logo additionnel et liens -->
    <img src="Resources/Logo_UPHF.png" alt="Logo uphf" width="10%">
    <a href="Presentation/Redirection.php">Informations</a>
    <a href="Presentation/Redirection.php">A propos</a>
</footer>


</body>
</html>