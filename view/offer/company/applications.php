<?php
session_start();
error_reporting(E_ALL ^ E_DEPRECATED);

require '../../../models/Applications.php';
require dirname(__FILE__) . '/../../../models/Offer.php';
require dirname(__FILE__) . '/../../../presenter/offer/filter.php';

$returnUrl = "/view/offer/list.php";
if (isset($_SERVER["HTTP_REFERER"])) $returnUrl = $_SERVER["HTTP_REFERER"];

// Parameters validation
$offerId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING) ?? 'Pending';
if (!$offerId) {
    header("Location: " . $returnUrl);
    die();
}

// Verification of the user
$groupeSecretariat = $_SESSION['secretariat'] ?? false;
$company_id = $_SESSION['company_id'] ?? 0;

if ($groupeSecretariat || ($company_id != 0 && Offer::isCompanyOffer($offerId, $company_id))) {
    $applications = Applications::getAllForOffer($offerId, $type);
} else {
    header("Location: ../../offer/list.php");
    die();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Candidatures</title>
    <link rel="stylesheet" href="../../css/header.css">
    <link rel="stylesheet" href="../../css/applications.css">
    <link rel="stylesheet" href="../../css/footer.css">
    <link rel="stylesheet" href="../../css/notification.css">
    <script src="../../js/notification.js"></script>
    <script src="https://kit.fontawesome.com/166cd842ba.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>
<body>
    <?php include '../../header.php' ?>
    <main>
        <div class="spacer">
            <div class="card">
                <h2>Liste des Candidatures pour <?php echo Applications::getOfferName($offerId);?></h2>
                <div id="liste-candidats">
                <?php
                    if ($applications) {
                        foreach($applications as $apply){
                            $id_user = $apply->getIdUser();
                            echo "<div class='candidat'>";
                            echo "<div class='info'>";
                            echo "<h3 class='nom'>".Applications::getUsername($id_user)."</h3>";
                            echo "<ul class='fichiers'>";
                            //Todo
                            echo "<li class='fichier' onclick='getFile(\"" . $id_user . "\", \"" . $offerId . "\", \"cv\")'>📎 CV</li>";
                            echo "<li class='fichier' onclick='getFile(\"" . $id_user . "\", \"" . $offerId . "\", \"motivation\")'>📎 Lettre de motivation</li>";
                            echo "</ul>";
                            echo "</div>";
                            echo "<span class='date'>".$apply->getCreatedAt()."</span>";
                            if (!$groupeSecretariat) {
                                echo "<div class='actions'>";
                                    echo "<form action='../../../presenter/offer/applications/validate.php' method='post'>";
                                        echo "<input type='hidden' name='id_offer' value='" . $offerId . "'>";
                                        echo "<input class='button accept' type='submit' name='Valider' value='Valider'>";
                                    echo "</form>";
                                    echo "<form action='../../../presenter/offer/applications/validate.php' method='post'>";
                                        echo "<input type='hidden' name='id_offer' value='" . $offerId . "'>";
                                        echo "<input class='button refuse' id='refuseButton' type='submit' name='Refuser' value='Refuser'>";
                                    echo "</form>";
                                echo "</div>";
                            }
                            echo "</div>";
                        }
                    } else {
                        echo "No applications found";
                    }
                ?>
                </div>
            </div>
        </div>
    </main>
    <?php include '../../footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Function to download the cv and motivation of users
        function getFile(id_user, id_offer, type_str) {
            $.ajax({
                url: '../../../presenter/offer/getfile.php',
                method: 'POST',
                data: { user: id_user, offer: id_offer, type: type_str },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(data, status, xhr) {
                    let disposition = xhr.getResponseHeader('Content-Disposition');
                    let matches = /"([^"]*)"/.exec(disposition);
                    let filename = (matches != null && matches[1] ? matches[1] : fileName + '.pdf');

                    let blob = new Blob([data], { type: 'application/pdf' });
                    let link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;

                    document.body.appendChild(link);

                    link.click();
                    link.parentNode.removeChild(link);
                },
                error: function(xhr, _status, _error) {
                    if (xhr.status === 404) {
                        sendNotification("failure", "Erreur", "Le fichier demandé n'existe pas");
                    } else {
                        sendNotification("warning", "Erreur", "Une erreur est survenue lors du téléchargement du fichier");
                    }
                }
            });
            }
    </script>
    </body>
</html>
