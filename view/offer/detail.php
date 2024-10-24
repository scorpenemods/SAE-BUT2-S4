<?php
session_start();

require dirname(__FILE__) . '/../../models/PendingOffer.php';
require dirname(__FILE__) . '/../../models/Company.php';
require dirname(__FILE__) . '/../../models/Media.php';
require dirname(__FILE__) . '/../../presenter/offer/filter.php';

$returnUrl = "/view/offer/list.php";
if (isset($_SERVER["HTTP_REFERER"])) {
    $returnUrl = $_SERVER["HTTP_REFERER"];
}

error_reporting(E_ALL ^ E_DEPRECATED);
$offerId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);
if ($offerId == null) {
    header("Location: " . $returnUrl);
    die();
}

// Verification de qui est l'utilisateur
$company_id = 0;
$groupeSecretariat = false;
if (isset($_SESSION['secretariat'])) {
    $groupeSecretariat = $_SESSION['secretariat'];
}
if (isset($_SESSION['company_id'])) {
    $company_id = $_SESSION['company_id'];
    if ($company_id != 0 && !Offer::isCompanyOffer($offerId, $company_id)) {
        header("Location: ../offer/list.php");
        die();
    }
}



if ($type == null || $type == 'all' || $type == 'inactive') {
    $offer = Offer::getById($offerId);
} else {
    $offer = PendingOffer::getByOfferId($offerId);
    if ($type == 'updated') {
        $offer_old = Offer::getById($offer->getOfferId());
    }
}

$isAlreadyPending = Offer::isAlreadyPending($offerId);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de l'offre - Le Petit Stage</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/view/css/detail.css">
    <link rel="stylesheet" href="/view/css/button.css">
    <link rel="stylesheet" href="/view/css/header.css">
    <link rel="stylesheet" href="/view/css/footer.css">
    <link rel="stylesheet" href="/view/css/apply.css">
    <script src="https://kit.fontawesome.com/166cd842ba.js" crossorigin="anonymous"></script>
