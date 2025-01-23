<?php
session_start();
error_reporting(E_ALL ^ E_DEPRECATED);

require dirname(__FILE__) . '/../../models/PendingOffer.php';
require dirname(__FILE__) . '/../../models/Company.php';
require dirname(__FILE__) . '/../../presenter/offer/filter.php';

// Parameters validation
$offerId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING) ?? "all";
if (!$offerId) {
    $returnUrl = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]/view/offer/list.php";
    header("Location: " . $returnUrl);
    die();
}

// Verification of the user
$company_id = $_SESSION['company_id'] ?? 0;
if ($company_id != 0 && Offer::isCompanyOffer($offerId, $company_id)) {
    header("Location: ../offer/list.php");
    die();
}
$secretariat_group = $_SESSION['secretariat'] ?? false;

// Get offers for the right type
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

// Check if an offer is "supressed"
if ($offer->getSupress() && !$secretariat_group) {
    //Make a 403 error
    header("HTTP/1.1 403 Forbidden");
    die();
}

$isAlreadyPending = Offer::isAlreadyPending($offerId);

/**
 * renderDetail
 * Renders a detail item with a label, an icon, and two values.
 * If the values are different, it renders a link to the old value and a link to the new value.
 * If the values are the same, it renders the new value as a link if $isLink is true, or as a plain text otherwise.
 * @param $label
 * @param $iconClass
 * @param $oldValue
 * @param $newValue
 * @param bool $isLink
 * @param string $linkPrefix
 * @return void
 */
function renderDetail($label, $iconClass, $oldValue, $newValue, bool $isLink = false, string $linkPrefix = ''): void {
    echo "<div class='detail-item' label='$label'>";
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

/**
 * renderForm
 * Renders a form with a hidden field for the offer ID and a button with the given text.
 * The form's action is set to $action and its method is set to 'post'.
 * The hidden field is added to the form with the given name and value.
 * @param $action
 * @param $id
 * @param $buttonText
 * @param $typeForm
 * @param array $hiddenFields
 * @return void
 */
function renderForm($action, $id, $buttonText, $typeForm, array $hiddenFields = []): void {
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
        <link rel="stylesheet" href="/view/css/detail.css">
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
            <div class='offer-card' style='margin-bottom: 10px'>
                <div class='offer-header'>
                    <div class='offer-title'>
                        <div>
                            <?php
                                if ($type == 'updated') {
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
                            <?php
                                if ($type != 'updated') {
                                    echo "<div class='apply-button-container'>";
                                    echo "<input type='hidden' name='id' value='" . $offer->getId() . "'>";

                                    if (!$secretariat_group && $company_id == 0) {
                                        echo "<button class='apply-button-edit' id='apply-button' onclick='openModal()'>Postuler</button>";
                                    }

                                    if ($company_id !== 0 || $secretariat_group) {
                                        echo "<button class='apply-button-edit'><a href='./company/applications.php?id=".$offerId."'>Candidatures</a></button>";
                                        echo "<form action='./company/edit.php' method='get' id='edit-form'>";
                                            echo "<input type='hidden' name='id' value='" . $offer->getId() . "'>";
                                            echo "<button class='apply-button-edit' id='edit-button'". ($isAlreadyPending ? "disabled=true> Modification en attente" : ">Modification") . "</button>";
                                        echo "</form>";

                                        echo "<form action='../../presenter/offer/company/hide.php' method='post' id='hide-form'>";
                                            echo "<input type='hidden' name='id' value='" . $offer->getId() . "'>";
                                            echo "<button class='apply-button-edit'>Cacher " . ($offer->getIsActive() ? "(Actif)" : "(Inactif)") . "</button>";
                                        echo "</form>";
                                    }
                                }

                                if ($secretariat_group && ($type == "new" || $type == "updated")) renderForm('../../presenter/offer/secretariat/deny.php', $offer->getId(), "Refuser", "deny-form", ['id' => $offer->getId()]);
                                if ($secretariat_group && ($type == "new" || $type == "updated")) renderForm('../../presenter/offer/secretariat/validate.php', $offer->getId(), "Valider", "validate-form", ['id' => $offer->getId()], "validate-form");
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class='offer-content'>
                <h3 class='company-name'><i class='fas fa-building'></i>
                    <?php
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
            <?php
                if ($type != 'updated') {
                    echo "<div id='applyModal' class='modal'>";
                        echo "<div class='modal-content'>";
                            echo "<span class='close' onclick='closeModal()'>&times;</span>";
                            echo "<h2>Déposez votre candidature pour cette offre :</h2><br>";
                            echo "<form id='apply-form'>";
                                echo "<label for='cv'>Déposez votre CV :</label>";
                                echo "<input type='file' class='file-upload' id='cv' name='cv' accept='.pdf' required><br>";
                                echo "<label for='motivation'>Déposez votre lettre de motivation :</label>";
                                echo "<input type='file' class='file-upload' id='motivation' name='motivation' accept='.pdf' required>";
                                echo "<input type='hidden' name='offre' value='" . $offer->getId() . "'>";
                                echo "<br>";
                                echo "<button type='submit'>Valider la candidature</button>";
                            echo "</form>";
                        echo "</div>";
                    echo "</div>";
                    echo "</div>";
                }
            ?>
        </main>
        <?php include dirname(__FILE__) . '/../footer.php'; ?>
        <script type="text/javascript">
            document.querySelectorAll('.offer-header').forEach(element => {
                element.style.backgroundImage = `url(<?php echo $offer->getImage(); ?>)`;
            });

            function openModal() {
                document.getElementById("applyModal").style.display = "block";
            }

            function closeModal() {
                document.getElementById("applyModal").style.display = "none";
            }

            const form = document.getElementById('apply-form');
            form.addEventListener('submit', (event) => {
                event.preventDefault();

                const formData = new FormData(form);
                $.ajax({
                    url: '/presenter/offer/apply.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        const result = JSON.parse(response);
                        if (result.status === "success") {
                            sendNotification("success", "Succès", "Votre candidature a bien été enregistrée !");
                            closeModal();
                        } else if (result.status === "already_applied") {
                            sendNotification("warning", "Attention", "Vous avez déjà postulé pour cette offre !");
                        } else {
                            sendNotification("failure", "Erreur", result.message || "Une erreur est survenue.");
                        }
                    },
                    error: function(xhr, status, error) {
                        sendNotification("failure", "Erreur", "Une erreur réseau est survenue lors de la candidature.");
                    }
                });
            });

            window.onclick = function (event) {
                let modal = document.getElementById("applyModal");

                if (event.target === modal) {
                    modal.style.display = "none";
                }
            }
        </script>
    </body>
</html>