<?php

require_once "../Model/Person.php"; // Inclusion de la classe Person pour gérer les informations de l'utilisateur

// Vérifie si l'utilisateur est connecté
if (isset($_SESSION['user'])) {
    // Désérialise l'objet utilisateur stocké dans la session
    $person = unserialize($_SESSION['user']);

    // Vérifie si l'objet désérialisé est bien une instance valide de la classe Person
    if ($person instanceof Person) {
        // Récupère les informations de l'utilisateur et les sécurise avec htmlspecialchars pour éviter les injections XSS
        $prenom = htmlspecialchars($person->getPrenom());
        $nom = htmlspecialchars($person->getNom());
        $email = htmlspecialchars($person->getEmail());
        $telephone = htmlspecialchars($person->getTelephone());
    } else {
        // Si l'objet désérialisé n'est pas valide, redirection vers la page de déconnexion
        header("Location: Logout.php");
        exit();
    }
} else {
    // Si aucune session utilisateur n'est trouvée, redirection vers la page de déconnexion
    header("Location: Logout.php");
    exit();
}


//TRADUCTION

// Vérifier si une langue est définie dans l'URL, sinon utiliser la session ou le français par défaut
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang; // Enregistrer la langue en session
} else {
    $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr'; // Langue par défaut
}

// Vérification si le fichier de langue existe, sinon charger le français par défaut
$langFile = "../locales/{$lang}.php";
if (!file_exists($langFile)) {
    $langFile = "../locales/fr.php";
}

// Charger les traductions
$translations = include $langFile;

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"> <!-- Définit l'encodage des caractères -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Rendre la page responsive -->
    <title><?= $translations['informations du compte']?></title> <!-- Titre de la page -->
    <link rel="stylesheet" href="../View/Settings/Information.css"> <!-- Lien vers la feuille de style CSS -->
    <script type="text/javascript" src="../View/Settings/Settings.js"></script> <!-- Lien vers le script JavaScript -->
</head>
<body>
<section id="infos" class="compte-info  <?php echo $darkModeEnabled ? 'dark-mode' : ''; ?>">
    <h2><?= $translations['informations du compte']?></h2> <!-- Titre de la section -->
    <table>
        <tr>
            <td><?= $translations['email_index']?></td>
            <td><?php echo $email; ?></td> <!-- Affiche l'adresse email de l'utilisateur -->
            <td><a href="../rebase/Modely/MailChange/MailChange.php"><button><?= $translations['modifier adresse email']?></button></a></td> <!-- Lien pour modifier l'email -->
        </tr>
        <tr>
            <td><?= $translations['prenom_register']?></td>
            <td><?php echo $prenom; ?></td> <!-- Affiche le prénom de l'utilisateur -->
        </tr>
        <tr>
            <td><?= $translations['nom_register']?></td>
            <td><?php echo $nom; ?></td> <!-- Affiche le nom de famille de l'utilisateur -->
        </tr>
        <tr>
            <td><?= $translations['telephone_register']?></td>
            <td><?php echo $telephone; ?></td> <!-- Affiche le numéro de téléphone de l'utilisateur -->
            <td><a href="../rebase/Modely/ChangePhoneNumber/ChangePhoneNumber.php"><button><?= $translations['modifier numéro de téléphone']?></button></a></td> <!-- Lien pour modifier le numéro de téléphone -->
        </tr>
        <tr>
            <td><?= $translations['mdp_index']?></td>
            <td>********</td> <!-- Le mot de passe n'est pas affiché pour des raisons de sécurité -->
            <td><a href="ChangePassword.php"><button><?= $translations['modifier mot de passe']?></button></a></td> <!-- Lien pour modifier le mot de passe -->
        </tr>
    </table>
    <a class="home-button" href='<?php echo $homePage; ?>'>
        Retour à la page d'accueil
    </a>
</section>
</body>
</html>
