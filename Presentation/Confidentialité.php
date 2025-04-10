<?php
// LANGAGE NOAH
require_once '../Model/Config.php';

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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Lien vers la feuille de style par défaut -->
    <link rel="stylesheet" href="../View/AccountCreation/AccountCreation.css">
    <script src="../View/Home/Lobby.js" defer></script>
    <title>Politique de Confidentialité</title>
    <link rel="stylesheet" href="../View/MentionLegal/confidentialite.css">


</head>
<body>

<header class="navbar">
    <div class="navbar-left">
        <!-- Logo de l'application -->
        <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
        <span class="app-name"><?= $translations['titre_appli'] ?></span> <!-- Nom de l'application -->
    </div>

    <div class="navbar-right">
        <!-- Language Switch -->
        <?php
        include '../Model/LanguageSelection.php';
        ?>

        <!-- Commutateur pour changer le thème (clair/sombre) -->
        <label class="switch">
            <input type="checkbox" id="theme-switch" onchange="toggleTheme()">
            <span class="slider round">
                <span class="switch-sticker switch-sticker-right">🌙</span> <!-- Sticker pour mode sombre -->
                <span class="switch-sticker">☀️</span> <!-- Sticker pour mode clair -->
            </span>
        </label>
    </div>
</header>

<main class="privacy-policy-container">
    <h1>Politique de Confidentialité</h1>

    <p>
        Cette politique de confidentialité explique comment nous collectons, utilisons et protégeons vos données personnelles lorsque vous utilisez notre application.
    </p>

    <h2>1. Données collectées</h2>
    <p>Nous collectons uniquement les informations nécessaires à la création et à la gestion de votre compte, telles que :</p>
    <ul>
        <li>Nom et prénom</li>
        <li>Adresse email</li>
        <li>Mot de passe (stocké de manière sécurisée via un hachage cryptographique)</li>
        <li>Numéro de téléphone (optionnel)</li>
        <li>Rôle et statut du compte</li>
        <li>Préférences utilisateur (thème sombre, notifications, 2FA)</li>
        <li>Historique de connexion et journalisation de certaines actions</li>
        <li>Données liées aux candidatures, messages, notes et documents déposés</li>
    </ul>

    <h2>2. Utilisation des données</h2>
    <p>Les données personnelles sont utilisées exclusivement dans les buts suivants :</p>
    <ul>
        <li>Création, gestion et authentification de votre compte</li>
        <li>Envoi d’e-mails de confirmation, de sécurité, ou d’alertes configurées</li>
        <li>Personnalisation de votre expérience utilisateur</li>
        <li>Amélioration continue de notre service</li>
    </ul>

    <h2>3. Partage des données</h2>
    <p>
        Vos données personnelles ne sont en aucun cas vendues ou échangées. Elles ne sont partagées qu'avec les services internes nécessaires à l’exploitation du site, ou si la loi l’exige.
    </p>

    <h2>4. Sécurité des données</h2>
    <p>
        Nous mettons en place des mesures techniques et organisationnelles pour assurer la sécurité de vos données, telles que :
    </p>
    <ul>
        <li>Stockage chiffré des mots de passe (algorithme de hachage sécurisé)</li>
        <li>Connexion au site sécurisée via HTTPS</li>
        <li>Accès restreint à la base de données</li>
    </ul>

    <h2>5. Vos droits</h2>
    <p>Conformément au RGPD, vous disposez des droits suivants :</p>
    <ul>
        <li>Droit d’accès à vos données</li>
        <li>Droit de rectification ou suppression</li>
        <li>Droit d’opposition au traitement</li>
        <li>Droit à la portabilité</li>
        <li>Droit de retirer votre consentement à tout moment</li>
    </ul>
    <p>Pour exercer vos droits, contactez-nous à : <a href="secretariat.lps.official@gmail.com">secretariat.lps.official@gmail.com</a>.</p>

    <h2>6. Cookies</h2>
    <p>
        Nous utilisons uniquement des cookies strictement nécessaires au bon fonctionnement du site, notamment pour mémoriser vos préférences de thème (sombre/clair) et votre langue.
        Aucun cookie publicitaire ou analytique n’est utilisé sans votre consentement explicite.
    </p>

    <h2>7. Modifications de la politique</h2>
    <p>
        Nous nous réservons le droit de modifier cette politique à tout moment. Dernière mise à jour : <strong><?= date("d/m/Y") ?></strong>.
    </p>
</main>


<?php include '../View/Footer.php'; ?>

</body>
</html>
