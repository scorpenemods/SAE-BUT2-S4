<?php
/*
 * manage_companies.php
 * Display the list of companies, and allow to delete them.
 */
session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/models/Company.php';
require $_SERVER['DOCUMENT_ROOT'] . '/models/PendingOffer.php';
require $_SERVER['DOCUMENT_ROOT'] . '/presenter/offer/filter.php';

$returnUrl = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]/view/offer/list.php";

// Verification of the user
$secretariat_group = $_SESSION['secretariat'] ?? false;
if (!$secretariat_group) {
    header('Location : '. $returnUrl);
    exit();
}

// Load all companies from the database
$companies = Company::getAll();
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
                <h1>Liste des entreprises</h1>
                <table>
                    <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Employés</th>
                        <th>Addresse</th>
                        <th>Siren</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($companies as $company) {
                            echo "<tr>";
                            echo "<td>" . $company->getName() . "</td>";
                            echo "<td>" . $company->getSize() . "</td>";
                            echo "<td>" . $company->getAddress() . "</td>";
                            echo "<td>" . $company->getSiren() . "</td>";
                            echo "<td><button class='delete-btn' onclick='deleteCompany(".$company->getId().")'>Supprimer</button></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
        <?php include dirname(__FILE__) . '/../footer.php'; ?>
        <script type="text/javascript">
            function deleteCompany (id) {
                $.ajax({
                    url: '../../presenter/offer/company/delete.php',
                    type: 'POST',
                    data: {
                        company_id: id
                    },
                    success: function (data) {
                        if (data.status === 'success') {
                            $('#notification').showNotification('success', 'La société a bien été supprimée');
                            sendNotification("success", "Succés", "La société a bien été supprimée");
                            location.reload();
                        } else {
                            sendNotification("failure", "Erreur", "La société n'a pas pu être supprimée");
                        }
                    }
                });
            }
        </script>
    </body>
</html>