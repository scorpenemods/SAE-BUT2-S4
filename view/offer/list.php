<?php
session_start();

// Verification de qui est l'utilisateur
$company_id = 0;
if (isset($_SESSION['company_id'])) {
    $company_id = $_SESSION['company_id'];
}

$groupeSecretariat = false;
if (isset($_SESSION['secretariat'])) {
    $groupeSecretariat = $_SESSION['secretariat'];
}

require dirname(__FILE__) . '/../../models/Company.php';
require dirname(__FILE__) . '/../../models/PendingOffer.php';
require dirname(__FILE__) . '/../../models/Media.php';
require dirname(__FILE__) . '/../../presenter/offer/filter.php';

require dirname(__FILE__) . '/../../presenter/utils.php';

$pageId = filter_input(INPUT_GET, 'pageId', FILTER_VALIDATE_INT);
if ($pageId == null) {
    $pageId = 1;
}

$curURL = $_SERVER["REQUEST_URI"];

function setPageId($url, $newPageId): string {
    $parsedUrl = parse_url($url);

    parse_str($parsedUrl['query'] ?? '', $queryParams);

    $queryParams['pageId'] = $newPageId;

    $newQueryString = http_build_query($queryParams);

    return (isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '')
            . ($parsedUrl['host'] ?? '')
            . ($parsedUrl['path'] ?? '')
            . (!empty($newQueryString) ? '?' . $newQueryString : '');
}

/*
 * Filters
 * Get and Sanitize filters from the request
 */
error_reporting(E_ALL ^ E_DEPRECATED);
$filters = array();

$title = filter_input(INPUT_GET, 'title', FILTER_SANITIZE_STRING);
$sort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING);
$startDate = filter_input(INPUT_GET, 'startDate', FILTER_SANITIZE_STRING);
$diploma = filter_input(INPUT_GET, 'diploma', FILTER_SANITIZE_STRING);
$minSalary = filter_input(INPUT_GET, 'minSalary', FILTER_SANITIZE_STRING);
$maxSalary = filter_input(INPUT_GET, 'maxSalary', FILTER_SANITIZE_STRING);
$city = filter_input(INPUT_GET, 'city', FILTER_SANITIZE_STRING);
$duration = filter_input(INPUT_GET, 'duration', FILTER_VALIDATE_INT);
$sector = filter_input(INPUT_GET, 'sector', FILTER_SANITIZE_STRING);
$keywords = filter_input(INPUT_GET, 'keywords', FILTER_SANITIZE_STRING);
$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);

if (isset($title)) { $filters["title"] = $title; }
if (isset($sort)) { $filters["sort"] = $sort; }
if (isset($startDate)) { $filters["startDate"] = $startDate; }
if (isset($diploma)) { $filters["diploma"] = $diploma; }
if (isset($minSalary)) { $filters["minSalary"] = $minSalary; }
if (isset($maxSalary)) { $filters["maxSalary"] = $maxSalary; }
if (isset($city)) { $filters["city"] = $city; }
if (isset($duration)) { $filters["duration"] = $duration; }
if (isset($sector)) { $filters["sector"] = $sector; }
if (isset($keywords)) { $filters["keywords"] = $keywords; }
if (isset($type) && $groupeSecretariat) { $filters["type"] = $type; }
if ($company_id != 0) { $filters["company_id"] = $company_id; }

