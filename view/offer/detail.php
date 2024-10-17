<?php
session_start();

require dirname(__FILE__) . '/../../models/Offer.php';
require dirname(__FILE__) . '/../../models/Company.php';
require dirname(__FILE__) . '/../../models/Media.php';

$returnUrl = "/view/offer/list.php";
if (isset($_SERVER["HTTP_REFERER"])) {
    $returnUrl = $_SERVER["HTTP_REFERER"];
}

$offerId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($offerId == null) {
    header("Location: " . $returnUrl);
    die();
}

$company_id = 0;
if (isset($_SESSION['company_id'])) {
    $company_id = $_SESSION['company_id'];
    if (!Offer::isCompanyOffer($offerId, $company_id)) {
        header("Location: ../offer/list.php");
        die();
    }
}



$groupeSecretariat = false;
if (isset($_SESSION['secretariat'])) {
    $groupeSecretariat = $_SESSION['secretariat'];
}

$offer = Offer::getById($offerId);
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Détails de l'offre - Le Petit Stage</title>

        <link rel="stylesheet" href="/view/css/detail.css">
        <link rel="stylesheet" href="/view/css/button.css">
        <link rel="stylesheet" href="/view/css/header.css">
        <link rel="stylesheet" href="/view/css/footer.css">
        <script src="https://kit.fontawesome.com/166cd842ba.js" crossorigin="anonymous"></script>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    </head>
    <body>
        <?php include dirname(__FILE__) . '/../header.php'; ?>
        <main>
            <div class="offer-card">
                <div class="offer-header">
                    <div class="offer-title">
                        <div>
                            <span class="offer-badge">Stage</span>
                            <?php echo "<h2>" . $offer->getTitle() . "</h2>"; ?>
                            <p class="offer-date"><?php echo "Publiée le " . $offer->getCreatedAt(); ?></p>
                        </div>

                        <div class="apply-button-container">
                            <form action="" method="get" id="apply-form" style="display: block;">
                                <input type="hidden" name="id" value="<?php echo $offer->getId(); ?>">
                                <button class="apply-button-edit">Postuler</button>
                            </form>
                            <form action="./company/edit.php" method="get" id ="edit-form" style="display: none;">
                                <input type="hidden" name="id" value="<?php echo $offer->getId(); ?>">
                                <button class="apply-button-edit">Modifier</button>
                            </form>
                            <form action="../../presenter/offer/company/hide.php" method="post" id="hide-form" style="display: none;">
                                <input type="hidden" name="id" value="<?php echo $offer->getId(); ?>">
                                <button class="apply-button-edit">Cacher <?php echo $offer->getIsActive() ? "(Actif)" : "(Inactif)"; ?></button>
                            </form>
                            <form action="../../presenter/offer/secretariat/deny.php" method="get" id="deny-form" style="display: none;">
                                <input type="hidden" name="id" value="<?php echo $offer->getId(); ?>">
                                <button class="apply-button-edit" >Refuser</button>
                            </form>
                            <form action="../../presenter/offer/secretariat/validate.php" method="post" id="validate-form" style="display: none;">
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
                            <i class="fas fa-map-marker-alt"></i>
                            <span>
                                <a href="https://maps.google.com/?q=<?php echo $offer->getAddress(); ?>"><?php echo $offer->getAddress(); ?></a>
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
        </main>
        <?php include dirname(__FILE__) . '/../footer.php'; ?>
        <script type="text/javascript">
            let offerHeader = document.querySelector('.offer-header');
            offerHeader.style.backgroundImage = `url(<?php echo $offer->getImage(); ?>)`;

            const companyId = <?php echo json_encode($company_id); ?>;
            const secretariat = <?php echo json_encode($groupeSecretariat); ?>;

            if (companyId !== 0) {
                document.getElementById('apply-form').style.display = 'none';
                document.getElementById('hide-form').style.display = 'block';
                document.getElementById('edit-form').style.display = 'block';
            } else if (secretariat) {
                document.getElementById('apply-form').style.display = 'none';
                document.getElementById('edit-form').style.display = 'none';
                document.getElementById('hide-form').style.display = 'block';
                document.getElementById('deny-form').style.display = 'block';
                document.getElementById('validate-form').style.display = 'block';
            }
        </script>
    </body>
</html>