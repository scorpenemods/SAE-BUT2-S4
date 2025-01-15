<?php
// File: List.php
// List all offers
session_start();

require dirname(__FILE__) . '/../../Model/Company.php';
require dirname(__FILE__) . '/../../Model/PendingOffer.php';

require dirname(__FILE__) . '/../../Presentation/Utils.php';
require dirname(__FILE__) . '/../../Presentation/Offer/Filter.php';


$secretariat_group = false;
$_SESSION['secretariat'] = false;


if (isset($_SESSION['company_id'])) {
    $company_id = $_SESSION['company_id'];
}
else{
    $company_id = 0;
    $_SESSION["company_id"] = $company_id;
}


if ($_SESSION["user_role"]==4 || $_SESSION["user_role"]==5) {
    $secretariat_group = true;
    $_SESSION['secretariat'] = true;
}

if (isset($_SESSION['user'])) {
    $user_id = $_SESSION['user'];
}






$pageId = filter_input(INPUT_GET, 'pageId', FILTER_VALIDATE_INT) ?? 1;
$currentURL = $_SERVER["REQUEST_URI"];

/**
 * set_page_id
 * Sets the pageId query parameter in the given URL to the given value
 * @param string $url
 * @param int $pageId
 * @return string
 */
function set_page_id(string $url, int $pageId): string {
    $parsedUrl = parse_url($url);
    parse_str($parsedUrl['query'] ?? '', $queryParams);

    $queryParams['pageId'] = $pageId;
    $newQueryString = http_build_query($queryParams);

    return (isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '') . ($parsedUrl['host'] ?? '') . ($parsedUrl['path'] ?? '') . (!empty($newQueryString) ? '?' . $newQueryString : '');
}

/*
 * Filters
 * Get and Sanitize filters from the request
 */
error_reporting(E_ALL ^ E_DEPRECATED);
$filters = array();
$title = filter_input(INPUT_GET, 'title');
$sort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING);
$startDate = filter_input(INPUT_GET, 'startDate', FILTER_SANITIZE_STRING);
$diploma = filter_input(INPUT_GET, 'diploma', FILTER_SANITIZE_STRING);
$minSalary = filter_input(INPUT_GET, 'minSalary', FILTER_SANITIZE_STRING);
$address = filter_input(INPUT_GET, 'address', FILTER_SANITIZE_STRING);
$duration = filter_input(INPUT_GET, 'duration', FILTER_VALIDATE_INT);
$sector = filter_input(INPUT_GET, 'sector', FILTER_SANITIZE_STRING);
$keywords = filter_input(INPUT_GET, 'keywords', FILTER_SANITIZE_STRING);
$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING) ?? 'all';
$latitude = filter_input(INPUT_GET, 'latitude', FILTER_VALIDATE_INT);
$longitude = filter_input(INPUT_GET, 'longitude', FILTER_VALIDATE_INT);
$distance = filter_input(INPUT_GET, 'distance', FILTER_VALIDATE_INT);


if (isset($title)) { $filters["title"] = $title; }
if (isset($sort)) { $filters["sort"] = $sort; }
if (isset($startDate)) { $filters["startDate"] = $startDate; }
if (isset($diploma)) { $filters["diploma"] = $diploma; }
if (isset($minSalary)) { $filters["minSalary"] = $minSalary; }
if (isset($address)) { $filters["address"] = $address; }
if (isset($duration)) { $filters["duration"] = $duration; }
if (isset($sector)) { $filters["sector"] = $sector; }
if (isset($keywords)) { $filters["keywords"] = $keywords; }
if (isset($type) && ($secretariat_group || $company_id != 0)) { $filters["type"] = $type; }
if (isset($latitude)) { $filters["latitude"] = $latitude; }
if (isset($longitude)) { $filters["longitude"] = $longitude; }
if (isset($distance)) { $filters["distance"] = $distance; }
if ($company_id != 0) { $filters["company_id"] = $company_id; }

