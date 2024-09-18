<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informations du compte</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="compte-info">
    <h2>Informations du compte</h2>
    <table>
        <tr>
            <td>Compte :</td>
            <td></td>
        </tr>
        <tr>
            <td>Prénom :</td>
            <td></td>
        </tr>
        <tr>
            <td>Nom :</td>
            <td></td>
        </tr>
        <tr>
            <td>Email :</td>
            <td></td>
            <td><a href="../MailChange/MailChange.php"><button>Modifier adresse e-mail</button></a></td>
        </tr>
        <tr>
            <td>Numéro de téléphone :</td>
            <td></td>
            <td><button onclick="window.location.href='modifier_tel.php'">Modifier numéro de téléphone</button></td>
        </tr>
        <tr>
            <td>Mot de passe :</td>
            <td></td>
            <td><a href="../ForgotPassword/ForgotPasswordMail.php"><button>Modifier mot de passe</button></a></td>

        </tr>
    </table>
</div>
</body>
</html>