<?php
use Model\Company;session_start();

require dirname(__FILE__) . '/../../../Model/Company.php';
global $tags;

if (isset($_SESSION['secretariat']) || isset($_SESSION['companyId'])) {
    $company_id = $_SESSION['companyId'];
    $groupeSecretariat = $_SESSION['secretariat'];
}

if (!(isset($_SESSION['companyId'])) || $_SESSION['companyId'] == 0) {
    $companies = Company::get_all();
}
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Le Petit Stage - Proposer une offre</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="../../css/Create.css">
        <link rel="stylesheet" href="../../css/Header.css">
        <link rel="stylesheet" href="../../css/Footer.css">
        <script src="https://kit.fontawesome.com/166cd842ba.js" crossorigin="anonymous"></script>
    </head>
    <body>
        <?php include dirname(__FILE__) . '/../../Header.php'; ?>
        <main class="container-principal">
            <h1>Créer une entreprise</h1>
            <form action="../../../../SAE-BUT2-1.1/Presentation/Offer/Company/Create.php" method="post" enctype="multipart/form-data">

                <div class="form-group">
                    <label for="title">Nom de l'entreprise</label>
                    <input type="text" id="name" name="name" placeholder="Ex: Coca Cola" required>
                </div>

                <div class="form-group">
                    <label for="address">Adresse</label>
                    <div class="search-container">
                        <input type="text" id="searchInput" class="search-input" placeholder="Entrez une adresse exemple : 123 Rue de la Paix, 75000 Paris" required>
                        <div id="dropdown" class="dropdown2"></div>
                    </div>
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">

                </div>

                <div class="form-group">
                    <label for="size">Nombre de salariés</label>
                    <input type="text" id="size" name="size" placeholder="Ex: 10 salariés" required>
                </div>

                <div class="form-group">
                    <label for="siren">Siren</label>
                    <input type="text" id="siren" name="siren" placeholder="Ex: 123443212" required>
                </div>

                <button type="submit">Créer l'entreprise</button>
            </form>
        </main>
        <?php include dirname(__FILE__) . '/../../Footer.php'; ?>
        <script>
            /*
               Manage the visibility of the tags dropdown.
             */
            const dropdownBtn = document.getElementById('tagsDropdownBtn');
            const dropdown = document.getElementById("tagsDropdown");
            const checkboxes = document.querySelectorAll('input[name="tags"]');

            function toggleDropdown() {
                dropdown.classList.toggle("show");
            }

            /*
                Close the dropdown when the user clicks outside of it.
             */
            window.onclick = function(event) {
                if (!event.target.matches('.tags-dropdown-btn') && !event.target.closest('.tags-dropdown-content')) {
                    if (dropdown.classList.contains('show')) {
                        dropdown.classList.remove('show');
                    }
                }
            }

            function updateDropdownButtonText(categories) {
                if (categories.length === 0) {
                    dropdownBtn.textContent = 'Sélectionner les catégories';
                } else if (categories.length <= 2) {
                    dropdownBtn.textContent = categories.join(', ');
                } else {
                    dropdownBtn.textContent = `${categories.length} catégories sélectionnées`;
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