</head>
<body>
<?php include dirname(__FILE__) . '/../header.php'; ?>
<main>
    <?php if ($type == 'updated') {
        echo "<div class='offer-card' style='margin-bottom: 10px'>";
            echo "<div class='offer-header'>";
                echo "<div class='offer-title'>";
                    echo "<div>";
                        if ($offer->getTitle() != $offer_old->getTitle()) {
                            echo "<h2 class='diff-old'>" . $offer_old->getTitle() . "</h2>";
                            echo "<h2 class='diff-new'>" . $offer->getTitle() . "</h2>";
                        } else {
                            echo "<h2>" . $offer->getTitle() . "</h2>";
                        }
                        echo "<p class='offer-date'>" . "Publiée le " . $offer->getCreatedAt() . "</p>";
                    echo "</div>";
        //Bouton ne pas toucher
                echo "<div class='apply-button-container'>";
                    echo "<form action='../../presenter/offer/secretariat/deny.php' method='get' id='deny-form' style='display: none;'>";
                        echo "<input type='hidden' name='id' value='" . $offer->getId() . "'>";
                        echo "<button class='apply-button-edit'>Refuser</button>";
                    echo "</form>";
                    echo "<form action='../../presenter/offer/secretariat/validate.php' method='post' id='validate-form' style='display: none;'>";
                        echo "<input type='hidden' name='id' value='" . $offer->getId() . "'>";
                        echo "<button class='apply-button-edit'>Valider</button>";
                    echo "</form>";
                echo "</div>";
        //Fin bouton
            echo "</div>";
        echo "</div>";
        echo "<div class='offer-content'>";
            echo "<h3 class='company-name'>";
            echo "<i class='fas fa-building'></i>";
            echo $offer_old->getCompany()->getName();
            echo "</h3>";
            echo "<div class='offer-details'>";
                echo "<div class='detail-item'>";
                    echo "<i class='fas fa-clock'></i>";
                    echo "<span>";
                    if ($offer->getRealDuration() != $offer_old->getRealDuration()){
                        echo "<p class='diff-old'>" . $offer_old->getRealDuration() . "</p>";
                        echo "<p class='diff-new'>" . $offer->getRealDuration() . "</p>";
                    }
                    else{
                        echo $offer->getRealDuration();
                    }
                    echo "</span>";
                echo "</div>";
                    echo "<div class='detail-item'>";
                        echo "<i class='fa-solid fa-phone'></i>";
                        echo "<span>";
                        if ($offer->getPhone() != $offer_old->getPhone()){
                            echo "<a class='diff-old' href='tel:" . $offer_old->getPhone() ."'>" . $offer_old->getPhone() . "</a>";
                            echo "<a class='diff-new' href='tel:" . $offer->getPhone() ."'>" . $offer->getPhone() . "</a>";
                        }
                        else{
                            echo "<a href='tel:" . $offer->getPhone() . "'>" . $offer->getPhone() . "</a>";
                        }
                        echo "</span>";
                    echo "</div>";
                echo "<div class='detail-item'>";
        echo "<i class='fa-solid fa-envelope'></i>";
        echo "<span>";
        if ($offer->getEmail() != $offer_old->getEmail()){
            echo "<a class='diff-old' href='mailto:" . $offer_old->getEmail() . "'>" . $offer_old->getEmail() . "</a>";
            echo "<a class='diff-new' href='mailto:" . $offer->getEmail() . "'>" . $offer->getEmail() . "</a>";
        }
        else{
            echo "<a href='mailto:" . $offer->getEmail() . "'>" . $offer->getEmail() . "</a>";
        }
        echo "</span>";
        echo "</div>";
        echo "<div class='detail-item'>";
        echo "<i class='fa-solid fa-link'></i>";
        echo "<span>";
        if ($offer->getDomain() != $offer_old->getDomain()){
            echo "<a class='diff-old' href='https://" . $offer_old->getDomain() . "'>" . $offer_old->getDomain() . "</a>";
            echo "<a class='diff-new' href='https://" . $offer->getDomain() . "'>" . $offer->getDomain() . "</a>";
        }
        else{
            echo "<a href='https://" . $offer_old->getDomain() . "'>" . $offer_old->getDomain() . "</a>";
        }
        echo "</span>";
        echo "</div>";
        echo "<div class='detail-item'>";
        echo "<i class='fas fa-map-marker-alt'></i>";
        echo "<span>";
        if ($offer->getAddress() != $offer_old->getAddress()){
            echo "<a class='diff-old' href='https://maps.google.com/?q=" . $offer_old->getAddress() . "'>" . $offer_old->getAddress() . "</a>";
            echo "<a class='diff-new' href='https://maps.google.com/?q=" . $offer->getAddress() . "'>" . $offer->getAddress() . "</a>";
        }
        else{
            echo "<a href='https://maps.google.com/?q=" . $offer->getAddress() . "'>" . $offer->getAddress() . "</a>";
        }
        echo "</span>";
        echo "</div>";
        echo "<div class='detail-item'>";
        echo "<i class='fas fa-calendar'></i>";
        if ($offer->getBeginDate() != $offer_old->getBeginDate()){
            echo "<span class='diff-old'>" . $offer_old->getBeginDate() . "</span>";
            echo "<span class='diff-new'>" . $offer->getBeginDate() . "</span>";
        }
        else{
            echo "<span>" . $offer->getBeginDate() . "</span>";
        }
        echo "</div>";
        echo "<div class='detail-item'>";
        echo "<i class='fas fa-graduation-cap'></i>";
        if ($offer->getStudyLevel() != $offer_old->getStudyLevel()){
            echo "<span class='diff-old'>" . $offer_old->getStudyLevel() . "</span>";
            echo "<span class='diff-new'>" . $offer->getStudyLevel() . "</span>";
        }
        else{
            echo "<span>" . $offer_old->getStudyLevel() . "</span>";
        }
        echo "</div>";
        echo "</div>";
        echo "<div class='separator'></div>";
        echo "<div class='offer-description'>";
        echo "<h3>Description de l'offre</h3>";
        if ($offer->getDescription() != $offer_old->getDescription()){
            echo "<p class='diff-old'>" . $offer_old->getDescription() . "</p>";
            echo "<p class='diff-new'>" . $offer->getDescription() . "</p>";
        }
        else{
            echo $offer_old->getDescription();
        }
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='offer-card' style='margin-bottom: 10px'>";
        echo "<div class='offer-header'>";
        echo "<div class='offer-title'>";
        echo "<div>";
        echo "<h2>" . $offer->getTitle() . "</h2>";
        echo "<p class='offer-date'>" . "Publiée le " . $offer->getCreatedAt() . "</p>";
        echo "</div>";
        echo "<div class='apply-button-container'>";
        echo "<input type='hidden' name='id' value='" . $offer->getId() . "'>";
        echo "<button class='apply-button-edit' id='apply-button' onclick='openModalWithMessage()' style='display: none;'>Postuler</button>";
        echo "<form action='./company/edit.php' method='get' id='edit-form' style='display: none;'>";
        echo "<input type='hidden' name='id' value='" . $offer->getId() . "'>";
        if ($isAlreadyPending) {
            echo "<button class='apply-button-edit' id='edit-button'>Modification en attente de validation</button>";
        } else {
            echo "<button class='apply-button-edit' id='edit-button'>Modifier</button>";
        }
        echo "</form>";
        echo "<form action='../../presenter/offer/company/hide.php' method='post' id='hide-form' style='display: none;'>";
        echo "<input type='hidden' name='id' value='" . $offer->getId() . "'>";
        if ($offer->getIsActive()) {
            echo "<button class='apply-button-edit'>Cacher (Actif)</button>";
        } else {
            echo "<button class='apply-button-edit'>Cacher (Inactif)</button>";
        }
        echo "</form>";
        echo "<form action='../../presenter/offer/secretariat/deny.php' method='post' id='deny-form' style='display: none;'>";
        echo "<input type='hidden' name='id' value='" . $offer->getId() . "'>";
        echo "<button class='apply-button-edit'>Refuser</button>";
        echo "</form>";
        echo "<form action='../../presenter/offer/secretariat/validate.php' method='post' id='validate-form' style='display: none;'>";
        echo "<input type='hidden' name='id' value='" . $offer->getId() . "'>";
        echo "<button class='apply-button-edit'>Valider</button>";
        echo "</form>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "<div class='offer-content'>";
        echo "<h3 class='company-name'>";
        echo "<i class='fas fa-building'></i>";
        echo $offer->getCompany()->getName();
        echo "</h3>";
        echo "<div class='offer-details'>";
        echo "<div class='detail-item'>";
        echo "<i class='fas fa-clock'></i>";
        echo "<span>";
        echo $offer->getRealDuration();
        echo "</span>";
        echo "</div>";
        echo "<div class='detail-item'>";
        echo "<i class='fa-solid fa-phone'></i>";
        echo "<span>";
        echo "<a href='tel:" . $offer->getPhone() . "'>" . $offer->getPhone() . "</a>";
        echo "</span>";
        echo "</div>";
        echo "<div class='detail-item'>";
        echo "<i class='fa-solid fa-envelope'></i>";
        echo "<span>";
        echo "<a href='mailto:" . $offer->getEmail() . "'>" . $offer->getEmail() . "</a>";
        echo "</span>";
        echo "</div>";
        echo "<div class='detail-item'>";
        echo "<i class='fa-solid fa-link'></i>";
        echo "<span>";
        echo "<a href='https://" . $offer->getDomain() . "'>" . $offer->getDomain() . "</a>";
        echo "</span>";
        echo "</div>";
        echo "<div class='detail-item'>";
        echo "<i class='fas fa-map-marker-alt'></i>";
        echo "<span>";
        echo "<a href='https://maps.google.com/?q=" . $offer->getAddress() . "'>" . $offer->getAddress() . "</a>";
        echo "</span>";
        echo "</div>";
        echo "<div class='detail-item'>";
        echo "<i class='fas fa-calendar'></i>";
        echo "<span>" . $offer->getBeginDate() . "</span>";
        echo "</div>";
        echo "<div class='detail-item'>";
        echo "<i class='fas fa-graduation-cap'></i>";
        echo "<span>" . $offer->getStudyLevel() . "</span>";
        echo "</div>";
        echo "</div>";
        echo "<div class='separator'></div>";
        echo "<div class='offer-description'>";
        echo "<h3>Description de l'offre</h3>";
        echo $offer->getDescription();
        echo "</div>";
        echo "</div>";
        // add modal
        echo "<div id='applyModal' class='modal'>";
        echo "<div class='modal-content'>";
        echo "<span class='close' onclick='closeModal()'>&times;</span>";
        echo "<h2>Déposez votre candidature pour cette offre :</h2><br>";
        echo "<form action='/presenter/offer/apply.php' method='POST' enctype='multipart/form-data'>";
        echo "<label for='cv'>Déposez votre CV :</label>";
        echo "<input type='file' class='file-upload' id='cv' name='cv' accept='.pdf' required><br>";
        echo "<label for='motivation'>Déposez votre lettre de motivation :</label>";
        echo "<input type='file' class='file-upload' id='motivation' name='motivation' accept='.pdf' required>";
        echo "<p id='modal-message'></p>";
        echo "<input type='hidden' name='offre' value='" . $offer->getId() . "'>";
        echo "<button type='submit'>Valider la candidature</button>";
        echo "</form>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    } ?>
</main>
<?php include dirname(__FILE__) . '/../footer.php'; ?>
<script type="text/javascript">
    let offerHeader = document.querySelectorAll('.offer-header');
    //change for all offerHeader
    offerHeader.forEach(element => {
        element.style.backgroundImage = `url(<?php echo $offer->getImage(); ?>)`;
    });

    const companyId = <?php echo json_encode($company_id); ?>;
    const secretariat = <?php echo json_encode($groupeSecretariat); ?>;
    const type = <?php echo json_encode($type); ?>;

    const isAlreadyPending = <?php echo json_encode($isAlreadyPending); ?>;

    if (isAlreadyPending) {
        //Make edit form disabled
        document.getElementById('edit-button').disabled = true;
    }

    const applyButton = document.getElementById('apply-button');
    const editButton = document.getElementById('edit-form');
    const hideButton = document.getElementById('hide-form');
    const denyButton = document.getElementById('deny-form');
    const validateButton = document.getElementById('validate-form');


    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');

    if (!status === 'success') {
        if (type === 'updated' && secretariat) {
            console.log('updated secretariat');
            denyButton.style.display = 'block';
            validateButton.style.display = 'block';
        } else if (type === 'inactive' && secretariat) {
            hideButton.style.display = 'block';
        } else if (type === 'inactive' && companyId !== 0) {
            hideButton.style.display = 'block';
        } else if (type === 'new' && secretariat) {
            denyButton.style.display = 'block';
            validateButton.style.display = 'block';
        } else if (secretariat && type === 'all' || type == null) {
            editButton.style.display = 'block';
            hideButton.style.display = 'block';
        } else if (companyId !== 0) {
            console.log('Company');
            editButton.style.display = 'block';
            hideButton.style.display = 'block';
        } else  {
            applyButton.style.display = 'block';
        }
    } else {
        applyButton.style.display = 'block';
    }


    // Fonction pour ouvrir la fenêtre modale avec un message personnalisé
    function openModalWithMessage(message) {
        document.getElementById("applyModal").style.display = "block";
        document.getElementById("modal-message").textContent = message;
    }

    // Vérifiez si un paramètre 'status' est passé dans l'URL
    if (status === 'success') {
        openModalWithMessage("Votre candidature a bien été enregistrée !");
    } else if (status === 'already_applied') {
        openModalWithMessage("Vous avez déjà postulé pour cette offre.");
    }

    // Fonction pour fermer la fenêtre modale
    function closeModal() {
        document.getElementById("applyModal").style.display = "none";
    }

    // Fermer la fenêtre si l'utilisateur clique en dehors de la modale
    window.onclick = function (event) {
        var modal = document.getElementById("applyModal");
        if (event.target === modal) {
            modal.style.display = "none";
        }
    }
</script>


</body>
</html>