<?php
// File: Detail.php
// Show detail of an offer
session_start();

require dirname(__FILE__) . '/../../Model/PendingOffer.php';
require dirname(__FILE__) . '/../../Model/Company.php';
require dirname(__FILE__) . '/../../Presentation/Offer/Filter.php';

$returnUrl = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]";

error_reporting(E_ALL ^ E_DEPRECATED);
$offer_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING) ?? "all";

if (!$offer_id) {
    header("Location: " . $returnUrl);
    die();
}

$companyId = $_SESSION['company_id'] ?? 0;
if ($companyId != 0 && Offer::is_company_offer($offer_id, $companyId)) {
    //header("Location: ../Offer/List.php");
    //die();
    echo !Offer::is_company_offer($offer_id, $companyId);
}
$secretariatGroup = $_SESSION['secretariat'] ?? false;

switch ($type) {
    case 'updated':
        $offer = PendingOffer::get_by_offer_id($offer_id);
        $offer_old = Offer::get_by_id($offer->get_offer_id());
        break;
    case 'new':
        $offer = PendingOffer::get_by_offer_id($offer_id);
        break;
    default:
        $offer = Offer::get_by_id($offer_id);
        break;
}

if ($offer->get_supress() && !$secretariatGroup) {
    //Make a 403 error
    header("HTTP/1.1 403 Forbidden");
    die();
}

