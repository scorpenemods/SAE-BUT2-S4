<?php
use Model\Offer;session_start();
global $tags;

require dirname(__FILE__) . '/../../../Model/Offer.php';
require dirname(__FILE__) . '/../../../Model/Company.php';

// Check if user has a Company
if (isset($_SESSION['Secretariat']) || (isset($_SESSION['companyId']) && isset($_GET['id']))) {
    $companyId = $_SESSION['companyId'];
    $offer = Offer::get_by_id($_GET['id']);
    if ($companyId!= null && !Offer::is_company_offer($_GET['id'], $companyId)) {
        header("Location: ../../Offer/List.php");
        die();
    } else {
        $companyId = $offer->get_company_id();
    }
} else {
    header("Location: ../../Offer/List.php");
    die();
}

?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Le Petit Stage - Modifier une offre</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="../../css/Create.css">
        <link rel="stylesheet" href="../../css/Header.css">
        <link rel="stylesheet" href="../../css/Footer.css">
        <script src="https://kit.fontawesome.com/166cd842ba.js" crossorigin="anonymous"></script>
    </head>
    <body>
        <?php include dirname(__FILE__) . '/../../Header.php'; ?>
        <main class="container-principal">
            <h1>Modifier une offre de stage</h1>
            <form action="../../../../SAE-BUT2-1.1/Presentation/Offer/Create.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $offer->get_id(); ?>">
                <input type="hidden" name="company_id" value="<?php echo $companyId; ?>">
                <div class="form-group">
                    <label for="title">Titre de l'offre</label>
                    <input type="text" id="title" name="title" value="<?php echo $offer->get_title(); ?>" placeholder="Ex: Développeur Web Junior">
                </div>

                <div class="form-group">
                    <label for="address">Adresse</label>
                    <div class="search-container">
                        <input type="text" id="searchInput" class="search-input" placeholder="Entrez une adresse exemple : 123 Rue de la Paix, 75000 Paris" value="<?php echo $offer->get_address(); ?>" required>
                        <div id="dropdown" class="dropdown2"></div>
                    </div>
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">

                </div>

                <div class="form-group">
                    <label for="position">Poste</label>
                    <input type="text" id="job" name="job" value="<?php echo $offer->get_job(); ?>">
                </div>

                <div class="form-group">
                    <label for="description">Description du stage</label>
                    <textarea id="description" name="description"><?php echo $offer->get_description(); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="duration">Durée</label>
                        <input type="text" id="duration" name="duration" value="<?php echo $offer->get_duration(); ?>">
                    </div>
                    <div class="form-group">
                        <label for="salary">Salaire</label>
                        <input type="text" id="salary" name="salary" value="<?php echo $offer->get_salary(); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="education">Niveau d'études</label>
                        <input type="text" id="education" name="education" value="<?php echo $offer->get_study_level(); ?>">
                    </div>
                    <div class="form-group">
                        <label for="start-date">Date de début</label>
                        <input type="date" id="start-date" name="start-date" value="<?php echo $offer->get_begin_date(); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="tags">Catégories</label>
                    <div class="tags-container">
                        <button type="button" id="tagsDropdownBtn" class="tags-dropdown-btn" onclick="toggleDropdown()">Sélectionner les catégories</button>
                        <div id="tagsDropdown" class="tags-dropdown-content">
                            <?php
                            $tags = Offer::get_all_tags();
                            foreach ($tags as $tag) {
                                echo "<label><input type='checkbox' name='tag_" . $tag . "' value='" . $tag . "'> " . $tag . "</label>";
                            }
                            ?>
                        </div>
                        <div class="tagsList"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email de contact</label>
                        <input type="email" id="email" name="email" value="<?php echo $offer->get_email(); ?>">
                    </div>
                    <div class="form-group">
                        <label for="phone">Téléphone de contact</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo $offer->get_phone(); ?>">
                    </div>
                    <div class="form-group">
                        <label for="website">Site web</label>
                        <input type="url" id="website" name="website" value="<?php echo $offer->get_website(); ?>" required>
                    </div>
                </div>

                <button type="submit">Publier l'offre</button>
            </form>
        </main>
        <?php include dirname(__FILE__) . '/../../Footer.php'; ?>
        <script>
            const dropdownBtn = document.getElementById('tagsDropdownBtn');
            const dropdown = document.getElementById("tagsDropdown");
            const checkboxes = document.querySelectorAll('input[name="tags"]');

            function toggleDropdown() {
                dropdown.classList.toggle("show");
            }

            window.onclick = function(event) {
                if (!event.target.matches('.tags-dropdown-btn') && !event.target.closest('.tags-dropdown-content')) {
                    if (dropdown.classList.contains('show')) {
                        dropdown.classList.remove('show');
                        updateDropdownButtonText()
                    }
                }
            }

            function updateDropdownButtonText() {
                const categories = []
                for (const category of dropdown.children) {
                    const checked = category.querySelector('input').checked;

                    if (checked) {
                        categories.push(category.textContent.trim());
                    }
                }

                const tagsList = document.querySelector('.tagsList');
                tagsList.innerHTML = '';

                categories.forEach(category => {
                    const tag = document.createElement('span');
                    tag.classList.add('tag');
                    tag.textContent = category;

                    tagsList.appendChild(tag);
                });

                window.onload = function() {
                    updateDropdownButtonText()
                }
            }

            const searchInput = document.getElementById('searchInput');
            const dropdown2 = document.getElementById('dropdown');
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
                        dropdown2.style.display = 'none';
                    }
                }, 1000);
            });

            function displayResults(results) {
                dropdown2.innerHTML = '';
                if (results.length > 0) {
                    results.forEach(result => {
                        const item = document.createElement('div');
                        item.className = 'dropdown-item';
                        item.textContent = result.display_name;
                        item.addEventListener('click', () => {
                            searchInput.value = result.display_name;
                            latitudeInput.value = result.lat;
                            longitudeInput.value = result.lon;
                            dropdown2.style.display = 'none';
                        });
                        dropdown2.appendChild(item);
                    });
                    dropdown2.style.display = 'block';
                } else {
                    dropdown2.style.display = 'none';
                }
            }

            document.addEventListener('click', function(event) {
                if (!dropdown2.contains(event.target) && event.target !== searchInput) {
                    dropdown2.style.display = 'none';
                }
            });

            searchInput.addEventListener('focus', function() {
                if (dropdown.children.length > 0) {
                    dropdown.style.display = 'block';
                }
            });
        </script>
    </body>
</html>