$filteredOffers = get_page_offers($pageId, $filters);
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
        <link rel="stylesheet" href="../css/Header.css">
        <link rel="stylesheet" href="../css/Footer.css">
        <link rel="stylesheet" href="../css/List.css">
        <link rel="stylesheet" href="../css/Notification.css">
        <script src="../Js/Notification.js" crossorigin="anonymous"></script>
        <script src="https://kit.fontawesome.com/166cd842ba.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    </head>
    <body>
        <?php include dirname(__FILE__) . '/../Header.php'; ?>
        <div class="blur-overlay" id="blurOverlay"></div>
        <main>
            <form method="GET">
                <div class="search-filter" id="search-filter">
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
            <div class="pagination button-group" style="text-align: center">
                <?php
                if ($secretariat_group) {
                    echo '<div id="new"> <a href="/View/Offer/List.php?type=new">Nouvelles offres</i></a> </div>';
                    echo '<div id="manage"> <a href="/View/Offer/ManageCompany.php">Gestions des sociétés</i></a> </div>';
                    echo '<div id="suppressed"><a href="/View/Offer/List.php?type=suppressed">Offres supprimés</i></a> </div>';
                    echo '<div id="create_company" style="text-align: center"> <a href="Company/Create.php">Créer une société</i></a> </div>';
                }

                if ($secretariat_group || $company_id != 0) {
                    echo '<div id="all"><a href="/View/Offer/List.php?type=all">Tous les offres</i></a> </div>';
                    echo '<div id="updated"> <a href="/View/Offer/List.php?type=updated">Offres mises à jour</i></a> </div>';
                    echo '<div id="inactive"> <a href="/View/Offer/List.php?type=inactive">Offres inactives</i></a> </div>';
                    echo '<div id="create_company" style="text-align: center"> <a href="Company/Create.php">Créer une société</i></a> </div>';
                }

                if (!$secretariat_group && $company_id == 0) {
                    echo '<div id="manage_alerts" style="text-align: center"> <a href="/View/Offer/ManageAlert.php">Gérer les alertes</i></a> </div>';
                    echo '<div id="manage_applications" style="text-align: center"> <a href="/View/Offer/ManageApplication.php">Voir mes candidatures</a></div>';

                }
                ?>
                <div id="create" style="text-align: center"> <a href="Create.php">Créer une offre</i></a> </div>
            </div>
            <div class="company-listings">
                <?php
                foreach ($offers as $offer) {
                    if ($company_id != 0 && !($company_id == $offer->get_company()->get_id())) {
                        continue;
                    }
                    echo "<a class='company-link' href='/View/Offer/Detail.php?id=" . $offer->get_id() . '&type=' . $type . "'>";
                        echo "<div class='company-card'>";
                            echo "<div class='company-header'>";
                                if ($type == 'all') {
                                    echo '<button title="Like" class="heart" onclick="heartUpdate(' . $offer->get_id() . ')"><i id="heart-icon-' . $offer->get_id() . '" class="'. (Offer::is_favorite($offer->get_id(), $user_id) ? 'fa-solid' : 'fa-regular') . ' fa-heart"></i></button>';
                                }
                                echo "<img src='".$offer->get_image()."' alt='Logo de " . $offer->get_company()->get_name() . "'>";
                                echo "<h3 class='title'>". $offer->get_title() ."</h3>";
                                echo "<span class='company'><i class='fas fa-building'></i> " . $offer->get_company()->get_name() . "</span>";
                            echo "</div>";
                            echo "<div class='company-info'>";
                                echo "<p>" . truncate_UTF8($offer->get_description(), 100) . "</p>";
                                echo "<div class='company-meta'>";
                                    echo "<span><i class='fas fa-clock'></i> " . $offer->get_real_duration() . "</span>";
                                    echo "<span><i class='fas fa-graduation-cap'></i> " . $offer->get_study_level() . "</span>";
                                echo "</div>";
                            echo "</div>";
                        echo "</div>";
                    echo "</a>";
                }
                ?>
            </div>
            <div id="pagination" class="pagination">
                <a href="<?php echo set_page_id($currentURL, 1); ?>" class="first-page"><i class="fas fa-angle-double-left"></i></a>
                <a href="<?php echo set_page_id($currentURL, $pageId > 1 ? $pageId - 1 : $pageId); ?>" class="prev-page"><i class="fas fa-angle-left"></i></a>
                <a disabled="true" href="#"><?php echo $pageId; ?> / <?php echo $totalPages; ?></a>
                <a href="<?php echo set_page_id($currentURL, $pageId < $totalPages ? $pageId + 1 : $pageId); ?>" class="next-page"><i class="fas fa-angle-right"></i></a>
                <a href="<?php echo set_page_id($currentURL, $totalPages); ?>" class="last-page"><i class="fas fa-angle-double-right"></i></a>
            </div>
        </main>
        <div class="filter-panel" id="filterPanel">
            <div id="filter-panel-content" class="filter-panel-content">
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
                    </div>

                    <div class="filter-section">
                        <h3><i class="fas fa-map-marker-alt"></i> Localisation</h3>

                        <label for="address">Adresse</label>
                        <div class="search-container">
                            <input type="text" id="searchInput" class="search-input" placeholder="Entrez une adresse">
                            <div id="dropdown" class="dropdown"></div>
                        </div>
                        <input type="hidden" id="latitude" name="latitude">
                        <input type="hidden" id="longitude" name="longitude">

                        <label for="distance">Distance</label>
                        <input type="range" id="distance" name="distance" min="0" max="10000" step="1" value="0">
                        <span id="distance-value">100</span>
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
        <?php include dirname(__FILE__) . '/../Footer.php'; ?>
        <script>
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

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeFilterPanel();
                }
            });

            const createNotificationBtn = document.getElementById('createNotification');
            createNotificationBtn.addEventListener('click', (e) => {
                e.preventDefault()
                const urlParams = new URLSearchParams(window.location.search);
                const filters = {
                    minSalary: urlParams.get('minSalary'),
                    address: urlParams.get('address'),
                    diploma: urlParams.get('diploma'),
                    duration: urlParams.get('duration'),
                    calendar: urlParams.get('calendar')
                };

                $.ajax({
                    url: '/Presentation/Offer/Alert/Create.php',
                    type: 'POST',
                    data: {
                        duration: filters.duration,
                        address: filters.address,
                        study_level: filters.diploma,
                        begin_date: filters.calendar,
                        salary: filters.minSalary
                    },
                    success: function(response) {
                        const result = JSON.parse(response);

                        if (result.status === "success") {
                            sendNotification("success", "Succès", "Notification créée avec succès!");
                        } else {
                            sendNotification("failure", "Erreur", result.message || "Une erreur est survenue.");
                        }
                    },
                    error: function(xhr, status, error) {
                        sendNotification("failure", "Erreur", "Une erreur réseau est survenue lors de la création de la notification.");
                    }
                });
            });

            function heartUpdate(id) {
                $.ajax({
                    url: '/Presentation/Offer/Favorite.php',
                    type: 'POST',
                    data: {id: id},
                    success: function(msg, status, jqXHR) {
                        if (status === "success") {
                            const heartIcon = document.getElementById('heart-icon-' + id);
                            if (heartIcon.classList.contains('fa-regular')) {
                                heartIcon.classList.remove('fa-regular');
                                heartIcon.classList.add('fa-solid');
                            } else {
                                heartIcon.classList.remove('fa-solid');
                                heartIcon.classList.add('fa-regular');
                            }
                        }
                    }
                });
            }

            document.querySelectorAll('.heart').forEach((button) => {
                button.addEventListener('click', (event) => {
                    event.preventDefault();
                });
            });

            const searchInput = document.getElementById('searchInput');
            const dropdown = document.getElementById('dropdown');
            const latitudeInput = document.getElementById('latitude');
            const longitudeInput = document.getElementById('longitude');

            let debounceTimer;

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);

                debounceTimer = setTimeout(() => {
                    const query = this.value.trim();
                    if (query.length > 2) {
                        fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=jsonv2&polygon_geojson=1`)
                            .then(response => response.json())
                            .then(data => {
                                displayResults(data);
                            })
                            .catch(error => {
                                console.error('Error fetching results:', error);
                            });
                    } else {
                        dropdown.style.display = 'none';
                    }
                }, 1000);
            });

            function displayResults(results) {
                dropdown.innerHTML = '';
                if (results.length > 0) {
                    results.forEach(result => {
                        const item = document.createElement('div');
                        item.className = 'dropdown-item';
                        item.textContent = result.display_name;
                        item.addEventListener('click', () => {
                            searchInput.value = result.display_name;
                            latitudeInput.value = result.lat;
                            longitudeInput.value = result.lon;
                            dropdown.style.display = 'none';
                        });
                        dropdown.appendChild(item);
                    });
                    dropdown.style.display = 'block';
                } else {
                    dropdown.style.display = 'none';
                }
            }

            document.addEventListener('click', function(event) {
                if (!dropdown.contains(event.target) && event.target !== searchInput) {
                    dropdown.style.display = 'none';
                }
            });

            searchInput.addEventListener('focus', function() {
                if (dropdown.children.length > 0) {
                    dropdown.style.display = 'block';
                }
            });

            const rangeDistance = document.getElementById("distance")
            const spanDistance = document.getElementById("distance-value")
            rangeDistance.addEventListener('input', function() {
                spanDistance.innerHTML = this.value

                if (this.value <= 100) {
                    this.step = 10
                } else if (this.value <= 1000) {
                    this.step = 100
                } else if (this.value <= 10000) {
                    this.step = 1000
                }
            })

        </script>
    </body>
</html>