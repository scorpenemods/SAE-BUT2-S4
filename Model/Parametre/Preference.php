<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Petit Stage - Préférences</title>
    <link rel="stylesheet" href="Preference.css">
</head>
<body>
<!-- Header section -->
<header>
    <div class="logo">
        <img src="SAE-BUT2-1.1\Ressources\LPS 1.0.png" alt="Logo">
        <h1>Le Petit Stage</h1>
    </div>
    <div class="user-section">
        <div class="language-dropdown">
            <span>Français</span>
            <span class="arrow">▼</span>
        </div>
        <div class="user-profile">
            <button class="logout-btn">Déconnexion</button>
            <span>Nom Prenom</span>
            <div class="user-avatar">
                <img src="default-avatar.png" alt="Avatar">
            </div>
            <div class="settings-icon">
                <img src="settings-icon.png" alt="Settings">
            </div>
        </div>
    </div>
</header>

<!-- Preferences section -->
<main>
    <h2>Préférences</h2>
    <div class="preferences">
        <div class="preference-item">
            <span>Notification :</span>
            <label class="switch">
                <input type="checkbox" checked>
                <span class="slider"></span>
            </label>
            <span>On</span>
            <span>Off</span>
        </div>
        <div class="preference-item">
            <span>A2F :</span>
            <label class="switch">
                <input type="checkbox" checked>
                <span class="slider"></span>
            </label>
            <span>On</span>
            <span>Off</span>
        </div>
        <div class="preference-item">
            <span>Cookies :</span>
            <label class="switch">
                <input type="checkbox">
                <span class="slider"></span>
            </label>
            <span>Nécessaire</span>
            <span>Tous</span>
        </div>
    </div>
</main>

<!-- Footer section -->
<footer>
    <div class="footer-links">
        <a href="#">Mentions légales</a>
        <a href="#">Aide</a>
    </div>
    <div class="footer-info">
        <img src="uphf-logo.png" alt="Université Polytechnique Hauts-de-France">
        <span>Université Polytechnique - Hauts-de-France</span>
    </div>
    <div class="footer-right">
        <a href="#">à propos</a>
    </div>
</footer>
</body>
</html>
