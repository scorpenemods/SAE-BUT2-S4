<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informations du compte</title>
    <link rel="stylesheet" href="Information.css">
    <script type="text/javascript" src="Information.js"></script>
</head>
<body>
    <header class="navbar">
        <div class="navbar-left">
            <img src="/Model/Accueil/LPS1.0.png" alt="Logo" class="logo"/>
            <span class="app-name">Le Petit Stage</span>
        </div>
d
        <div class="navbar-right">
            <p>Lucien Newerkauswitchz</p>
            <!-- Language Switch -->
            <label class="switch">
                <input type="checkbox" id="language-switch" onchange="toggleLanguage()">
                <span class="slider round">
                        <span class="switch-sticker">🇫🇷</span> <!-- Sticker Français -->
                        <span class="switch-sticker switch-sticker-right">🇬🇧</span> <!-- Sticker English -->
                    </span>
            </label>
            <!-- Theme Switch -->
            <label class="switch">
                <input type="checkbox" id="theme-switch" onchange="toggleTheme()">
                <span class="slider round">
                        <span class="switch-sticker switch-sticker-right">🌙</span> <!-- Sticker Dark Mode -->
                        <span class="switch-sticker">☀️</span> <!-- Sticker Light Mode -->
                    </span>
            </label>
            <button class="mainbtn" onclick="turn()"><img src="../../Ressources/Param.png"></button>
            <div class="hide-list">
                <a href="Parametre.php">Information</a>
                <a href="../Deconnexion/Deconnexion.php">Deconnexion</a>
            </div>
        </div>
    </header>
    <section class="compte-info">
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
                <td><a href="../ChangePhoneNumber/ChangePhoneNumber.php"><button>Modifier numéro de téléphone</button></a></td>
            </tr>
            <tr>
                <td>Mot de passe :</td>
                <td></td>
                <td><a href="../ForgotPassword/ForgotPasswordMail.php"><button>Modifier mot de passe</button></a></td>

            </tr>
        </table>
    </section>
    <footer class="PiedDePage">
        <img src="../../Ressources/Logo_UPHF.png" alt="Logo uphf" width="10%">
        <a href="../Redirection/Redirection.php">Informations</a>
        <a href="../Redirection/Redirection.php">A propos</a>
    </footer>
</body>
</html>