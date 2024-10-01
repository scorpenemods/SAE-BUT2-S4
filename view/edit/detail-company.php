<?php
session_start();

require dirname(__FILE__) . '/../../models/Offer.php';
require dirname(__FILE__) . '/../../models/Company.php';

$returnUrl = "/view/offer/list-company.php";
if (isset($_SERVER["HTTP_REFERER"])) {
    $returnUrl = $_SERVER["HTTP_REFERER"];
}

$offerId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($offerId == null) {
    header("Location: " . $returnUrl);
    die();
}

// Check if user is logged in and has a company
$company_id = 1;
//if ($_SESSION['company_id']) {
//    $company_id = $_SESSION['company_id'];
//} else {
//    header("Location: ../offer/list-company.php");
//    die();
//}

$offer = Offer::getById($offerId);

if ($offer->getCompany()->getId() != $company_id) {
    header("Location: ../offer/list-company.php");
    die();
}
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Détails de l'offre - Le Petit Stage</title>

        <link rel="stylesheet" href="/view/css/detail.css">
        <link rel="stylesheet" href="/view/css/header.css">
        <link rel="stylesheet" href="/view/css/footer.css">
        <link rel="stylesheet" href="/view/css/detail-company.css">
        <script src="https://kit.fontawesome.com/your-font-awesome-kit.js" crossorigin="anonymous"></script>
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
                            <form action="../edit/edit-company.php" method="get">
                                <input type="hidden" name="id" value="<?php echo $offer->getId(); ?>">
                                <button class="apply-button-edit" onclick="">Modifier</button>
                            </form>
                            <form action="../../presenter/edit/cacher.php" method="post">
                                <input type="hidden" name="id" value="<?php echo $offer->getId(); ?>">
                                <button class="apply-button-edit">Cacher <?php echo $offer->getIsActive() ? "(Actif)" : "(Inactif)"; ?></button>
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
                            <i class="fas fa-map-marker-alt"></i>
                            <span>
                                <?php echo $offer->getAddress(); ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar"></i>
                            <span><?php echo "Début: " . $offer->getBeginDate(); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-graduation-cap"></i>
                            <span><?php echo "Diplôme requis: " . $offer->getStudyLevel(); ?></span>
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
            offerHeader.style.backgroundImage = `url(<?php echo $offer->getMedias()[0]->getUrl(); ?>)`;
        </script>
    </body>
</html>