<?php
session_start();
$_SESSION['user'] = 1;
$_SESSION['company_id'] = 1;
global $tags;

require dirname(__FILE__) . '/../../../models/Offer.php';
require dirname(__FILE__) . '/../../../models/Company.php';
require dirname(__FILE__) . '/../../../models/Media.php';

// Check if user has a company
//if ($_SESSION['company_id']) {
//    $company_id = $_SESSION['company_id'];
//}
//else {
//    header("Location: ../offer/list.php");
//    die();
//}

if ($_GET['id']) {
    $offer = Offer::getById($_GET['id']);
} else {
    header("Location: ../offer/company/list.php");
    die();
}

?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Le Petit Stage - Modifier une offre</title>
        <link rel="stylesheet" href="/view/css/create.css">
        <link rel="stylesheet" href="/view/css/header.css">
        <link rel="stylesheet" href="/view/css/footer.css">
    </head>
    <body>
        <?php include dirname(__FILE__) . '/../../header.php'; ?>
        <main class="container-principal">
            <h1>Modifier une offre de stage</h1>
            <form action="../../../presenter/offer/create.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $offer->getId(); ?>">
                <div class="form-group">
                    <label for="title">Titre de l'offre</label>
                    <input type="text" id="title" name="title" value="<?php echo $offer->getTitle(); ?>" placeholder="Ex: Développeur Web Junior">
                </div>

                <div class="form-group">
                    <label for="address">Adresse</label>
                    <input type="text" id="address" name="address" value="<?php echo $offer->getAddress(); ?>" placeholder="Ex: 123 Rue de la Paix, 75000 Paris">
                </div>

                <div class="form-group">
                    <label for="position">Poste</label>
                    <input type="text" id="job" name="job" value="<?php echo $offer->getJob(); ?>">
                </div>

                <div class="form-group">
                    <label for="description">Description du stage</label>
                    <textarea id="description" name="description"><?php echo $offer->getDescription(); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="duration">Durée</label>
                        <input type="text" id="duration" name="duration" value="<?php echo $offer->getDuration(); ?>">
                    </div>
                    <div class="form-group">
                        <label for="salary">Salaire</label>
                        <input type="text" id="salary" name="salary" value="<?php echo $offer->getSalary(); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="education">Niveau d'études</label>
                        <input type="text" id="education" name="education" value="<?php echo $offer->getStudyLevel(); ?>">
                    </div>
                    <div class="form-group">
                        <label for="start-date">Date de début</label>
                        <input type="date" id="start-date" name="start-date" value="<?php echo $offer->getBeginDate(); ?>">
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
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email de contact</label>
                        <input type="email" id="email" name="email" value="<?php echo $offer->getEmail(); ?>">
                    </div>
                    <div class="form-group">
                        <label for="phone">Téléphone de contact</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo $offer->getPhone(); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="file-upload">Pièce jointe</label>
                    <div class="file-upload" id="file-upload-area">
                        <label for="file-upload">Choisir un fichier</label>
                        <input type="file" id="file-upload" name="file-upload" accept=".pdf,.doc,.docx">
                        <p>ou glissez-déposez votre fichier ici</p>
                        <div class="file-name" id="file-name"></div>
                    </div>
                </div>

                <button type="submit">Publier l'offre</button>
            </form>
        </main>
        <?php include dirname(__FILE__) . '/../../footer.php'; ?>
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

            // File upload
            const fileUpload = document.getElementById('file-upload');
            const fileName = document.getElementById('file-name');
            const fileUploadArea = document.getElementById('file-upload-area');

            fileUpload.addEventListener('change', function(e) {
                handleFiles(e.target.files);
            });

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                fileUploadArea.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                fileUploadArea.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                fileUploadArea.addEventListener(eventName, unhighlight, false);
            });

            function highlight(e) {
                fileUploadArea.classList.add('dragover');
            }

            function unhighlight(e) {
                fileUploadArea.classList.remove('dragover');
            }

            fileUploadArea.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                handleFiles(files);
            }

            function handleFiles(files) {
                if (files.length > 0) {
                    fileName.textContent = files[0].name;
                } else {
                    fileName.textContent = '';
                }
            }
        </script>
    </body>
</html>