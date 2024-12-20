<?php
session_start();

require dirname(__FILE__) . '/../Model/Company.php';
global $tags;

if (isset($_SESSION['secretariat']) || isset($_SESSION['company_id'])) {
    $company_id = $_SESSION['company_id'];
    $groupesecretariat = $_SESSION['secretariat'];
}

if (!(isset($_SESSION['company_id'])) || $_SESSION['company_id'] == 0) {
    $companies = Company::getAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Le Petit Stage - Proposer une offre</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="/View/css/Create.css">
        <link rel="stylesheet" href="/View/css/Header.css">
        <link rel="stylesheet" href="/View/css/Footer.css">
        <script src="https://kit.fontawesome.com/166cd842ba.js" crossorigin="anonymous"></script>
    </head>
    <body>
        <?php include 'Header.php'; ?>
        <main class="container-principal">
            <h1>Proposer une offre de stage</h1>
            <form action="../Presentation/Offer/Create.php" method="post" enctype="multipart/form-data">
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
                    <input type="text" id="address" name="address" placeholder="Ex: 123 Rue de la Paix, 75000 Paris" required>
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
                        <input type="text" id="duration" name="duration" placeholder="Ex: 30 jours" required>
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
                            include dirname(__FILE__) . '/../Model/Offer.php';
                            $tags = Offer::getAllTags();
                            foreach ($tags as $tag) {
                                echo "<label><input type='checkbox' name='tag" . $tag . "' value='" . $tag . "'> " . $tag . "</label>";
                            }
                            ?>
                        </div>
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
                        <input type="url" id="website" name="website" placeholder="Ex: https://www.example.com">
                    </div>
                </div>


                <button type="submit">Publier l'offre</button>
            </form>
        </main>
        <?php include  'Footer.php'; ?>
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
        </script>
    </body>
</html>