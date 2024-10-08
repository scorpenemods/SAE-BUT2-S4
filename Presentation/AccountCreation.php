<!DOCTYPE html>
<html>
<head>
    <!-- Lien vers la feuille de style par défaut -->
    <link rel="stylesheet" href="../rebase/Modely/DefaultStyles/styles.css">
</head>
<body>
<header class="navbar">
    <div class="navbar-left">
        <!-- Logo de l'application -->
        <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
        <span class="app-name">Le Petit Stage</span> <!-- Nom de l'application -->
    </div>

    <div class="navbar-right">
        <p>Guest</p> <!-- Affichage du nom de l'utilisateur ou "Guest" si non connecté -->

        <!-- Commutateur pour changer la langue -->
        <label class="switch">
            <input type="checkbox" id="language-switch" onchange="toggleLanguage()">
            <span class="slider round">
                <span class="switch-sticker">🇫🇷</span> <!-- Sticker pour la langue française -->
                <span class="switch-sticker switch-sticker-right">🇬🇧</span> <!-- Sticker pour la langue anglaise -->
            </span>
        </label>

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

<div class="container">
    <h1>Création du compte</h1> <!-- Titre de la page de création de compte -->

    <!-- Formulaire pour la création de compte -->
    <form action="Register.php" method="post">
        <p>
            <!-- Options pour le type de compte à créer -->
            <input type="radio" name="choice" value="student" id="student" required />
            <label for="student">Étudiant</label>

            <input type="radio" name="choice" value="tutorprofessor" id="tutorprofessor" required />
            <label for="tutorprofessor">Professeur référant</label>

            <input type="radio" name="choice" value="tutorcompany" id="tutorcompany" required />
            <label for="tutorcompany">Tuteur professionnel</label>

            <input type="radio" name="choice" value="secritariat" id="secritariat" required />
            <label for="secritariat">Secrétariat</label>
        </p>

        <!-- Champ pour la fonction professionnelle/universitaire -->
        <p>
            <label for="function">Activité professionnelle/universitaire :</label>
            <input name="function" id="function" type="text" required/>
        </p>

        <!-- Champ pour l'adresse e-mail -->
        <p>
            <label for="email">E-mail :</label>
            <input name="email" id="email" type="email" required/>
        </p>

        <!-- Champ pour le nom de famille -->
        <p>
            <label for="name">Nom :</label>
            <input name="name" id="name" type="text" required/>
        </p>

        <!-- Champ pour le prénom -->
        <p>
            <label for="firstname">Prénom :</label>
            <input name="firstname" id="firstname" type="text" required/>
        </p>

        <!-- Champ pour le numéro de téléphone -->
        <p>
            <label for="phone">Téléphone :</label>
            <input name="phone" id="phone" type="text" required/>
        </p>

        <!-- Champ pour le mot de passe -->
        <p>
            <label for="password">Mot de passe :</label>
            <input name="password" id="password" type="password" required/>
        </p>

        <!-- Champ pour confirmer le mot de passe -->
        <p>
            <label for="confirm_password">Confirmer le mot de passe :</label>
            <input name="confirm_password" id="confirm_password" type="password" required/>
        </p>

        <!-- Bouton de validation -->
        <button type="submit">Valider</button>
    </form>
</div>

<!-- Pied de page avec logo et liens vers des pages d'informations -->
<footer class="PiedDePage">
    <img src="../Resources/Logo_UPHF.png" alt="Logo UPHF" width="10%"> <!-- Logo UPHF -->
    <a href="Redirection.php">Informations</a> <!-- Lien vers une page d'informations -->
    <a href="Redirection.php">À propos</a> <!-- Lien vers une page "À propos" -->
</footer>

</body>
</html>
