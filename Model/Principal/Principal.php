<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Petit Stage</title>
    <link rel="stylesheet" href="Principal.css">
    <script type="text/javascript" src="./Principal.js"></script>
</head>
<body>
    <header class="navbar">
        <div class="navbar-left">
            <img src="/Model/Accueil/LPS1.0.png" alt="Logo" class="logo"/>
            <span class="app-name">Le Petit Stage</span>
        </div>

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
                <a href="../InformationAccount/Information.php">Information</a>
                <a href="../Deconnexion/Deconnexion.php">Deconnexion</a>
            </div>
        </div>
    </header>


    <section class="Menus">
        <nav>
            <span onclick="widget(0)" class="Current">Accueil</span>
            <span onclick="widget(1)">Messagerie</span>
            <span onclick="widget(2)">Offres</span>
            <span onclick="widget(3)">Documents</span>
            <span onclick="widget(4)">Livret de suivi</span>
        </nav>
        <div class="Contenus">
            <div class="Visible">Contenu Accueil</div>
            <div class="Contenu">Contenu Messagerie</div>
            <div class="Contenu">Contenu Offres</div>
            <div class="Contenu">Contenu Documents</div>
            <div class="Contenu">Contenu Livret de suivi</div>
        </div>
    </section>

    <footer class="PiedDePage">
        <img src="../../Ressources/Logo_UPHF.png" alt="Logo uphf" width="10%">
        <a href="../Redirection/Redirection.php">Informations</a>
        <a href="../Redirection/Redirection.php">A propos</a>
    </footer>
</body>
</html>