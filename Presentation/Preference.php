<?php

if(isset($_POST['notif']))
{
    setcookie('notification', $_POST['notif'], time() + 50, null, null, false, true); // Start the cookie
    setcookie('a2f', $_POST['a2f'], time() + 50, null, null, false, true); // Start the cookie
    header("Location: ./Preference.php");
    echo "Le cookie a été créé avec la valeur : " . $_POST['notif'];
}
?>
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
    <form class="preferences" method="post" action="./Preference.php">
        <div class="preference-item">
            <span>Notification :</span>
            <span>Off</span>
            <label class="switch">
                <input type="checkbox" name="notif" checked>
                <span class="slider"></span>
            </label>
            <span>On</span>
        </div>
        <div class="preference-item">
            <span>A2F :</span>
            <span>Off</span>
            <label class="switch">
                <input type="checkbox" name="a2f" checked>
                <span class="slider"></span>
            </label>
            <span>On</span>
        </div>
        <input type="submit" value="OK">
    </form>
</main>
</body>
</html>
