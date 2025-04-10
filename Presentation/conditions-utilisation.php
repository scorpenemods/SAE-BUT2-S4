<?php
// Chargement de la langue
session_start();
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;
} else {
    $lang = $_SESSION['lang'] ?? 'fr';
}

$langFile = "../Locales/{$lang}.php";
if (!file_exists($langFile)) {
    $langFile = "../Locales/fr.php";
}
$translations = include $langFile;

// Dark mode ?
$darkModeEnabled = $_COOKIE['darkMode'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Lien vers la feuille de style par défaut -->
    <link rel="stylesheet" href="../View/AccountCreation/AccountCreation.css">
    <script src="../View/Home/Lobby.js" defer></script>
    <title>Conditions Générales d’Utilisation</title>
    <link rel="stylesheet" href="../View/MentionLegal/confidentialite.css">

</head>
<body class="<?= $darkModeEnabled ? 'dark-mode' : '' ?>">

<header class="navbar">
    <div class="navbar-left">
        <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
        <span class="app-name"><?= $translations['titre_appli'] ?? 'LPS' ?></span>
    </div>
</header>

<main class="privacy-policy-container">
    <h1>Conditions Générales d’Utilisation</h1>

    <p>
        Les présentes conditions générales d’utilisation (CGU) régissent l’accès et l’utilisation de notre plateforme.
        En accédant au site, vous acceptez pleinement et entièrement ces conditions.
    </p>

    <h2>1. Objet du service</h2>
    <p>
        La plateforme permet aux utilisateurs de créer un compte, de consulter et de postuler à des offres d’emploi ou de stage, d’interagir avec d’autres membres et de gérer leurs documents professionnels.
    </p>

    <h2>2. Inscription et compte utilisateur</h2>
    <ul>
        <li>L’utilisateur doit fournir des informations exactes lors de la création de son compte.</li>
        <li>Un seul compte est autorisé par utilisateur.</li>
        <li>L’utilisateur est responsable de la confidentialité de ses identifiants.</li>
    </ul>

    <h2>3. Engagements des utilisateurs</h2>
    <ul>
        <li>Ne pas publier de contenu illicite, offensant ou inapproprié.</li>
        <li>Respecter les autres membres de la plateforme.</li>
        <li>Ne pas usurper l’identité d’une autre personne.</li>
        <li>Utiliser la plateforme uniquement à des fins légales et professionnelles.</li>
    </ul>

    <h2>4. Propriété intellectuelle</h2>
    <p>
        Tous les éléments du site (code, textes, images, logos, etc.) sont protégés par le droit de la propriété intellectuelle. Toute reproduction totale ou partielle est interdite sans autorisation préalable.
    </p>

    <h2>5. Responsabilités</h2>
    <ul>
        <li>Nous mettons tout en œuvre pour assurer la disponibilité et la sécurité du service.</li>
        <li>Nous ne sommes pas responsables des contenus postés par les utilisateurs.</li>
        <li>Nous nous réservons le droit de suspendre un compte en cas de non-respect des CGU.</li>
    </ul>

    <h2>6. Suppression du compte</h2>
    <p>
        L’utilisateur peut à tout moment demander la suppression de son compte via la section "profil" ou en contactant le support.
    </p>

    <h2>7. Modifications des CGU</h2>
    <p>
        Les présentes CGU peuvent être modifiées à tout moment. L’utilisateur sera informé des modifications lors de sa prochaine connexion. Date de dernière mise à jour : <strong><?= date("d/m/Y") ?></strong>.
    </p>

    <h2>8. Contact</h2>
    <p>
        Pour toute question ou réclamation : <a href="secretariat.lps.official@gmail.com">secretariat.lps.official@gmail.com</a>
    </p>
</main>

<?php include '../View/Footer.php'; ?>

</body>
</html>
