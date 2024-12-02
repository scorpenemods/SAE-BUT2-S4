<?php
session_start();


include_once'Header.php';
function afficherMentionLegale() {
    // Récupérer la valeur de la mention depuis la session ou GET
    if (isset($_GET['mention'])) {
        $_SESSION['mention'] = $_GET['mention']; // Met à jour la session avec la valeur GET
    }

    if (!isset($_SESSION['mention'])) {
        echo "<p>Aucune mention sélectionnée. Veuillez spécifier une catégorie valide.</p>";
        return;
    }

    // Récupération de la mention
    $mention = $_SESSION['mention'];

    switch ($mention) {
        case 'informations':
            afficherInformations();
            break;
        case 'a-propos':
            afficherAPropos();
            break;
        case 'confidentialite':
            afficherConfidentialite();
            break;
        case 'conditions-utilisation':
            afficherConditionsUtilisation();
            break;
        default:
            echo "<p>Section non reconnue. Veuillez vérifier votre sélection.</p>";
    }
}
function afficherInformations() {
    echo "<div class ='mention'><h1>Informations</h1>";
    echo "<p>Nom des étudiants :</p>";
    echo "<ul>";
    echo"<li>Boulet Rémy</li>";
    echo"<li>Héthuin Marion</li>";
    echo"<li>Lemaire Noa</li>";
    echo"<li>Bourgeois Julien</li>";
    echo"<li>Newerkowitsch Lucien</li>";
    echo"<li>Lovinhov Valerii </li>";
    echo"</ul>";
    echo "<p>Nom des alternants(sous-traitant) :</p>";
    echo "<ul>";
    echo"<li>Terrier Margot</li>";
    echo"<li>Massy Thibault</li>";
    echo"<li>Wargnier Lorenzo</li>";
    echo"</ul>";
    echo "<p>Adresse : IUT Maubeuge, Bd Charles de Gaulle, 59600 Maubeuge</p>";
    echo "<p>Email : remy.boulet@iut.fr</p>";
    echo "<p>Hébergeur : Wargnier Lorenzo </p></div>";
}

function afficherAPropos() {
    echo "<div class ='mention'><h1>À propos</h1>";
    echo "<p>Ce site a été conçu dans le cadre d’un projet étudiant.</p>";
    echo "<p>Il est destiné à des fins pédagogiques et n’a pas d’objectif commercial.</p></div>";
}

function afficherConfidentialite() {
    echo "<div class ='mention'><h1>Confidentialité</h1>";
    echo "<p>Ce site respecte le RGPD et ne collecte pas de données personnelles sans consentement explicite.</p>";
    echo "<p>Vous pouvez gérer vos préférences en matière de cookies dans votre navigateur.</p></div>";
}

function afficherConditionsUtilisation() {
    echo "<div class ='mention'><h1>Conditions d'utilisation</h1>";
    echo "<p>Les contenus de ce site sont protégés par le droit d’auteur.</p>";
    echo "<p>Tout usage non autorisé est interdit. Ce site est un projet étudiant.</p></div>";
}

?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Le Petit Stage - Modifier une offre</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="/View/css/Create.css">
        <link rel="stylesheet" href="/View/css/Header.css">
        <link rel="stylesheet" href="/View/css/Footer.css">
        <script src="https://kit.fontawesome.com/166cd842ba.js" crossorigin="anonymous"></script>
    </head>

    <?php
    echo "<body>";
    afficherMentionLegale();
    echo "</body>";
    echo "<footer>";
    include_once "Footer.php";
    echo "</footer>";
?>
