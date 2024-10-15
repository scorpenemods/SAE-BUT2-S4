<?php
session_start();

require dirname(__FILE__) . '/../../models/Company.php';
require dirname(__FILE__) . '/../../models/Media.php';
require dirname(__FILE__) . '/../../presenter/offer/filter.php';

require dirname(__FILE__) . '/../../presenter/utils.php';

$pageId = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
if ($pageId == null) {
    $pageId = 1;
}

$curURL = $_SERVER["REQUEST_URI"];

function setPageId(string $url, int $pageId): string {
    $baseURL = substr($url, 0, strpos($url, '?'));
    parse_str($url, $urlParts);

    $urlParts["page"] = $pageId;
    $queryString = http_build_query($urlParts);

    return $baseURL . "?page=" . $pageId . ($queryString ? "&" . $queryString : "");
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

$filteredOffers = getPageOffers($pageId, $filters);
$offers = $filteredOffers["offers"];
$totalPages = $filteredOffers["totalPages"];
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Le Petit Stage - Advanced</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="/view/css/list.css">
        <link rel="stylesheet" href="/view/css/header.css">
        <link rel="stylesheet" href="/view/css/footer.css">
        <link rel="stylesheet" href="/view/css/list.css">

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
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-filter"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        </button>
                        <button type="button" id="createNotification" aria-label="Créer une demande de notification">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        </button>
                        <button type="submit" id="search" aria-label="Rechercher">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </button>
                    </div>
                </div>
            </form>

            <div class="company-listings">
                <?php
                //Get all offers and display them
                foreach ($offers as $offer) {
                    echo "<div class='company-card'>";
                        echo "<div class='company-carousel'>";
                            foreach ($offer->getMedias() as $media) {
                                echo "<img loading=\"lazy\" src='" . $media->getUrl() . "' alt='" . $media->getDescription() . "' " . ($media->getDisplayOrder() == 1 ? "class='active'" : "") . ">";
                                echo "<h3 class='title'><a href='/view/offer/detail.php?id=" . $offer->getId() . "'>" . $offer->getTitle() . "</a></h3>";
                                echo "<span class='company'>" . $offer->getCompany()->getName() . "</span>";
                            }
                        echo "</div>";
                        echo "<div class='company-info'>";
                            echo "<p>" . truncateUTF8($offer->getDescription(), 100) . "</p>";
                            echo "<div class='company-meta'>";
                                echo "<span>" . $offer->getAddress() . "</span>";
                                echo "<span>" . $offer->getRealDuration() . "</span>";
                            echo "</div>";
                        echo "</div>";
                    echo "</div>";
                }
                ?>
            </div>
            <div class="pagination">
                <a href="<?php echo setPageId($curURL, 1); ?>" class="first-page">⟸</a>
                <a href="<?php echo setPageId($curURL, $pageId > 1 ? $pageId - 1 : $pageId); ?>" class="prev-page">‹</a>
                <a aria-disabled="true"><?php echo $pageId; ?> / <?php echo $totalPages; ?></a>
                <a href="<?php echo setPageId($curURL, $pageId < $totalPages ? $pageId + 1 : $pageId); ?>" class="next-page">›</a>
                <a href="<?php echo setPageId($curURL, $totalPages); ?>" class="last-page">⟹</a>
            </div>
        </main>
        <div class="filter-panel" id="filterPanel">
            <div class="filter-panel-content">
                <form method="GET">
                <h2>Trier</h2>
                <div>
                    <label><input type="radio" name="sort" value="recentes">   Les plus récentes</label>
                    <label><input type="radio" name="sort" value="anciennes">   Les plus anciennes</label>
                    <label><input type="radio" name="sort" value="consultees">   Les plus consultées</label>
                </div><br>

                </form>


                <h2>Filtrer</h2>
                <form id="filterForm" method="GET">

                    <div class="filter-section">
                        <h3>Date de début</h3>
                        <input type="date" id="start-date" name="calendar">
                    </div>

                    <div class="filter-section">
                        <h3>Niveau d'étude</h3>
                        <label><input type="radio" name="diploma" value="Pas de niveau prérequis"> Pas de niveau prérequis</label>
                        <label><input type="radio" name="diploma" value="Bac"> Bac, Bac Pro, CAP</label>
                        <label><input type="radio" name="diploma" value="Bac+2"> Bac+2</label>
                        <label><input type="radio" name="diploma" value="Bac+3"> Bac+3, Bachelor</label>
                        <label><input type="radio" name="diploma" value="Bac+5"> Bac+5, Master, diplôme d'ingénieur</label>
                        <label><input type="radio" name="diploma" value="Bac+8"> Bac+8, Doctorat</label>
                    </div>

                    <div class="filter-section">
                        <h3>Montant du salaire</h3>
                        <label for="mini">Salaire minimum</label>
                        <input type="text" id="mini" name="minSalary" placeholder="Sans préférences">
                        <label for="maxi">Salaire maximum</label>
                        <input type="text" id="maxi" name="maxSalary" placeholder="Sans préférences">
                    </div>


                    <div class="filter-section">
                        <h3>Localisation</h3>
                        <label for="city">Ville</label>
                        <input type="text" id="city" name="city" placeholder="Entrez une ville">

                        <label for="distance">Distance (km)</label>
                        <input type="number" id="distance" name="distance" min="0" max="500" step="10" value="50">
                    </div>

                    <div class="filter-section">
                        <h3>Durée du stage</h3>
                        <div class="radio-group">
                            <label><input type="radio" name="duration" value="1"> 1 à 3 mois</label>
                            <label><input type="radio" name="duration" value="2"> 3 à 6 mois</label>
                            <label><input type="radio" name="duration" value="3"> Plus de 6 mois</label>
                        </div>
                    </div>


                    <div class="filter-section">
                        <h3>Secteur d'activité</h3>
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
                        <h3>Mots clés</h3>
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
        </script>
    </body>
</html>
