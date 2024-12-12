<?php
session_start();

require dirname(__FILE__) . '/../../models/Applications.php';
require dirname(__FILE__) . '/../../models/Offer.php';
require dirname(__FILE__) . '/../../models/Company.php';
require dirname(__FILE__) . '/../../presenter/offer/filter.php';

//$returnUrl = $_SERVER["HTTP_REFERER"] ?? $_SERVER["HTTP_ORIGIN"] . $_SERVER["REQUEST_URI"];

error_reporting(E_ALL ^ E_DEPRECATED);
$user = $_SESSION["user"] ?? null;
if ($user === null) {
    header("Location: /view/offer/list.php");
    exit();
}

$applications = Applications::getAllForUser($user);
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
                <h1>Tableau des candidature</h1>
                <table>
                    <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Email</th>
                        <th>Numéro</th>
                        <th>Date de début</th>
                        <th>Status</th>
                        <th>Voir l'offre</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($applications as $apply) {
                            $offer = Offer::getById($apply->getIdOffer());
                            echo "<tr>";
                            echo "<td>" . $offer->getTitle() . "</td>";
                            echo "<td>" . $offer->getEmail() . "</td>";
                            echo "<td>" . $offer->getPhone() . "</td>";
                            echo "<td>" . $offer->getBeginDate() . "</td>";
                            if ($apply->getStatus() == "Pending") {
                                echo "<td style='background: orange; text-align: center'>" . $apply->getStatus() . "</td>";
                            } else if ($apply->getStatus() == "Accepted") {
                                echo "<td style='background: green; text-align: center'>" . $apply->getStatus() . "</td>";
                            } else if ($apply->getStatus() == "Rejected") {
                                echo "<td style='background: red; text-align: center'>" . $apply->getStatus() . "</td>";
                            }
                            echo "<td><a class='show-btn' href='/view/offer/detail.php?id=" . $offer->getId() . "'>Voir l'offre</a></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
        <?php include dirname(__FILE__) . '/../footer.php'; ?>
        <script type="text/javascript">
            function goToPage(id) {
                window.location.href('/detail.php?id=' + id);
            }
        </script>
    </body>
</html>