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
    <div class="offer-card">
        <div class="offer-header">
            <div class="offer-title">
                <div>
                    <?php echo "<h2>" . $offer->getTitle() . $type . "</h2>"; ?>
                    <?php
                    $tags = $offer->getTags();

                    foreach ($tags as $tag) {
                        echo "<span class='offer-badge'>" . $tag . "</span>";
                    }
                    ?>
                    <br>
                    <p class="offer-date"><?php echo "Publiée le " . $offer->getCreatedAt(); ?></p>
                </div>
                <!-- bouton pour postuler -->
                <div class="apply-button-container">
                    <input type="hidden" name="id" value="<?php echo $offer->getId(); ?>">
                    <button class="apply-button-edit" id="apply-button" onclick="openModalWithMessage()">Postuler</button>
                    <form action="./company/edit.php" method="get" id="edit-form" style="display: none;">
                        <input type="hidden" name="id" value="<?php echo $offer->getId(); ?>">
                        <button class="apply-button-edit" id="edit-button"><?php echo $isAlreadyPending ?  "Modification en attente de validation": "Modifier" ?></button>
                    </form>
                    <form action="../../presenter/offer/company/hide.php" method="post" id="hide-form"
                          style="display: none;">
                        <input type="hidden" name="id" value="<?php echo $offer->getId(); ?>">
                        <button class="apply-button-edit">
                            Cacher <?php echo $offer->getIsActive() ? "(Actif)" : "(Inactif)"; ?></button>
                    </form>
                    <form action="../../presenter/offer/secretariat/deny.php" method="get" id="deny-form"
                          style="display: none;">
                        <input type="hidden" name="id" value="<?php echo $offer->getId(); ?>">
                        <button class="apply-button-edit">Refuser</button>
                    </form>
                    <form action="../../presenter/offer/secretariat/validate.php" method="post" id="validate-form"
                          style="display: none;">
                        <input type="hidden" name="id" value="<?php echo $offer->getId(); ?>">
                        <button class="apply-button-edit">Valider</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="offer-content">
            <h3 class="company-name">
                <i class="fas fa-building"></i>
                <?php echo $offer->getCompany()->getName(); ?>
            </h3>
            <div class="offer-details">
                <div class="detail-item">
                    <i class="fas fa-clock"></i>
                    <span>
                        <?php echo $offer->getRealDuration(); ?>
                    </span>
                </div>
                <div class="detail-item">
                    <i class="fa-solid fa-phone"></i>
                    <span>
                        <a href="tel:<?php echo $offer->getPhone(); ?>"><?php echo $offer->getPhone(); ?></a>
                    </span>
                </div>
                <div class="detail-item">
                    <i class="fa-solid fa-envelope"></i>
                    <span>
                        <a href="mailto:<?php echo $offer->getEmail(); ?>"><?php echo $offer->getEmail(); ?></a>
                    </span>
                </div>
                <div class="detail-item">
                    <i class="fa-solid fa-link"></i>
                    <span>
                        <a target="_blank" rel="noopener noreferrer" href="https://<?php echo $offer->getDomain(); ?>"><?php echo $offer->getDomain(); ?></a>
                    </span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>
                        <a target="_blank" rel="noopener noreferrer" href="https://maps.google.com/?q=<?php echo $offer->getAddress(); ?>"><?php echo $offer->getAddress(); ?></a>
                    </span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-calendar"></i>
                    <span><?php echo $offer->getBeginDate(); ?></span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-graduation-cap"></i>
                    <span><?php echo $offer->getStudyLevel(); ?></span>
                </div>
            </div>
            <div class="separator"></div>
            <div class="offer-description">
                <h3>Description de l'offre</h3>
                <?php echo $offer->getDescription(); ?>
            </div>
        </div>
    </div>

    <div id="applyModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Déposez votre candidature pour cette offre :</h2><br>
            <form action="/presenter/offer/apply.php" method="POST" enctype="multipart/form-data">
                <label for="cv">Déposez votre CV :</label>
                <input type="file" class="file-upload" id="cv" name="cv" accept=".pdf" required><br>

                <label for="motivation">Déposez votre lettre de motivation :</label>
                <input type="file" class="file-upload" id="motivation" name="motivation" accept=".pdf" required>

                <p id="modal-message"></p>

                <input type="hidden" name="offre" value="<?php echo $offer->getId(); ?>">
                <button type="submit">Valider la candidature</button>
            </form>
        </div>
    </div>


</main>
<?php include dirname(__FILE__) . '/../footer.php'; ?>
<script type="text/javascript">
    let offerHeader = document.querySelector('.offer-header');
    offerHeader.style.backgroundImage = `url(<?php echo $offer->getImage(); ?>)`;

    const companyId = <?php echo json_encode($company_id); ?>;
    const secretariat = <?php echo json_encode($groupeSecretariat); ?>;
    const type = <?php echo json_encode($type); ?>;

    console.log(type);
    console.log(secretariat);
    console.log(companyId);
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


    if (type === 'inactive' && secretariat) {
        applyButton.style.display = 'none';
        editButton.style.display = 'none';
        hideButton.style.display = 'block';
        denyButton.style.display = 'none';
        validateButton.style.display = 'none';
    } else if (type === 'inactive' && companyId !== 0) {
        console.log('Company + inactive');
        applyButton.style.display = 'none';
        editButton.style.display = 'none';
        hideButton.style.display = 'block';
        denyButton.style.display = 'none';
        validateButton.style.display = 'none';
    } else if ((type === 'new' || type === 'updated') && secretariat) {
        applyButton.style.display = 'none';
        editButton.style.display = 'none';
        hideButton.style.display = 'none';
        denyButton.style.display = 'block';
        validateButton.style.display = 'block';
    } else if (secretariat && type === 'all' || type == null) {
        applyButton.style.display = 'none';
        denyButton.style.display = 'none';
        editButton.style.display = 'block';
        hideButton.style.display = 'block';
        validateButton.style.display = 'none';
    } else if (companyId !== 0) {
        applyButton.style.display = 'none';
        editButton.style.display = 'block';
        hideButton.style.display = 'block';
    }

    // Fonction pour ouvrir la fenêtre modale avec un message personnalisé
    function openModalWithMessage(message) {
        document.getElementById("applyModal").style.display = "block";
        document.getElementById("modal-message").textContent = message;
    }

    // Vérifiez si un paramètre 'status' est passé dans l'URL
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');

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