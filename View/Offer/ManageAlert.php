<?php
// File: ManageAlert.php
// Manage alerts
session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/Model/Database.php';

$userId = $_SESSION['user'];

$database = (Database::getInstance());
$alerts = $database->getAlertByUser($userId);
?>
<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Détails de l'offre - Le Petit Stage</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="../css/ListCompanies.css">
        <link rel="stylesheet" href="../css/Button.css">
        <link rel="stylesheet" href="../css/Header.css">
        <link rel="stylesheet" href="../css/Footer.css">
        <link rel="stylesheet" href="../css/Apply.css">
        <link rel="stylesheet" href="../css/Notification.css">
        <script src="../Js/Notification.js" crossorigin="anonymous"></script>
        <script src="https://kit.fontawesome.com/166cd842ba.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    </head>
    <body>
        <?php include dirname(__FILE__) . '/../Header.php'; ?>
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
        <?php include dirname(__FILE__) . '/../Footer.php'; ?>
        <script type="text/javascript">
            function deleteAlert (id) {
                $.ajax({
                    url: '../../Presentation/Offer/Alert/Delete.php',
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