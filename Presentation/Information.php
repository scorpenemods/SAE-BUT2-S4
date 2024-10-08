<?php

session_start(); // Start the session


require_once "../Model/Person.php"; // Make sure the Person class is included

// Check if the user is logged in
if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    if ($person instanceof Person) { // Check if the unserialized object is a valid Person object
        // Retrieve user information
        $userName = htmlspecialchars($person->getLogin());
        $prenom = htmlspecialchars($person->getPrenom());
        $nom = htmlspecialchars($person->getNom());
        $email = htmlspecialchars($person->getEmail());
        $telephone = htmlspecialchars($person->getTelephone());
    } else {
        // Invalid session, redirect to deconnexion
        header("Location: Logout.php");
        exit();
    }
} else {
    // No user session found, redirect to deconnexion
    header("Location: Logout.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informations du compte</title>
    <link rel="stylesheet" href="../View/Settings/Information.css">
    <script type="text/javascript" src="../View/Settings/Settings.js"></script>
</head>
<body>
<section class="compte-info">
    <h2>Informations du compte</h2>
    <table>
        <tr>
            <td>Compte :</td>
            <td><?php echo $userName; ?></td> <!-- Display the username -->
        </tr>
        <tr>
            <td>Prénom :</td>
            <td><?php echo $prenom; ?></td> <!-- Display the first name -->
        </tr>
        <tr>
            <td>Nom :</td>
            <td><?php echo $nom; ?></td> <!-- Display the last name -->
        </tr>
        <tr>
            <td>Email :</td>
            <td><?php echo $email; ?></td> <!-- Display the email -->
            <td><a href="../rebase/Modely/MailChange/MailChange.php"><button>Modifier adresse e-mail</button></a></td>
        </tr>
        <tr>
            <td>Numéro de téléphone :</td>
            <td><?php echo $telephone; ?></td> <!-- Display the phone number -->
            <td><a href="../rebase/Modely/ChangePhoneNumber/ChangePhoneNumber.php"><button>Modifier numéro de téléphone</button></a></td>
        </tr>
        <tr>
            <td>Mot de passe :</td>
            <td>********</td> <!-- Password is not displayed -->
            <td><a href="ForgotPasswordMail.php"><button>Modifier mot de passe</button></a></td>
        </tr>
    </table>
</section>
</body>
</html>
