<?php
session_start(); // Démarre ou reprend la session

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
    <title>Déconnexion</title> <!-- Titre de la page affiché dans l'onglet du navigateur -->
    <link rel="stylesheet" href="../View/Logout/Logout.css"> <!-- Lien vers la feuille de style CSS spécifique pour cette page -->
    <link rel="stylesheet" href="../rebase/Modely/DefaultStyles/styles.css"> <!-- Lien vers la feuille de style par défaut -->
</head>
<body>
<!-- Barre de navigation avec logo et nom de l'application -->
<nav class="navbar">
    <div class="navbar-left">
        <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/> <!-- Logo de l'application -->
        <span class="app-name">Le Petit Stage</span> <!-- Nom de l'application -->
    </div>
</nav>

<!-- Section contenant le message de déconnexion -->
<article class="carré">
    <h1>Déconnexion effectuée</h1> <!-- Titre principal -->
    <h3>Vous avez été déconnecté avec succès.</h3> <!-- Message de confirmation de déconnexion -->

    <!-- Lien vers la page d'accueil -->
    <p class="textCenter">
        <a href="../Index.php">Retour à l'accueil</a> <br>
    </p>

    <!-- Information pour fermer la page pour des raisons de sécurité -->
    <p>
        Si vous ne souhaitez pas revenir à l'accueil, pour des raisons de sécurité, veuillez fermer cette page.
    </p>
</article>

<!-- Pied de page avec logo et liens -->
<footer>
    <img src="../Resources/Logo_UPHF.png" alt="Logo UPHF" width="10%"> <!-- Logo UPHF -->
    <a href="Redirection.php">Informations</a> <!-- Lien vers les informations -->
    <a href="Redirection.php">A propos</a> <!-- Lien vers la page "À propos" -->
</footer>

</body>
</html>
