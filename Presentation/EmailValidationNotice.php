<?php
// Démarre une session pour accéder aux données de session utilisateur
session_start();

// Inclusion de la classe Database (si nécessaire plus tard dans le script)
require_once "../Model/Database.php";

// Définit le fuseau horaire pour Paris
date_default_timezone_set('Europe/Paris');

// Récupère le nom d'utilisateur à partir de la session, si disponible, sinon 'Guest'
$userName = $_SESSION['user_name'] ?? 'Guest';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Métadonnées du document -->
    <meta charset="UTF-8"> <!-- Définit l'encodage des caractères -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Permet de rendre la page responsive -->

    <!-- Titre de la page -->
    <title>Validation de l'Email</title>

    <!-- Inclusion de la police Roboto via Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">

    <!-- Lien vers la feuille de style pour cette page -->
    <link rel="stylesheet" href="../View/Verification/VerificationStyles.css">
</head>
<body>
<div class="wrapper">
    <!-- En-tête de la page avec barre de navigation -->
    <header class="navbar">
        <div class="navbar-left">
            <!-- Logo de l'application -->
            <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
            <span class="app-name">Le Petit Stage</span> <!-- Nom de l'application -->
        </div>
        <div class="navbar-right">
            <!-- Affichage du nom d'utilisateur sécurisé avec htmlspecialchars -->
            <p><?php echo htmlspecialchars($userName); ?></p>
        </div>
    </header>

    <!-- Contenu principal de la page -->
    <div class="container">
        <h2>Bienvenue sur Le Petit Stage!</h2> <!-- Message de bienvenue -->
        <p>Pour finaliser votre inscription, veuillez valider votre adresse email. Un code de vérification a été envoyé à votre adresse email.</p>
        <p>Si vous n'avez pas reçu d'email, cliquez sur le bouton ci-dessous pour le renvoyer.</p>
        <!-- Lien vers la page de vérification de l'email -->
        <a href="VerifyEmail.php" class="btn">Valider mon adresse email</a>
    </div>
</div>

<!-- Pied de page de l'application -->
<footer class="PiedDePage">
    <!-- Logo d'un partenaire ou institution -->
    <img src="../Resources/Logo_UPHF.png" alt="Logo UPHF" width="10%">
    <!-- Liens supplémentaires -->
    <a href="Redirection.php">Informations</a>
    <a href="Redirection.php">À propos</a>
</footer>
</body>
</html>
