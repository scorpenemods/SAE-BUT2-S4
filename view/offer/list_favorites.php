<?php
session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/models/Database.php';
require $_SERVER['DOCUMENT_ROOT'] . '/models/Offer.php';
require $_SERVER['DOCUMENT_ROOT'] . '/models/Company.php';

// Verification of the user
$user = $_SESSION["user"] ?? null;
if ($user === null) {
    $returnUrl = $_SERVER["HTTP_REFERER"] ?? (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    header("Location: " . $returnUrl);
    exit();
}

// Load database instance & data from it
$likedOffers = Offer::getFavorites($user);
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
                <h1>Liste des offres favorites</h1>
                <table>
                    <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Entreprise</th>
                        <th>Description</th>
                        <th>Durée</th>
                        <th>Diplôme</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($likedOffers as $offer) {
                            echo '<tr data-id="' . $offer->getId() . '">';
                            echo '<td><a href="/view/offer/detail.php?id=' . $offer->getId() . '">' . $offer->getTitle() . '</a></td>';
                            echo '<td>' . $offer->getCompany()->getName() . '</td>';
                            echo '<td>' . $offer->getDescription() . '</td>';
                            echo '<td>' . $offer->getRealDuration() . '</td>';
                            echo '<td>' . $offer->getStudyLevel() . '</td>';
                            echo '<td><button class="button-delete" onclick="deleteAlert(' . $offer->getId() . ')">Supprimer</button></td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
        <?php include dirname(__FILE__) . '/../footer.php'; ?>
        <script type="text/javascript">
            function deleteAlert(id) {
                $.ajax({
                    url: '/presenter/offer/favorite.php',
                    type: 'POST',
                    data: {id: id},
                    success: function(msg, status, jqXHR) {
                        console.log(msg);
                        if (status === "success") {
                            sendNotification("success", "Succès", "Favoris supprimé avec succès!");

                            const row = document.querySelector(`tr[data-id="${id}"]`);
                            row.remove();
                        } else {
                            sendNotification("failure", "Erreur", msg || "Une erreur est survenue.");
                        }
                    }
                });
            }
        </script>
    </body>
</html>