<?php
// manage mail validation
session_start();

// Removing session variables related to verification
unset($_SESSION['email_verification']);
unset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Activation réussie</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../View/Verification/VerificationStyles.css">
</head>
<body>
<div class="wrapper">
    <div class="container">
        <h2 style="color: #0fcd5e"><b>Activation réussie !</b></h2>
        <p>Votre adresse email a été vérifiée avec succès.</p>
        <p>Votre compte est maintenant en attente de validation par le secrétariat.</p>
        <p>Vous recevrez un email une fois que votre compte sera approuvé.</p>
        <div class="link">
            <a href="../index.php">Retour à la connexion</a>
        </div>
    </div>
</div>
</body>
</html>