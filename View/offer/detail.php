<?php
session_start();

require dirname(__FILE__) . '/../../Model/PendingOffer.php';
require dirname(__FILE__) . '/../../Model/Company.php';
require dirname(__FILE__) . '/../../Presentation/offer/filter.php';


if (isset($_SERVER["HTTP_REFERER"])) {
    $returnUrl = $_SERVER["HTTP_REFERER"];
}

error_reporting(E_ALL ^ E_DEPRECATED);
$offerId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);
if (!$offerId) {
    header("Location: " . $returnUrl);
    die();
}

// Verification de qui est l'utilisateur

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


switch ($type) {
    case 'updated':
        $offer = PendingOffer::getByOfferId($offerId);
        $offer_old = Offer::getById($offer->getOfferId());
        break;
    case 'new':
        $offer = PendingOffer::getByOfferId($offerId);
        break;
    default:
        $offer = Offer::getById($offerId);
        break;

}

$isAlreadyPending = Offer::isAlreadyPending($offerId);

function renderDetail($label, $iconClass, $oldValue, $newValue, $isLink = false, $linkPrefix = ''): void {
    /*
    * Renders a detail item with a label, an icon, and two values.
    * If the values are different, it renders a link to the old value and a link to the new value.
    * If the values are the same, it renders the new value as a link if $isLink is true, or as a plain text otherwise.
    */
    echo "<div class='detail-item'>";
    echo "<span>";
    echo "<i class='$iconClass'> </i>";
    if ($oldValue != $newValue) {
        if ($isLink) {
            echo "<a class='diff-old' href='$linkPrefix$oldValue'> $oldValue</a>";
            echo "<a class='diff-new' href='$linkPrefix$newValue'> $newValue</a>";
        } else {
            echo "<p class='diff-old'> $oldValue</p>";
            echo "<p class='diff-new'> $newValue</p>";
        }
    } else {
        if ($isLink) {
            echo "<a href='$linkPrefix$newValue'> $newValue</a>";
        } else {
            echo $newValue;
        }
    }
    echo "</span>";
    echo "</div>";
}

