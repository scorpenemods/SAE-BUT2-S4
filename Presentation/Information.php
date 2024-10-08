<?php

session_start(); // Démarre une session pour accéder aux données utilisateur

require_once "../Model/Person.php"; // Inclusion de la classe Person pour gérer les informations de l'utilisateur

// Vérifie si l'utilisateur est connecté
if (isset($_SESSION['user'])) {
    // Désérialise l'objet utilisateur stocké dans la session
    $person = unserialize($_SESSION['user']);

    // Vérifie si l'objet désérialisé est bien une instance valide de la classe Person
    if ($person instanceof Person) {
        // Récupère les informations de l'utilisateur et les sécurise avec htmlspecialchars pour éviter les injections XSS
        $userName = htmlspecialchars($person->getLogin());
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"> <!-- Définit l'encodage des caractères -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Rendre la page responsive -->
    <title>Informations du compte</title> <!-- Titre de la page -->
    <link rel="stylesheet" href="../View/Settings/Information.css"> <!-- Lien vers la feuille de style CSS -->
    <script type="text/javascript" src="../View/Settings/Settings.js"></script> <!-- Lien vers le script JavaScript -->
</head>
<body>
<section class="compte-info">
    <h2>Informations du compte</h2> <!-- Titre de la section -->
    <table>
        <tr>
            <td>Compte :</td>
            <td><?php echo $userName; ?></td> <!-- Affiche le nom d'utilisateur -->
        </tr>
        <tr>
            <td>Prénom :</td>
            <td><?php echo $prenom; ?></td> <!-- Affiche le prénom de l'utilisateur -->
        </tr>
        <tr>
            <td>Nom :</td>
            <td><?php echo $nom; ?></td> <!-- Affiche le nom de famille de l'utilisateur -->
        </tr>
        <tr>
            <td>Email :</td>
            <td><?php echo $email; ?></td> <!-- Affiche l'adresse email de l'utilisateur -->
            <td><a href="../rebase/Modely/MailChange/MailChange.php"><button>Modifier adresse e-mail</button></a></td> <!-- Lien pour modifier l'email -->
        </tr>
        <tr>
            <td>Numéro de téléphone :</td>
            <td><?php echo $telephone; ?></td> <!-- Affiche le numéro de téléphone de l'utilisateur -->
            <td><a href="../rebase/Modely/ChangePhoneNumber/ChangePhoneNumber.php"><button>Modifier numéro de téléphone</button></a></td> <!-- Lien pour modifier le numéro de téléphone -->
        </tr>
        <tr>
            <td>Mot de passe :</td>
            <td>********</td> <!-- Le mot de passe n'est pas affiché pour des raisons de sécurité -->
            <td><a href="ChangePassword.php"><button>Modifier mot de passe</button></a></td> <!-- Lien pour modifier le mot de passe -->
        </tr>
    </table>
</section>
</body>
</html>
