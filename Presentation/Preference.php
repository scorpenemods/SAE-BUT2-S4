<?php
session_start();
require_once '../Model/Database.php'; // Classe pour gérer la connexion à la base de données
require_once '../Model/Person.php';

if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    if ($person instanceof Person) {
        $userId = $person->getUserId();
        $userName = htmlspecialchars($person->getPrenom()) . ' ' . htmlspecialchars($person->getNom());
    }
} else {
    header("Location: Logout.php");
    exit();
}

// Connexion à la base de données
$db = new Database();

// Si la requête est envoyée via POST, traiter la mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notif_value = isset($_POST['notif']) ? 1 : 0;
    $a2f_value = isset($_POST['a2f']) ? 1 : 0;

    // Mettre à jour ou insérer les préférences de l'utilisateur
    if ($db->setUserPreferences($userId, $notif_value, $a2f_value)) {
        // Affiche un message de succès
        $message = "Les préférences ont été mises à jour avec succès.";
        $alert_class = "alert-success";
        header("Location: Settings.php");
    } else {
        header("Location: Settings.php");
        $message = "Erreur lors de la mise à jour des préférences.";
        $alert_class = "alert-error";
    }
}

// Récupérer les préférences actuelles de l'utilisateur
$preferences = $db->getUserPreferences($userId);
$notif = isset($preferences['notification']) && $preferences['notification'] == 1 ? 'checked' : '';
$a2f = isset($preferences['a2f']) && $preferences['a2f'] == 1 ? 'checked' : '';
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

<main>
    <h2>Préférences</h2>
    <form class="preferences" method="POST" action="preference.php">
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
            <span>A2F :</span>
            <span>Off</span>
            <label class="switch">
                <input type="checkbox" name="a2f" <?php echo $a2f; ?>>
                <span class="slider"></span>
            </label>
            <span>On</span>
        </div>
        <input type="submit" value="Enregistrer les préférences">
    </form>
</main>

</body>
</html>
