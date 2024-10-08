<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Petit Stage - Préférences</title>
    <link rel="stylesheet" href="../View/Settings/Preference.css">
</head>
<body>
<!-- Preferences section -->
<main>
    <h2>Préférences</h2>
    <div class="preferences">
        <div class="preference-item">
            <span>Notification :</span>
            <span>Off</span>
            <label class="switch">
                <input type="checkbox" name="notif" value="<?php if(isset($_COOKIE['notification'])) echo $_COOKIE['notification']; ?>" checked>
                <span class="slider"></span>
            </label>
            <span>On</span>
        </div>
        <div class="preference-item">
            <span>A2F :</span>
            <span>Off</span>
            <label class="switch">
                <input type="checkbox" name="a2f" value="<?php if(isset($_COOKIE['a2f'])) echo $_COOKIE['a2f']; ?>" checked>
                <span class="slider"></span>
            </label>
            <span>On</span>
        </div>
        <input type="submit" value="OK">
    </div>
</main>
</body>
</html>
