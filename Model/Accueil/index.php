<?php


class Database
{
    private $pdo;

    public function __construct($host, $dbname, $username, $password)
    {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
        try {
            $this->pdo = new PDO($dsn, $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    public function authenticateUser($username, $password)
    {
        try {
            // Correct field names according to your schema
            $stmt = $this->pdo->prepare("
                SELECT p.passwordsae_hash 
                FROM a_usersae u 
                JOIN a_passwordsae p ON u.user_id = p.user_id 
                WHERE u.login = :login
            ");
            $stmt->execute([':login' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verifying the password
            if ($user && password_verify($password, $user['passwordsae_hash'])) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de l'authentification : " . $e->getMessage());
        }
    }
}

session_start();

// Initialize the Database connection
$db = new Database("141.94.245.139", "s3081_BDD_Barkhane", "u3081_erRWAWL7zt", "ODyKebC@rSeyavay2Olz4!K!");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Authenticate user
    if ($db->authenticateUser($username, $password)) {
        echo "Connexion réussie !";
        $_SESSION['user'] = $username;
        header('Location: /SAE-BUT2-1.1/Model/Principal/Principal.php');
        exit;
    } else {
        echo "Échec de la connexion. Vérifiez vos identifiants.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Petit Stage</title>
    <link rel="stylesheet" href="lobby.css">
    <link rel="stylesheet" href="login.css">
    <script src="accueil.js" defer></script>
</head>
<body>
<!-- Navbar -->
<nav class="navbar">
    <div class="navbar-left">
        <img src="/Model/Accueil/LPS1.0.png" alt="Logo" class="logo"/>
        <span class="app-name">Le Petit Stage</span>
    </div>
    <div class="navbar-right">
        <!-- Language Switch -->
        <label class="switch">
            <input type="checkbox" id="language-switch" onchange="toggleLanguage()">
            <span class="slider round">
                <span class="switch-sticker">🇫🇷</span> <!-- Sticker Français -->
                <span class="switch-sticker switch-sticker-right">🇬🇧</span> <!-- Sticker English -->
            </span>
        </label>
        <!-- Theme Switch -->
        <label class="switch">
            <input type="checkbox" id="theme-switch" onchange="toggleTheme()">
            <span class="slider round">
                <span class="switch-sticker">🌙</span> <!-- Sticker Dark Mode -->
                <span class="switch-sticker switch-sticker-right">☀️</span> <!-- Sticker Light Mode -->
            </span>
        </label>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content">
    <h1 class="main-heading">Vous êtes un étudiant en stage à UPHF?<br> Nous avons la solution!</h1>
    <p class="sub-text">
        Une application innovante pour les étudiants, enseignants et personnel de l'UPHF. Gérez vos stages et restez connectés avec toutes les parties prenantes facilement et efficacement.
    </p>

    <div class="login-container">
        <h2>Connexion</h2>
        <form action="" method="POST">
            <div class="form-group">
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button class="primary-button" ><a>Se connecter</a></button>
        </form>
    </div>

    <div class="button-group">
        <p style="font-size: large"><b>ou</b></p>
        <button class="secondary-button"><a href="/Model/AccountCreation/AccountCreation.php">S’enregistrer</a></button>
    </div>
</div>
</body>
</html>