$isAlreadyPending = Offer::is_already_pending($offer_id);

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
function render_detail($label, $iconClass, $oldValue, $newValue, bool $isLink = false, string $linkPrefix = ''): void {
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
 * Renders a form with a hidden field for the Offer ID and a button with the given text.
 * The form's action is set to $action and its method is set to 'post'.
 * The hidden field is added to the form with the given name and value.
 * @param $action
 * @param $id
 * @param $buttonText
 * @param $typeForm
 * @param array $hiddenFields
 * @return void
 */
function render_form($action, $id, $buttonText, $typeForm, array $hiddenFields = []): void {
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
        <link rel="stylesheet" href="/View/css/Detail.css">
        <link rel="stylesheet" href="/View/css/Button.css">
        <link rel="stylesheet" href="/View/css/HeaderAlt.css">
        <link rel="stylesheet" href="/View/css/FooterAlt.css">
        <link rel="stylesheet" href="/View/css/Apply.css">
        <link rel="stylesheet" href="/View/css/Notification.css">
        <script src="/View/Js/Notification.js" crossorigin="anonymous"></script>
        <script src="https://kit.fontawesome.com/166cd842ba.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    </head>
    <body>
        <?php include '../../View/HeaderAlt.php'; ?>
        <main>
            <div class='offer-card' style='margin-bottom: 10px'>
                <div class='offer-header'>
                    <div class='offer-title'>
                        <div>
                            <?php
                                if ($type == 'updated') {
                                    if ($offer->get_title() != $offer_old->get_title()) {
                                        echo "<h2 class='diff-old'>" . $offer_old->get_title() . "</h2>";
                                        echo "<h2 class='diff-new'>" . $offer->get_title() . "</h2>";
                                    } else {
                                        echo "<h2>" . $offer->get_title() . "</h2>";
                                    }
                                } else {
                                    echo "<h2>" . $offer->get_title() . "</h2>";
                                }
                                $tags = $offer->get_tags();

                                foreach ($tags as $tag) {
                                    echo "<span class='Offer-badge'>" . $tag . "</span>";
                                }
                                echo "<p class='Offer-date'>" . "Publiée le " . $offer->get_created_at() . "</p>";
                            ?>
                        </div>
                        <div class='apply-button-container'>
                            <?php
                                echo "<div class='apply-button-container'>";
                                if ($type != 'updated' && $type != 'suppressed') {
                                    echo "<input type='hidden' name='id' value='" . $offer->get_id() . "'>";

                                    if (!$secretariatGroup && $companyId == 0) {
                                        echo "<button class='apply-button-edit' id='apply-button' onclick='openModal()'>Postuler</button>";
                                    }

                                    if (($companyId !== 0 || $secretariatGroup) && $type != 'new' ) {
                                        echo "<button class='apply-button-edit'><a href='./Company/Application.php?id=".$offer_id."'>Candidatures</a></button>";
                                        echo "<form action='Company/Edit.php' method='get' id='edit-form'>";
                                            echo "<input type='hidden' name='id' value='" . $offer->get_id() . "'>";
                                            echo "<button class='apply-button-edit' id='edit-button'". ($isAlreadyPending ? "disabled=true> Modification en attente" : ">Modification") . "</button>";
                                        echo "</form>";

                                        echo "<form action='/Presentation/Offer/Company/Hide.php' method='post' id='hide-form'>";
                                            echo "<input type='hidden' name='id' value='" . $offer->get_id() . "'>";
                                            echo "<button class='apply-button-edit'>Cacher " . ($offer->get_is_active() ? "(Actif)" : "(Inactif)") . "</button>";
                                        echo "</form>";
                                        echo "<form action='/Presentation/Offer/Delete.php' method='post' id='hide-form'>";
                                        echo "<input type='hidden' name='id' value='" . $offer->get_id() . "'>";
                                            echo "<button class='apply-button-edit'>Supprimer</button>";
                                        echo "</form>";
                                    }
                                }

                                if ($secretariatGroup && ($type == "new" || $type == "updated")) render_form('../../Presentation/Offer/Secretariat/Deny.php', $offer->get_id(), "Refuser", "deny-form", ['id' => $offer->get_id()]);
                                if ($secretariatGroup && ($type == "new" || $type == "updated")) render_form('../../Presentation/Offer/Secretariat/Validate.php', $offer->get_id(), "Valider", "validate-form", ['id' => $offer->get_id()], "validate-form");
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class='offer-content'>
                <h3 class='company-name'><i class='fas fa-building'></i>
                    <?php
                        if ($type == 'updated') {
                            echo $offer_old->get_company()->get_name();
                            echo "</h3>";
                            echo "<div class='Offer-details'>";
                            render_detail("Durée", "fas fa-clock", $offer_old->get_real_duration(), $offer->get_real_duration(), false);
                            render_detail("Téléphone", "fa-solid fa-phone", $offer_old->get_phone(), $offer->get_phone(), true, "tel:");
                            render_detail("Email", "fa-solid fa-envelope", $offer_old->get_email(), $offer->get_email(), true, "mailto:");
                            render_detail("Site web", "fa-solid fa-link", $offer_old->get_domain(), $offer->get_domain(), true, "https://");
                            render_detail("Adresse", "fas fa-map-marker-alt", $offer_old->get_address(), $offer->get_address(), true, "https://maps.google.com/?q=");
                            render_detail("Date de début", "fas fa-calendar", $offer_old->get_begin_date(), $offer->get_begin_date(), false);
                            render_detail("Niveau d'études", "fas fa-graduation-cap", $offer_old->get_study_level(), $offer->get_study_level(), false);
                        } else {
                            echo $offer->get_company()->get_name();
                            echo "</h3>";
                            echo "<div class='Offer-details'>";
                            render_detail("Durée", "fas fa-clock", $offer->get_real_duration(), $offer->get_real_duration(), false);
                            render_detail("Téléphone", "fa-solid fa-phone", $offer->get_phone(), $offer->get_phone(), true, "tel:");
                            render_detail("Email", "fa-solid fa-envelope", $offer->get_email(), $offer->get_email(), true, "mailto:");
                            render_detail("Site web", "fa-solid fa-link", $offer->get_domain(), $offer->get_domain(), true, "https://");
                            render_detail("Adresse", "fas fa-map-marker-alt", $offer->get_address(), $offer->get_address(), true, "https://maps.google.com/?q=");
                            render_detail("Date de début", "fas fa-calendar", $offer->get_begin_date(), $offer->get_begin_date(), false);
                            render_detail("Niveau d'études", "fas fa-graduation-cap", $offer->get_study_level(), $offer->get_study_level(), false);
                        }
                    ?>
                    </div>
                <div class='separator'></div>
                <div class='offer-description'>
                    <h3>Description de l'offre</h3>
                    <?php if ($type == 'updated') {
                        if ($offer->get_description() != $offer_old->get_description()) {
                            echo "<p class='diff-old'>" . $offer_old->get_description() . "</p>";
                            echo "<p class='diff-new'>" . $offer->get_description() . "</p>";
                        } else {
                            echo $offer_old->get_description();
                        }
                    } else {
                        echo $offer->get_description();
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
                                echo "<input type='hidden' name='offer' value='" . $offer->get_id() . "'>";
                                echo "<br>";
                                echo "<button type='submit'>Valider la candidature</button>";
                            echo "</form>";
                        echo "</div>";
                    echo "</div>";
                    echo "</div>";
                }
            ?>
        </main>
        <?php include '../../View/FooterAlt.php'; ?>
        <script type="text/javascript">
            document.querySelectorAll('.Offer-header').forEach(element => {
                element.style.backgroundImage = `url(<?php echo $offer->get_image(); ?>)`;
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
                    url: '/Presentation/Offer/Apply.php',
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