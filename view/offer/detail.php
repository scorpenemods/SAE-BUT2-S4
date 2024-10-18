<?php
session_start();
$_SESSION['user'] = 1; //pour tester pour l'instant


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

$offer = Offer::getById($offerId);
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
        <link rel="stylesheet" href="/view/css/apply.css">

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
                        </div class="apply-button">

                        <!-- bouton pour postuler -->
                        <button class="apply-button" onclick="openModalWithMessage()">Postuler</button>

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

            <!-- fenêtre modal pour valider candidature -->
            <div id="applyModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h2>Déposez votre candidature pour cette offre :</h2><br>
                    <form action="/presenter/offer/apply.php" method="POST" enctype="multipart/form-data">
                        <label for="cv">Déposez votre CV :</label>
                        <input type="file" id="cv" name="cv" accept=".pdf" required><br>

                        <label for="motivation">Déposez votre lettre de motivation :</label>
                        <input type="file" id="motivation" name="motivation" accept=".pdf" required>

                        <p id="modal-message"></p> <!-- Zone pour afficher le message personnalisé -->

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
        </script>

        <script type="text/javascript">
            // Fonction pour ouvrir la fenêtre modale avec un message personnalisé
            function openModalWithMessage(message) {
                document.getElementById("applyModal").style.display = "block";
                document.getElementById("modal-message").textContent = message;
            }

            // Vérifiez si un paramètre 'status' est passé dans l'URL
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');

            if (status === 'success') {
                openModalWithMessage("Votre candidature a bien été enregistrée !" );
            } else if (status === 'already_applied') {
                openModalWithMessage("Vous avez déjà postulé pour cette offre." );
            }

            // Fonction pour fermer la fenêtre modale
            function closeModal() {
                document.getElementById("applyModal").style.display = "none";
            }

            // Fermer la fenêtre si l'utilisateur clique en dehors de la modale
            window.onclick = function(event) {
                var modal = document.getElementById("applyModal");
                if (event.target === modal) {
                    modal.style.display = "none";
                }
            }
        </script>


    </body>
</html>