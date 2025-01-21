<?php
session_start();

require dirname(__FILE__) . '/../../../models/Company.php';
global $tags;

// Verification of the user
if (isset($_SESSION['secretariat']) || isset($_SESSION['company_id'])) {
    $company_id = $_SESSION['company_id'];
    $groupeSecretariat = $_SESSION['secretariat'];
}

// Check if the user is allowed to create a company
if (!(isset($_SESSION['company_id'])) || $_SESSION['company_id'] == 0) {
    $companies = Company::getAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Créer une entreprise</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="/view/css/create.css">
        <link rel="stylesheet" href="/view/css/header.css">
        <link rel="stylesheet" href="/view/css/footer.css">
        <script src="https://kit.fontawesome.com/166cd842ba.js" crossorigin="anonymous"></script>
    </head>
    <body>
        <?php include dirname(__FILE__) . '/../../header.php'; ?>
        <main class="container-principal">
            <h1>Créer une entreprise</h1>
            <form action="../../../presenter/offer/company/create.php" method="post" enctype="multipart/form-data">

                <div class="form-group">
                    <label for="title">Nom de l'entreprise</label>
                    <input type="text" id="name" name="name" placeholder="Ex: Coca Cola" required>
                </div>

                <div class="form-group">
                    <label for="address">Adresse</label>
                    <input type="text" id="address" name="address" placeholder="Ex: 123 Rue de la Paix, 75000 Paris" required>
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
        <?php include dirname(__FILE__) . '/../../footer.php'; ?>
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