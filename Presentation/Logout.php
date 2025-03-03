<?php
// manage logout
session_start(); // Démarre ou reprend la session

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

// Efface toutes les données de session
$_SESSION = array();

// Détruit le cookie de session si l'option "session.use_cookies" est activée
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    // Crée un cookie avec une date d'expiration passée pour supprimer le cookie
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Détruit complètement la session côté serveur
session_destroy();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"> <!-- Définit l'encodage des caractères en UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déconnexion</title> <!-- Titre de la page affiché dans l'onglet du navigateur -->
    <link rel="stylesheet" href="/View/Logout/Logout.css"> <!-- Feuille de style spécifique pour cette page -->
    <link rel="stylesheet" href="/View/Principal/Principal.css">
    <link rel="stylesheet" href="/View/css/Footer.css"> <!-- Feuille de style du pied de page -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/> <!-- Animate.css pour les animations -->
</head>
<body>
<div class="wrapper">
<!-- Barre de navigation avec logo et nom de l'application -->
<nav class="navbar">
    <div class="navbar-left">
        <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/> <!-- Logo de l'application -->
        <span class="app-name"><?= $translations['titre_appli'] ?></span> <!-- Nom de l'application -->
    </div>
</nav>

<!-- Section contenant le message de déconnexion -->
<article class="logout-container animate__animated animate__fadeIn">
    <h1><?= $translations['deco_effec'] ?></h1> <!-- Titre principal -->
    <h3><?= $translations['deco_success'] ?></h3> <!-- Message de confirmation de déconnexion -->

    <!-- Lien vers la page d'accueil avec un bouton stylisé -->
    <p class="text-center">
        <a href="../index.php" class="home-button"><?= $translations['return_home'] ?></a>
    </p>

    <!-- Information pour fermer la page pour des raisons de sécurité -->
    <p class="security-note">
        <?= $translations['phrase_logout'] ?>
    </p>
</article>
</div>
<!-- Pied de page avec logo et liens -->
<footer>
    <?php include dirname(__FILE__) . '/../View/Footer.php'; ?>
</footer>
</body>
</html>