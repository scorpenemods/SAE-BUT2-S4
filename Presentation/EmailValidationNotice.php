<?php
session_start();
require_once "../Model/Database.php";

date_default_timezone_set('Europe/Paris');

$userName = $_SESSION['user_name'] ?? 'Guest';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation de l'Email</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../View/Verification/VerificationStyles.css">
</head>
<body>
<div class="wrapper">
    <header class="navbar">
        <div class="navbar-left">
            <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
            <span class="app-name">Le Petit Stage</span>
        </div>
        <div class="navbar-right">
            <p><?php echo htmlspecialchars($userName); ?></p>
        </div>
    </header>

    <div class="container">
        <h2>Bienvenue sur Le Petit Stage!</h2>
        <p>Pour finaliser votre inscription, veuillez valider votre adresse email. Un code de vérification a été envoyé à votre adresse email.</p>
        <p>Si vous n'avez pas reçu d'email, cliquez sur le bouton ci-dessous pour le renvoyer.</p>
        <a href="VerifyEmail.php" class="btn">Valider mon adresse email</a>
    </div>
</div>

<footer class="PiedDePage">
    <img src="../Resources/Logo_UPHF.png" alt="Logo UPHF" width="10%">
    <a href="Redirection.php">Informations</a>
    <a href="Redirection.php">À propos</a>
</footer>
</body>
</html>