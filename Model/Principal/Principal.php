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
    <header class="Entete">
        <div class="nameapp">
            <img src="../../Ressources/Logo_UPHF.png" alt="Logo" class="logo" width="5%"/>
            <p class="app-name">Le Petit Stage</p>
        </div>
        <div class="rightheader">
            <div class="trad">
                <img src="../../Ressources/LogoTrad.png" alt="logo traduction" width="5%">
                <p> Francais </p>
            </div>
            <div class="infos">
                <a href="../Redirection/Redirection.php"><button>Deconnexion</button></a>
                <p>Prenom nom</p>
                <button class="mainbtn" onclick="turn()"><img src="../../Ressources/Param.png"></button>
                <div class="hide-list">
                    <a href="../Redirection/Redirection.php">Information</a>
                    <a href="../Login/Login.php">Deconnexion</a>
                </div>
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