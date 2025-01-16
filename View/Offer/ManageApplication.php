<?php
// File: ManageApplication.php
// Manage applications
session_start();

require dirname(__FILE__) . '/../../Model/Application.php';
require dirname(__FILE__) . '/../../Model/Offer.php';
require dirname(__FILE__) . '/../../Model/Company.php';
require dirname(__FILE__) . '/../../Presentation/Offer/Filter.php';

$returnUrl = $_SERVER["HTTP_REFERER"] ?? (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

error_reporting(E_ALL ^ E_DEPRECATED);
$user = $_SESSION["user"] ?? null;
if ($user === null) {
    header("Location: " . $returnUrl);
    exit();
}

$applications = Application::get_all_for_user($_SESSION['user_id']);
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
                <h1>Liste des candidature</h1>
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Email</th>
                            <th>Numéro</th>
                            <th>Date de début</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (isset($apply)){
                                foreach ($applications as $apply) {
                                $offer = Offer::get_by_id($apply->get_id_offer());
                                echo "<tr>";
                                echo "<td><a href='/View/Offer/Detail.php?id=" . $offer->get_id() . "'>" . $offer->get_title() . "</a></td>";
                                echo "<td><a href='mailto:" . $offer->get_email() . "'>" . $offer->get_email() . "</a></td>";
                                echo "<td><a href='tel:" . $offer->get_phone() . "'>" . $offer->get_phone() . "</a></td>";
                                echo "<td>" . $offer->get_begin_date() . "</td>";
                                switch ($apply->getStatus()) {
                                    case "Pending":
                                        echo "<td style='background: orange; text-align: center'>" . "En attente" . "</td>";
                                        break;
                                    case "Accepted":
                                        echo "<td style='background: green; text-align: center'>" . "Accepté" . "</td>";
                                        break;
                                    case "Rejected":
                                        echo "<td style='background: red; text-align: center'>" . "Refusé" . "</td>";
                                        break;
                                }
                            }
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
        <?php include dirname(__FILE__) . '/../Footer.php'; ?>
        <script type="text/javascript">
            function goToPage(id) {
                window.location.href('/Detail.php?id=' + id);
            }
        </script>
    </body>
</html>