$filteredOffers = getPageOffers($pageId, $filters);
$offers = $filteredOffers["offers"] ?? array();
$totalPages = $filteredOffers["totalPages"] ?? 1;
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Le Petit Stage - Offres">
        <title>Le Petit Stage - Advanced</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="/view/css/list.css">
        <link rel="stylesheet" href="/view/css/header.css">
        <link rel="stylesheet" href="/view/css/footer.css">
        <link rel="stylesheet" href="/view/css/list.css">
        <script src="https://kit.fontawesome.com/166cd842ba.js" crossorigin="anonymous"></script>
    </head>
    <body>
        <?php include dirname(__FILE__) . '/../header.php'; ?>
        <div class="blur-overlay" id="blurOverlay"></div>
        <main>
            <form method="GET">
                <div class="search-filter">
                    <div class="search-bar">
                        <input name="title" type="text" placeholder="Rechercher une offre" aria-label="Rechercher une offre">
                        <button type="button" id="openFilter" aria-label="Ouvrir les filtres">
                            <i class="fas fa-filter fa-xl"></i>    
                        </button>
                        <button type="button" id="createNotification" aria-label="Créer une demande de notification">
                            <i class="fas fa-bell fa-xl"></i>
                        </button>
                        <button type="submit" id="search" aria-label="Rechercher">
                            <i class="fas fa-search fa-xl"></i>
                        </button>
                    </div>
                </div>
            </form>
            <div class="pagination" id="type_show" style="text-align: center">
                <a href="/view/offer/list.php?type=all" class="prev-page">All Offer</i></a>
                <a href="/view/offer/list.php?type=new" class="next-page">New Offer</i></a>
                <a href="/view/offer/list.php?type=updated" class="last-page">Updated Offer</i></a>
            </div>
            <div class="company-listings">
                <?php
                foreach ($offers as $offer) {
                    echo "<a class='company-link' href='/view/offer/detail.php?id=" . $offer->getId() . "'>";
                        echo "<div class='company-card'>";
                            echo "<div class='company-header'>";
                                echo false ? '<i class="fa-regular fa-heart"></i>' : '<i class="fa-solid fa-heart"></i>';
                                echo "<img src='".$offer->getImage()."' alt='Logo de " . $offer->getCompany()->getName() . "'>";
                                echo "<h3 class='title'>". $offer->getTitle() ."</h3>";
                                echo "<span class='company'><i class='fas fa-building'></i> " . $offer->getCompany()->getName() . "</span>";
                            echo "</div>";
                            echo "<div class='company-info'>";
                                echo "<p>" . truncateUTF8($offer->getDescription(), 100) . "</p>";
                                echo "<div class='company-meta'>";
                                    echo "<span><i class='fas fa-clock'></i> " . $offer->getRealDuration() . "</span>";
                                    echo "<span><i class='fas fa-graduation-cap'></i> " . $offer->getStudyLevel() . "</span>";
                                echo "</div>";
                            echo "</div>";
                        echo "</div>";
                    echo "</a>";
                }
                ?>
            </div>
            <div class="pagination">
                <a href="<?php echo setPageId($curURL, 1); ?>" class="first-page"><i class="fas fa-angle-double-left"></i></a>
                <a href="<?php echo setPageId($curURL, $pageId > 1 ? $pageId - 1 : $pageId); ?>" class="prev-page"><i class="fas fa-angle-left"></i></a>
                <a disabled="true" href="#"><?php echo $pageId; ?> / <?php echo $totalPages; ?></a>
                <a href="<?php echo setPageId($curURL, $pageId < $totalPages ? $pageId + 1 : $pageId); ?>" class="next-page"><i class="fas fa-angle-right"></i></a>
                <a href="<?php echo setPageId($curURL, $totalPages); ?>" class="last-page"><i class="fas fa-angle-double-right"></i></a>
            </div>
        </main>
        <div class="filter-panel" id="filterPanel">
            <div class="filter-panel-content">
                <form id="filterForm" method="GET">
                    <div class="filter-section">
                        <h3><i class="fas fa-calendar"></i> Date de début</h3>
                        <label><input type="date" id="start-date" name="calendar"></label>
                    </div>

                    <div class="filter-section">
                        <h3><i class="fas fa-graduation-cap"></i> Diplôme requis</h3>
                        <label><input type="radio" name="diploma" value="Pas de niveau prérequis"> Pas de niveau prérequis</label>
                        <label><input type="radio" name="diploma" value="Bac"> Bac, Bac Pro, CAP</label>
                        <label><input type="radio" name="diploma" value="Bac+2"> Bac+2</label>
                        <label><input type="radio" name="diploma" value="Bac+3"> Bac+3, Bachelor</label>
                        <label><input type="radio" name="diploma" value="Bac+5"> Bac+5, Master, diplôme d'ingénieur</label>
                        <label><input type="radio" name="diploma" value="Bac+8"> Bac+8, Doctorat</label>
                    </div>

                    <div class="filter-section">
                        <h3><i class="fas fa-euro-sign"></i> Rémunération</h3>
                        <label for="mini">Salaire minimum</label>
                        <input type="text" id="mini" name="minSalary" placeholder="Sans préférences">
                        <label for="maxi">Salaire maximum</label>
                        <input type="text" id="maxi" name="maxSalary" placeholder="Sans préférences">
                    </div>


                    <div class="filter-section">
                        <h3><i class="fas fa-map-marker-alt"></i> Localisation</h3>
                        <label for="city">Ville</label>
                        <input type="text" id="city" name="city" placeholder="Entrez une ville">

                        <label for="distance">Distance (km)</label>
                        <input type="number" id="distance" name="distance" min="0" max="500" step="10" value="50">
                    </div>

                    <div class="filter-section">
                        <h3><i class="fas fa-clock"></i> Durée</h3>
                        <div class="radio-group">
                            <label><input type="radio" name="duration" value="1"> 1 à 3 mois</label>
                            <label><input type="radio" name="duration" value="2"> 3 à 6 mois</label>
                            <label><input type="radio" name="duration" value="3"> Plus de 6 mois</label>
                        </div>
                    </div>


                    <div class="filter-section">
                        <h3><i class="fas fa-industry"></i> Secteur</h3>
                        <select id="sector" name="sector">
                            <option value="">Tous les secteurs</option>
                            <option value="Engineering">Ingénierie</option>
                            <option value="Research">Recherche</option>
                            <option value="Finance">Finance</option>
                            <option value="Design">Design</option>
                            <option value="Marketing">Marketing</option>
                        </select>
                    </div>


                    <div class="filter-section">
                        <h3><i class="fas fa-tags"></i> Mots-clés</h3>
                        <input type="text" id="skills" name="keywords" placeholder="Ex: JavaScript, Marketing, Finance">
                    </div>

                    <!-- Ajout du bouton submit -->
                    <div class="filter-panel-footer">
                        <button type="submit" form="filterForm">Appliquer les filtres</button>
                        <button type="button" class="close-filter" id="closeFilter" aria-label="Fermer les filtres">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php include dirname(__FILE__) . '/../footer.php'; ?>
        <script>
            // Filter panel functionality
            const filterPanel = document.getElementById('filterPanel');
            const blurOverlay = document.getElementById('blurOverlay');
            const openFilterBtn = document.getElementById('openFilter');
            const closeFilterBtn = document.getElementById('closeFilter');
            const filterForm = document.getElementById('filterForm');
            const navbar = document.querySelector('nav');

            function openFilterPanel() {
                filterPanel.classList.add('open');
                blurOverlay.classList.add('active');
                navbar.style.filter = 'blur(5px)';
                document.body.style.overflow = 'hidden';
            }

            function closeFilterPanel() {
                filterPanel.classList.remove('open');
                blurOverlay.classList.remove('active');
                navbar.style.filter = '';
                document.body.style.overflow = '';
            }

            openFilterBtn.addEventListener('click', openFilterPanel);
            closeFilterBtn.addEventListener('click', closeFilterPanel);
            blurOverlay.addEventListener('click', closeFilterPanel);




            // Close filter panel when pressing Escape key
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeFilterPanel();
                }
            });


            const createNotificationBtn = document.getElementById('createNotification');
            createNotificationBtn.addEventListener('click', () => {
                alert('Fonctionnalité de création de demande de notification à implémenter');
            });

            const type_show = document.getElementById('type_show');
            const groupeSecretariat = <?php echo json_encode($groupeSecretariat); ?>;
            if (groupeSecretariat) {
                type_show.style.display = "block";
            } else {
                type_show.style.display = "none";
            }
        </script>
    </body>
</html>
