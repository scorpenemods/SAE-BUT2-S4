<?php
include dirname(__FILE__) . '/../header.php';
require dirname(__FILE__) . '/../../models/Offer.php';

// get id from URL query string
//$offerId = $_GET['id'];
//if (!isset($offerId)) {
//    exit();
//}

$offer = new Offer(1, "developpeur web", "developpeur web pour faire un site de gestion d'offres de stage", "developpeur", 30, 300, true, "27-09-2024", "27-09-2024");


?>



<!DOCTYPE html>
<html lang="fr">
<link>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de l'offre - Le Petit Stage</title>
    <link rel="stylesheet" href="/view/css/view.css">

<style>

    </style>
</head>
<body>


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
                <?php echo "le nom de la compagnie à mettre"; ?>
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
                    <span>A FAIRE</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-calendar"></i>
                    <span><?php echo "Debut : ";?></span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-graduation-cap"></i>
                    <span><?php echo "diplome requis"; ?></span>
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

<!-- Font Awesome for icons -->
<script src="https://kit.fontawesome.com/your-font-awesome-kit.js" crossorigin="anonymous"></script>
</body>
</html>