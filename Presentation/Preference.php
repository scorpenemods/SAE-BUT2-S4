<?php
require_once '../Model/Database.php'; // Classe pour gérer la connexion à la base de données
require_once '../Model/Person.php';

require_once "../Model/Config.php";


if (isset($_SESSION['last_activity'])) {
    // Calculer le temps d'inactivité
    $inactive_time = time() - $_SESSION['last_activity'];

    // Si le temps d'inactivité dépasse le délai autorisé
    if ($inactive_time > SESSION_TIMEOUT) {
        // Détruire la session et rediriger vers la page de connexion
        session_unset();
        session_destroy();
        header("Location: Logout.php");
    }
}
$_SESSION['last_activity'] = time();

if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    if ($person instanceof Person) {
        $userId = $person->getId();
        $userName = htmlspecialchars($person->getPrenom()) . ' ' . htmlspecialchars($person->getNom());
    }
} else {
    header("Location: Logout.php");
    exit();
}

// Connexion à la base de données
$db = (Database::getInstance());

// Si la requête est envoyée via POST, traiter la mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notif_value = isset($_POST['notif']) ? 1 : 0;
    $darkmode_value = isset($_POST['darkmode']) ? 1 : 0; // Nouveau pour darkmode

    // Mettre à jour ou insérer les préférences de l'utilisateur
    if ($db->setUserPreferences($userId, $notif_value, $darkmode_value)) {
        // Définir le message de succès dans la session
        $_SESSION['success_message'] = "Les préférences ont été mises à jour avec succès.";
        header("Location: Settings.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Erreur lors de la mise à jour des préférences.";
        header("Location: Settings.php");
        exit();
    }
}


// Récupérer les préférences actuelles de l'utilisateur
$preferences = $db->getUserPreferences($userId);
$notif = isset($preferences['notification']) && $preferences['notification'] == 1 ? 'checked' : '';
$a2f = isset($preferences['a2f']) && $preferences['a2f'] == 1 ? 'checked' : '';
$darkmode = isset($preferences['darkmode']) && $preferences['darkmode'] == 1 ? 'checked' : ''; // Nouveau pour darkmode
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Petit Stage - Préférences</title>
    <link rel="stylesheet" href="../View/Settings/Preference.css">
    <style>
        .alert {
            padding: 15px;
            background-color: #4CAF50; /* Green */
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            text-align: center;
            display: none;
        }
        .alert-error {
            background-color: #f44336; /* Red */
        }
        .alert-success {
            background-color: #4CAF50; /* Green */
        }
    </style>
</head>
<body>

<?php if (isset($message)): ?>
    <div class="alert <?php echo $alert_class; ?>" id="alertMessage"><?php echo $message; ?></div>
    <script>
        // Afficher l'alerte pendant 5 secondes
        document.getElementById('alertMessage').style.display = 'block';
        setTimeout(function() {
            document.getElementById('alertMessage').style.display = 'none';
        }, 5000); // L'alerte disparaît après 5 secondes
    </script>
<?php endif; ?>

<section class="preferences">
    <h2>Préférences</h2>
    <form method="POST" action="./Settings.php?section=preferences">
        <div class="preference-item">
            <span>Notification :</span>
            <span>Off</span>
            <label class="switch">
                <input type="checkbox" name="notif" <?php echo $notif; ?>>
                <span class="slider"></span>
            </label>
            <span>On</span>
        </div>
        <div class="preference-item">
            <span>Mode Sombre :</span> <!-- Nouveau pour darkmode -->
            <span>Off</span>
            <label class="switch">
                <input type="checkbox" name="darkmode" <?php echo $darkmode; ?>>
                <span class="slider"></span>
            </label>
            <span>On</span>
        </div>
        <input type="submit" value="Enregistrer les préférences">
    </form>
</section>

</body>
</html>
