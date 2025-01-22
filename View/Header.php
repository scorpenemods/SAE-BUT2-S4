<?php
// Vérifier si une langue est définie dans l'URL, sinon utiliser la session ou le français par défaut
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang; // Enregistrer la langue en session
} else {
    $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr'; // Langue par défaut
}

// Vérification si le fichier de langue existe, sinon charger le français par défaut
$langFile = "../Locales/{$lang}.php";
if (!file_exists($langFile)) {
    $langFile = "../Locales/fr.php";
}
$pagename = basename($_SERVER['PHP_SELF']);
// Charger les traductions
$translations = include $langFile;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../View/css/Header.css">
    <link rel="stylesheet" href="/View/Principal/Notifs.css">
    <script src="/View/Principal/Notif.js"></script>

</head>
<header class="navbar">
    <div class="navbar-left">
        <!-- Affichage du logo et du nom de l'application -->
        <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
        <?php
        if($userRole == 1){
            echo '<span class="app-name">Le Petit Stage - Etudiant</span>';
        }
        elseif($userRole == 2){
            echo '<span class="app-name">Le Petit Stage - Professeur</span>';
        }
        elseif($userRole == 3){
            echo '<span class="app-name">Le Petit Stage - Maitre de stage</span>';
        }
        elseif($userRole == 4 or $userRole == 5){
            echo '<span class="app-name">Le Petit Stage - Secrétariat</span>';
        }
        ?>
    </div>
    <div class="navbar-right">

        <div id="notification-icon" onclick="toggleNotificationPopup()">
            <img id="notification-icon-img" src="../Resources/Notif.png" alt="Notifications">
            <span id="notification-count" style="display: none;"></span>
        </div>

        <!-- Notification Popup -->
        <div id="notification-popup" class="notification-popup">
            <div class="notification-popup-header">
                <h3>Notifications</h3>
                <button onclick="closeNotificationPopup()">X</button>
            </div>
            <div class="notification-popup-content">
                <ul id="notification-list">
                    <!-- Notifications will be loaded here via JavaScript -->
                </ul>
            </div>
        </div>
        <!-- Affichage du nom de l'utilisateur connecté et contrôles pour changer la langue et le thème -->
        <p><?php echo $userName; ?></p>
        <?php
        include '../Model/LanguageSelection.php';
        ?>

        <!-- Bouton pour ouvrir le menu des paramètres -->
        <?php
        if ($pagename != "Settings.php") {
            echo '<button class="mainbtn" onclick="toggleMenu()">';
            echo '<img src="../Resources/Param.png" alt="Settings">';
            echo '</button>';
            echo '<div class="hide-list" id="settingsMenu">';
            echo '<a href="../Presentation/Settings.php">Information</a>';
            echo '<a href="../Presentation/Logout.php">Deconnexion</a>';
            echo '</div>';
        }
        ?>

    </div>
</header>