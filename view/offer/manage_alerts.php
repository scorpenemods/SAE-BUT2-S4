<?php
session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/models/Database.php';

// Verification of the user
$user = $_SESSION["user"] ?? null;
if ($user === null) {
    $returnUrl = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]/view/offer/list.php";
    header("Location: " . $returnUrl);
    exit();
}

// Load database instance & data from it
$database = (Database::getInstance());
$alerts = $database->getAlertByUser($user);
?>
<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Détails de l'offre - Le Petit Stage</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="/view/css/list_companies.css">
        <link rel="stylesheet" href="/view/css/button.css">
        <link rel="stylesheet" href="/view/css/header.css">
        <link rel="stylesheet" href="/view/css/footer.css">
        <link rel="stylesheet" href="/view/css/apply.css">
        <link rel="stylesheet" href="../css/notification.css">
        <script src="../js/notification.js" crossorigin="anonymous"></script>
        <script src="https://kit.fontawesome.com/166cd842ba.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    </head>
    <body>
        <?php include dirname(__FILE__) . '/../header.php'; ?>
        <main>
            <div class="container-table">
                <h1>Liste des alertes</h1>
                <table>
                    <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Poste</th>
                        <th>Durée</th>
                        <th>Localisation</th>
                        <th>Diplôme</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($alerts as $alert) {
                            echo '<tr>';
                            echo '<td>' . $alert['title'] . '</td>';
                            echo '<td>' . $alert['study_level'] . '</td>';
                            echo '<td>' . $alert['duration'] . '</td>';
                            echo '<td>' . $alert['address'] . '</td>';
                            echo '<td>' . $alert['salary'] . '</td>';
                            echo '<td><button class="delete-btn" onclick="deleteAlert(' . $alert['id'] . ')">Supprimer</button></td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
        <?php include dirname(__FILE__) . '/../footer.php'; ?>
        <script type="text/javascript">
            function deleteAlert (id) {
                $.ajax({
                    url: '../../presenter/offer/alert/delete.php',
                    type: 'POST',
                    data: {
                        id: id
                    },
                    success: function (data) {
                        if (data.status === 'success') {
                            sendNotification("success", "Succés", "L'alerte a bien été supprimée.");
                            location.reload();
                        } else {
                            console.log(data)
                            sendNotification("failure", "Erreur", "L'alerte n'a pas pu être supprimée.");
                        }
                    }
                });
            }
        </script>
    </body>
</html>