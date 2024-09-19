<?php
session_start();

$_SESSION = array();


if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title> Déconnexion </title>
    <link rel="stylesheet" href="Deconnexion.css">
</head>
<body>
<nav class="navbar">
<div class="navbar-left">
    <img src="/Model/Accueil/LPS1.0.png" alt="Logo" class="logo"/>
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
    <a href="../Accueil/index.php">Retour à l'accueil</a> <br>
</p>
<p>
    Si vous ne souhaitez pas revenir à l'accueil, pour des raisons de sécurité, veuillez fermer cette page.
</p>
</article>

</body>
</html>
