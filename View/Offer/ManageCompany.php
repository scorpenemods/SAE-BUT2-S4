<?php
// File: ManageCompany.php
// Manage companies
session_start();

require dirname(__FILE__) . '/../../Model/PendingOffer.php';
require dirname(__FILE__) . '/../../Model/Company.php';
require dirname(__FILE__) . '/../../Presentation/Offer/Filter.php';

$returnUrl = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]";

error_reporting(E_ALL ^ E_DEPRECATED);

$secretariatGroup = $_SESSION['secretariat'] ?? false;
if (!$secretariatGroup) {
    header('Location : '. $returnUrl);
}

$companies = Company::get_all();
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
        <link rel="stylesheet" href="../css/HeaderAlt.css">
        <link rel="stylesheet" href="../css/FooterAlt.css">
        <link rel="stylesheet" href="../css/Apply.css">
        <link rel="stylesheet" href="../css/Notification.css">
        <script src="../Js/Notification.js" crossorigin="anonymous"></script>
        <script src="https://kit.fontawesome.com/166cd842ba.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    </head>
    <body>
        <?php include dirname(__FILE__) . '/../HeaderAlt.php'; ?>
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
                            echo "<td>" . $company->get_name() . "</td>";
                            echo "<td>" . $company->get_size() . "</td>";
                            echo "<td>" . $company->get_address() . "</td>";
                            echo "<td>" . $company->get_siren() . "</td>";
                            echo "<td><button class='delete-btn' onclick='deleteCompany(".$company->get_id().")'>Supprimer</button></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
        <?php include dirname(__FILE__) . '/../FooterAlt.php'; ?>
        <script type="text/javascript">
            function deleteCompany (id) {
                $.ajax({
                    url: '../../Presentation/Offer/Company/Delete.php',
                    type: 'POST',
                    data: {
                        company_id: id
                    },
                    success: function (data) {
                        if (JSON.parse(data).status === 'success') {
                            sendNotification("success", "Succés", "La société a bien été supprimée");
                            //Wait for the page to reload
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            sendNotification("failure", "Erreur", "La société n'a pas pu être supprimée");
                        }
                    }
                });
            }
        </script>
    </body>
</html>