<?php
require dirname(__FILE__) . '/../../models/Offer.php';
require dirname(__FILE__) . '/../../models/Company.php';

//$offerId = $_GET['id'];
//if (!isset($offerId)) {
//    exit();
//}

//$offer = Offer::getById($offerId);
$offer = new Offer(1, 1,new Company(1, "e", 1, "e", "", "", ""), "developpeur web", "developpeur web pour faire un site de gestion d'offres de stage", "developpeur", 30, "30 septembre 2025",300, "Lille", "Bac+3","true", "27-09-2024", "27-09-2024");
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Détails de l'offre - Le Petit Stage</title>
        <link rel="stylesheet" href="/view/css/view.css">

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
                        <button class="apply-button">Postuler</button>
                    </div>
                </div>
                <div class="offer-content">
                    <h3 class="company-name">
                        <i class="fas fa-building"></i>
                        <?php echo ""; ?>
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
                            <span><?php echo $offer->getLocation();?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar"></i>
                            <span><?php echo "Debut : " . $offer->getBeginDate();?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-graduation-cap"></i>
                            <span><?php echo $offer->getStudyLevel();?></span>
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
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js" crossorigin="anonymous"></script>
    </body>
</html>