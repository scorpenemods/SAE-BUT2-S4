<?php
// File: Application.php
// Create an application
require '../../../Model/Application.php';
session_start();

require dirname(__FILE__) . '/../../../Model/Offer.php';
require dirname(__FILE__) . '/../../../Presentation/Offer/Filter.php';

$returnUrl = "/View/Offer/List.php";
if (isset($_SERVER["HTTP_REFERER"])) {
    $returnUrl = $_SERVER["HTTP_REFERER"];
}


error_reporting(E_ALL ^ E_DEPRECATED);
$offerId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING) ?? 'Pending';
if (!$offerId) {
    header("Location: " . $returnUrl);
    die();
}

// Verification de qui est l'utilisateur
$groupeSecretariat = $_SESSION['secretariat'] ?? false;
$companyId = $_SESSION['companyId'] ?? 0;

if ($groupeSecretariat || ($companyId != 0 && Offer::is_company_offer($offerId, $companyId))) {
    $applications = Application::get_all_for_offer($offerId, $type) ?? [];
} else {
    header("Location: ../../Offer/List.php");
    die();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Candidatures</title>
    <link rel="stylesheet" href="../../css/Header.css">
    <link rel="stylesheet" href="../../css/Application.css">
    <link rel="stylesheet" href="../../css/Footer.css">
    <link rel="stylesheet" href="../../css/Notification.css">
    <script src="../../Js/Notification.js"></script>
    <script src="https://kit.fontawesome.com/166cd842ba.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>
<body>
    <?php include '../../Header.php' ?>
    <main>
        <div class="spacer">
            <div class="card">
                <h2>Liste des Candidatures pour <?php echo Application::get_offer_name($offerId);?></h2>
                <div id="liste-candidats">
                <?php
                    if ($applications != []) {
                        foreach($applications as $apply){
                            $idUser = $apply->get_id_user();
                            echo "<div class='candidat'>";
                            echo "<div class='info'>";
                            echo "<h3 class='nom'>".Application::get_username($idUser)."</h3>";
                            echo "<ul class='fichiers'>";
                            //Todo
                            echo "<li class='fichier' onclick='getFile(\"" . $idUser . "\", \"" . $offerId . "\", \"cv\")'>📎 CV</li>";
                            echo "<li class='fichier' onclick='getFile(\"" . $idUser . "\", \"" . $offerId . "\", \"motivation\")'>📎 Lettre de motivation</li>";
                            echo "</ul>";
                            echo "</div>";
                            echo "<span class='date'>".$apply->get_created_at()."</span>";
                            if (!$groupeSecretariat) {
                                echo "<div class='actions'>";
                                    echo "<form action='../../../Presentation/Offer/Applications/Validate.php' method='post'>";
                                        echo "<input type='hidden' name='id_offer' value='" . $offerId . "'>";
                                        echo "<input class='button accept' type='submit' name='Valider' value='Valider'>";
                                    echo "</form>";
                                    echo "<form action='../../../Presentation/Offer/Applications/Validate.php' method='post'>";
                                        echo "<input type='hidden' name='id_offer' value='" . $offerId . "'>";
                                        echo "<input class='button refuse' id='refuseButton' type='submit' name='Refuser' value='Refuser'>";
                                    echo "</form>";
                                echo "</div>";
                            }
                            echo "</div>";
                        }
                    } else {
                        echo "No Applications found";
                    }
                ?>
                </div>
            </div>
        </div>
    </main>
    <?php include '../../Footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function getFile(idUser, idOffer, typeStr) {
            $.ajax({
                url: '../../../presenter/offer/getfile.php',
                method: 'POST',
                data: { user: idUser, offer: idOffer, type: typeStr },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(data, status, xhr) {
                    let disposition = xhr.getResponseHeader('Content-Disposition');
                    let matches = /"([^"]*)"/.exec(disposition);
                    let filename = (matches != null && matches[1] ? matches[1] : fileName + '.pdf');

                    // Create blob link to download
                    let blob = new Blob([data], { type: 'application/pdf' });
                    let link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;

                    // Append to html link element page
                    document.body.appendChild(link);

                    // Start download
                    link.click();

                    // Clean up and remove the link
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
