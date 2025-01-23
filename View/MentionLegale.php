<?php
// Affichage des mentions legales
// Démarre une nouvelle session ou reprend une session existante
session_start();
// Inclusion des fichiers nécessaires pour la base de données et les objets Person
require "../Model/Database.php";
require "../Model/Person.php";
// Création d'une nouvelle instance de la classe Database
$database = (Database::getInstance());
// Initialisation du nom d'utilisateur par défaut
$userName = "Guest";
// Vérifie si l'utilisateur est connecté et récupère ses données
if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    // Vérifie si l'objet déserialisé est une instance de la classe Person
    if ($person instanceof Person) {
        // Sécurise et affiche le prénom et le nom de la personne connectée
        $userName = htmlspecialchars($person->getPrenom()) . ' ' . htmlspecialchars($person->getNom());
    }
} else {
    // Si aucune session d'utilisateur n'est trouvée, redirige vers la page de déconnexion
    header("Location: Logout.php");
    exit();
}
// Récupère le rôle de l'utilisateur et l'ID du destinataire des messages
$userRole = $person->getRole();

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
    echo "<p class='nom'>Nom des étudiants :</p>";
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
    echo"<li>Massy Thibaut</li>";
    echo"<li>Warnier Lorenzo</li>";
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

// Détermine la page d'accueil en fonction du rôle de l'utilisateur
$homePage = '';
if ($userRole == 1) {
    $homePage = '../Presentation/Student.php';
} elseif ($userRole == 2) {
    $homePage = '../Presentation/Professor.php';
} elseif ($userRole == 3) {
    $homePage = '../Presentation/MaitreStage.php';
} elseif ($userRole == 4 or $userRole == 5) {
    $homePage = '../Presentation/Secretariat.php';
}

?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Le Petit Stage - Modifier une offre</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="/View/css/MentionLegal.css">
        <script src="https://kit.fontawesome.com/166cd842ba.js" crossorigin="anonymous"></script>
    </head>
    <?php include('Header.php'); ?>
    <?php
    echo "<body>";
    echo "<section class='mentionsLegales'>";
    afficherMentionLegale();
    echo "</section>";
    ?>
    <a class="home-button" href='<?php echo $homePage; ?>'>
        Retour à la page d'accueil
    </a>
    <?php
    echo "</body>";
    echo "<footer>";
    include_once "Footer.php";
    echo "</footer>";
?>
</html>