function renderForm($action, $id, $buttonText, $typeForm, $hiddenFields = []): void {
    /*
     * Renders a form with a hidden field for the offer ID and a button with the given text.
     * The form's action is set to $action and its method is set to 'post'.
     * The hidden field is added to the form with the given name and value.
     */
    echo "<form action='$action' method='post'>";
    foreach ($hiddenFields as $name => $value) {
        echo "<input type='hidden' name='$name' value='$value'>";
    }
    echo "<button class='apply-button-edit' id='$typeForm'>$buttonText</button>";
    echo "</form>";
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de l'offre - Le Petit Stage</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/View/css/detail.css">
    <link rel="stylesheet" href="/View/css/button.css">
    <link rel="stylesheet" href="/View/css/header.css">
    <link rel="stylesheet" href="/View/css/footer.css">
    <link rel="stylesheet" href="/View/css/apply.css">
    <script src="https://kit.fontawesome.com/166cd842ba.js" crossorigin="anonymous"></script>
</head>
<body>
<?php include dirname(__FILE__) . '/../header.php'; ?>
<main>
    <div class='offer-card' style='margin-bottom: 10px'>
        <div class='offer-header'>
            <div class='offer-title'>
                <div>
                <?php if ($type == 'updated') {
                    if ($offer->getTitle() != $offer_old->getTitle()) {
                        echo "<h2 class='diff-old'>" . $offer_old->getTitle() . "</h2>";
                        echo "<h2 class='diff-new'>" . $offer->getTitle() . "</h2>";
                    } else {
                        echo "<h2>" . $offer->getTitle() . "</h2>";
                    }
                } else {
                    echo "<h2>" . $offer->getTitle() . "</h2>";
                }
                $tags = $offer->getTags();

                foreach ($tags as $tag) {
                    echo "<span class='offer-badge'>" . $tag . "</span>";
                }
                echo "<p class='offer-date'>" . "Publiée le " . $offer->getCreatedAt() . "</p>";
                ?>
                </div>
                <div class='apply-button-container'>
                    <?php if ($type != 'updated') {
                        echo "<div class='apply-button-container'>";
                            echo "<input type='hidden' name='id' value='" . $offer->getId() . "'>";
                            echo "<button class='apply-button-edit' id='apply-button' onclick='openModalWithMessage()'>Postuler</button>";
                            echo "<form action='../../View/offer/company/edit.php' method='get' id='edit-form'>";
                                echo "<input type='hidden' name='id' value='" . $offer->getId() . "'>";
                                if ($isAlreadyPending) {
                                    echo "<button class='apply-button-edit' id='edit-button'>Modification en attente de validation</button>";
                                } else {
                                    echo "<button class='apply-button-edit' id='edit-button'>Modifier</button>";
                                }
                            echo "</form>";
                            echo "<form action='../../Presentation/offer/company/hide.php' method='post' id='hide-form'>";
                                echo "<input type='hidden' name='id' value='" . $offer->getId() . "'>";
                                if ($offer->getIsActive()) {
                                    echo "<button class='apply-button-edit'>Cacher (Actif)</button>";
                                } else {
                                    echo "<button class='apply-button-edit'>Cacher (Inactif)</button>";
                                }
                            echo "</form>";
                    }
                    renderForm('../../Presentation/offer/secretariat/deny.php', $offer->getId(), "Refuser", "deny-form", ['id' => $offer->getId()]);
                    renderForm('../../Presentation/offer/secretariat/validate.php', $offer->getId(), "Valider", "validate-form", ['id' => $offer->getId()], "validate-form");
                    ?>
                    </div>
                </div>
            </div>
        </div>
        <div class='offer-content'>
            <h3 class='company-name'><i class='fas fa-building'></i><?php
                if ($type == 'updated') {
                    echo $offer_old->getCompany()->getName();
                    echo "</h3>";
                    echo "<div class='offer-details'>";
                    renderDetail("Durée", "fas fa-clock", $offer_old->getRealDuration(), $offer->getRealDuration(), false);
                    renderDetail("Téléphone", "fa-solid fa-phone", $offer_old->getPhone(), $offer->getPhone(), true, "tel:");
                    renderDetail("Email", "fa-solid fa-envelope", $offer_old->getEmail(), $offer->getEmail(), true, "mailto:");
                    renderDetail("Site web", "fa-solid fa-link", $offer_old->getDomain(), $offer->getDomain(), true, "https://");
                    renderDetail("Adresse", "fas fa-map-marker-alt", $offer_old->getAddress(), $offer->getAddress(), true, "https://maps.google.com/?q=");
                    renderDetail("Date de début", "fas fa-calendar", $offer_old->getBeginDate(), $offer->getBeginDate(), false);
                    renderDetail("Niveau d'études", "fas fa-graduation-cap", $offer_old->getStudyLevel(), $offer->getStudyLevel(), false);
                } else {
                    echo $offer->getCompany()->getName();
                    echo "</h3>";
                    echo "<div class='offer-details'>";
                    renderDetail("Durée", "fas fa-clock", $offer->getRealDuration(), $offer->getRealDuration(), false);
                    renderDetail("Téléphone", "fa-solid fa-phone", $offer->getPhone(), $offer->getPhone(), true, "tel:");
                    renderDetail("Email", "fa-solid fa-envelope", $offer->getEmail(), $offer->getEmail(), true, "mailto:");
                    renderDetail("Site web", "fa-solid fa-link", $offer->getDomain(), $offer->getDomain(), true, "https://");
                    renderDetail("Adresse", "fas fa-map-marker-alt", $offer->getAddress(), $offer->getAddress(), true, "https://maps.google.com/?q=");
                    renderDetail("Date de début", "fas fa-calendar", $offer->getBeginDate(), $offer->getBeginDate(), false);
                    renderDetail("Niveau d'études", "fas fa-graduation-cap", $offer->getStudyLevel(), $offer->getStudyLevel(), false);
                }
                ?>
                </div>
            <div class='separator'></div>
            <div class='offer-description'>
                <h3>Description de l'offre</h3>
                <?php if ($type == 'updated') {
                    if ($offer->getDescription() != $offer_old->getDescription()) {
                        echo "<p class='diff-old'>" . $offer_old->getDescription() . "</p>";
                        echo "<p class='diff-new'>" . $offer->getDescription() . "</p>";
                    } else {
                        echo $offer_old->getDescription();
                    }
                } else {
                    echo $offer->getDescription();
                }
                ?>
            </div>
    <?php if ($type != 'updated') {
            // add modal
            echo "<div id='applyModal' class='modal'>";
                echo "<div class='modal-content'>";
                    echo "<span class='close' onclick='closeModal()'>&times;</span>";
                    echo "<h2>Déposez votre candidature pour cette offre :</h2><br>";
                    echo "<form action='/Presentation/offer/apply.php' method='POST' enctype='multipart/form-data'>";
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
    document.querySelectorAll('.offer-header').forEach(element => {
        element.style.backgroundImage = `url(<?php echo $offer->getImage(); ?>)`;
    });

    //Get variables from php
    const companyId = <?php echo json_encode($company_id); ?>;
    const secretariat = <?php echo json_encode($groupeSecretariat); ?>;
    const type = <?php echo json_encode($type); ?>;
    const isAlreadyPending = <?php echo json_encode($isAlreadyPending); ?>;


    if (isAlreadyPending) {
        //Make edit form disabled
        document.getElementById('edit-button').disabled = true;
    }

    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');


    //Toggle visibility of elements with the given ID
    function toggleVisibility(elementId, show) {
        const element = document.getElementById(elementId);
        if (element) {
            element.classList.toggle('hidden', !show);
        }
    }

    //Default visibility
    toggleVisibility('apply-button', false);
    toggleVisibility('edit-form', false);
    toggleVisibility('hide-form', false);
    toggleVisibility('deny-form', false);
    toggleVisibility('validate-form', false);


    //Show or hide elements based on the status of the offer, the type of the offer, and the user's role
    if (status !== 'success') {
        if (type === 'updated' && secretariat) {
            toggleVisibility('deny-form', true);
            toggleVisibility('validate-form', true);
        } else if (type === 'inactive' && secretariat) {
            toggleVisibility('hide-form', true);
        } else if (type === 'inactive' && companyId !== 0) {
            toggleVisibility('edit-form', true);
            toggleVisibility('hide-form', true);
        } else if (type === 'new' && secretariat) {
            toggleVisibility('deny-form', true);
            toggleVisibility('validate-form', true);
        } else if (secretariat && type === 'all' || type == null) {
            toggleVisibility('edit-form', true);
            toggleVisibility('hide-form', true);
        } else if (companyId !== 0) {
            toggleVisibility('edit-form', true);
            toggleVisibility('hide-form', true);
        } else {
            toggleVisibility('apply-button', true);
        }
    } else {
        toggleVisibility('apply-button', true);
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