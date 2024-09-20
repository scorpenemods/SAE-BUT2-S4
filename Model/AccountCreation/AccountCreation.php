<?php
/*
include 'Service/DB.php';
session_start();


#Si le formulaire est rempli correctement on redirige vers une page de validation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('location: accuil.php');
}
*/
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../DefaultStyles/styles.css">
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
        </div>
    </header>

<div class="container">
<h1>Création du compte</h1>
<form action="../Redirection/Redirection.php" method="post">
    <p>
        <input type="radio" name="choice" value="student" id="student" required />
        <label for="student">Étudiant</label>

        <input type="radio" name="choice" value="tutorprofessor" id="tutorprofessor" required />
        <label for="tutorprofessor">Professeur referant :</label>

        <input type="radio" name="choice" value="tutorcompany" id="tutorcompany" required />
        <label for="tutorcompany">Tuteur professionnel :</label>
    </p>
    <p>
        <label for="function">Activité professionnelle/universitaire :</label>
        <input name="function" id="function" type="text" required/>
    </p>
    <p>
        <label for="email">E-mail :</label>
        <input name="email" id="email" type="text" required/>
    </p>
    <p>
        <label for="name">Nom :</label>
        <input name="name" id="name" type="text" required/>
    </p>
    <p>
        <label for="firstname">Prénom :</label>
        <input name="firstname" id="firstname" type="text" required/>
    </p>

    <p>
        <label for="phone">Téléphone :</label>
        <input name="phone" id="phone" type="text" required/>
    </p>

    <button type="submit">Valider</button>
</form>
</div>
    <footer class="PiedDePage">
        <img src="../../Ressources/Logo_UPHF.png" alt="Logo uphf" width="10%">
        <a href="../Redirection/Redirection.php">Informations</a>
        <a href="../Redirection/Redirection.php">A propos</a>
    </footer>
</body>
</html>