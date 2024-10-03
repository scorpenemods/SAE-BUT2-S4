<?php
session_start();

// Clear all session data
$_SESSION = array();

// Destroy the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Completely destroy the session
session_destroy();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title> Déconnexion </title>
    <link rel="stylesheet" href="../View/Logout/Logout.css">
    <link rel="stylesheet" href="../rebase/Modely/DefaultStyles/styles.css">
</head>
<body>
<nav class="navbar">
<div class="navbar-left">
    <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
    <span class="app-name">Le Petit Stage</span>
</div>
</nav>
<!-- Carré d'information sur la déconnexion -->
<article class="carré">

    <h1>
        Déconnexion effectuée
        <br>
    </h1>
    <h3>Vous avez été déconnecté avec succès.</h3>

<p class="textCenter">
    <a href="../Index.php">Retour à l'accueil</a> <br>
</p>
<p>
    Si vous ne souhaitez pas revenir à l'accueil, pour des raisons de sécurité, veuillez fermer cette page.
</p>
</article>

<footer>
    <img src="../Resources/Logo_UPHF.png" alt="Logo uphf" width="10%">
    <a href="Redirection.php">Informations</a>
    <a href="Redirection.php">A propos</a>
</footer>

</body>
</html>
