<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirection</title>
    <link rel="stylesheet" href="../View/Redirection/Redirection.css"> <!-- Lien vers le fichier CSS -->
</head>
<body>
<div class="container">
    <h1>Redirection...</h1>
    <p>Retour à la page d'accueil dans 3 secondes...</p>
</div>
<?php
session_start();

if($_SESSION["user_role"] == 4 || $_SESSION["user_role"] == 5){
    header('Location: Secretariat.php?section=0');
    die();
}
if($_SESSION["user_role"] == 3 ){
    header('Location: MaitreStage.php?section=1');

}
if($_SESSION["user_role"] == 2 ){
    header('Location: Professor.php?section=1');

}
if($_SESSION["user_role"] == 1){
    header('Location: Student.php?section=1');

}



?>
</body>
</html>
