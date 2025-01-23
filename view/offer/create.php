<?php
/*
 * create.php
 * Allows the user to create a new company on the website, to then create an offer for it.
 */

session_start();

require $_SERVER["DOCUMENT_ROOT"] . '/models/Offer.php';
require $_SERVER["DOCUMENT_ROOT"] . '/models/Company.php';

global $tags;

$returnUrl = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]/view/offer/list.php";

// Check if the user is allowed to create a company
if (!(isset($_SESSION['company_id'])) || $_SESSION['company_id'] == 0) {
    $companies = Company::getAll();
} else {
    header("Location: " . $returnUrl);
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Proposer une offre</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="/view/css/create.css">
        <link rel="stylesheet" href="/view/css/header.css">
        <link rel="stylesheet" href="/view/css/footer.css">
        <script src="https://kit.fontawesome.com/166cd842ba.js" crossorigin="anonymous"></script>
    </head>
    <body>
        <?php include dirname(__FILE__) . '/../header.php'; ?>
        <main class="container-principal">
            <h1>Proposer une offre de stage</h1>
            <form action="../../presenter/offer/create.php" method="post" enctype="multipart/form-data">
                <?php if (!(isset($_SESSION['company_id'])) || $_SESSION['company_id'] == 0) {
                    echo "<div class='form-group'>";
                    // Si l'utilisateur est un secretariat, il avoir un menu déroulant avec les companies
                    echo "<label for='company_id'>Choisissez une entreprise :</label>";
                    echo "<select name='company_id' id='company_id'>";
                    foreach ($companies as $company) {
                        echo "<option value='" . $company->getId() . "'>" . $company->getName() . "</option>";
                    }
                    echo "</select>";
                    echo "</div>";
                } else {
                    echo "<input type='hidden' id='company_id' name='company_id' value='" . $_SESSION['company_id'] . "'>";
                }
                ?>
                <div class="form-group">
                    <label for="title">Titre de l'offre</label>
                    <input type="text" id="title" name="title" placeholder="Ex: Développeur Web Junior" required>
                </div>

                <div class="form-group">
                    <label for="address">Adresse</label>
                    <div class="search-container">
                        <input type="text" id="searchInput" class="search-input" placeholder="Entrez une adresse exemple : 123 Rue de la Paix, 75000 Paris" required>
                        <div id="dropdown" class="dropdown2"></div>
                    </div>
                    <input type="hidden" id="address" name="address">
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">
                </div>

                <div class="form-group">
                    <label for="position">Poste</label>
                    <input type="text" id="job" name="job" placeholder="Ex: Stagiaire en développement web" required>
                </div>

                <div class="form-group">
                    <label for="description">Description du stage</label>
                    <textarea id="description" name="description" placeholder="Décrivez les responsabilités et les tâches du stagiaire" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="duration">Durée</label>
                        <input type="text" id="duration" name="duration" placeholder="Ex: 6 semaines" required>
                    </div>
                    <div class="form-group">
                        <label for="salary">Salaire</label>
                        <input type="text" id="salary" name="salary" placeholder="Ex: 600€ / mois" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="education">Niveau d'études</label>
                        <input type="text" id="education" name="education" placeholder="Ex: Bac+3" required>
                    </div>
                    <div class="form-group">
                        <label for="start-date">Date de début</label>
                        <input type="date" id="start-date" name="start-date" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="tags">Catégories</label>
                    <div class="tags-container">
                        <button type="button" id="tagsDropdownBtn" class="tags-dropdown-btn" onclick="toggleDropdown()">Sélectionner les catégories</button>
                        <div id="tagsDropdown" class="tags-dropdown-content">
                            <?php
                            $tags = Offer::getAllTags();

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
                        <input type="email" id="email" name="email" placeholder="Ex: contact@entreprise.com" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Téléphone de contact</label>
                        <input type="tel" id="phone" name="phone" placeholder="Ex: 01 23 45 67 89" required>
                    </div>
                    <div class="form-group">
                        <label for="website">Site web</label>
                        <input type="url" id="website" name="website" placeholder="Ex: https://www.example.com" required>
                    </div>
                </div>


                <button type="submit">Publier l'offre</button>
            </form>
        </main>
        <?php include dirname(__FILE__) . '/../footer.php'; ?>
        <script>
            const dropdownBtn = document.getElementById('tagsDropdownBtn');
            const dropdown = document.getElementById("tagsDropdown");
            const checkboxes = document.querySelectorAll('input[name="tags"]');
            const addressInput = document.getElementById('address');

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
                            addressInput.value = result.display_name;
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