<?php
session_start();

require dirname(__FILE__) . '/../../models/Offer.php';
require dirname(__FILE__) . '/../../models/Media.php';
require dirname(__FILE__) . '/../../models/Company.php';
require dirname(__FILE__) . '/../../presenter/apply-filter.php';
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

    </head>
    <body>
        <?php include dirname(__FILE__) . '/../header.php'; ?>
        <div class="blur-overlay" id="blurOverlay"></div>
        <main>
            <div class="search-filter">
                <div class="search-bar">
                    <input type="text" placeholder="Rechercher une offre" aria-label="Rechercher une offre">
                    <button id="openFilter" aria-label="Ouvrir les filtres">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-filter"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    </button>
                    <button id="createNotification" aria-label="Créer une demande de notification">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    </button>
                    <button id="search" aria-label="Rechercher">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </button>
                </div>
            </div>

            <div class="company-listings">
                <?php
                $offers = Offer::getAll();

                foreach ($offers as $offer) {
                    echo "<div class='company-card'>";
                        echo "<div class='company-carousel'>";
                            foreach ($offer->getMedias() as $media) {
                                echo "<img src='" . $media->getUrl() . "' alt='" . $media->getDescription() . "' " . ($media->getDisplayOrder() == 1 ? "class='active'" : "") . ">";
                            }
                            echo "<div class='carousel-nav'>";
                                foreach ($offer->getMedias() as $media) {
                                    echo "<button " . ($media->getDisplayOrder() == 1 ? "class='active'" : "") . "></button>";
                                }
                            echo "</div>";
                        echo "</div>";
                        echo "<div class='company-info'>";
                            echo "<h3>" . $offer->getTitle() . "</h3>";
                            echo "<p>" . $offer->getDescription() . "</p>";
                            echo "<div class='company-meta'>";
                                echo "<span>" . $offer->getCompany()->getName() . "</span>";
                                echo "<span>" . $offer->getAddress() . "</span>";
                                echo "<span>" . $offer->getRealDuration() . "</span>";
                            echo "</div>";
                        echo "</div>";
                    echo "</div>";
                }
                ?>
            </div>
        </main>

        <div class="filter-panel" id="filterPanel">
            <div class="filter-panel-content">
                <form action="../../presenter/apply-filter.php" id="sortForm" method="post" >

                <h2>Trier</h2>
                <div>
                    <label><input type="radio" name="sort" value="recentes">   Les plus récentes</label>
                    <label><input type="radio" name="sort" value="anciennes">   Les plus anciennes</label>
                    <label><input type="radio" name="sort" value="consultees">   Les plus consultées</label>
                </div><br>

                </form>


                <h2>Filtrer</h2>
                <form id="filterForm" action="../../presenter/apply-filter.php" method="post">

                    <div class="filter-section">
                        <h3>Date de début</h3>
                        <input type="date" id="start-date" name="calendar">
                    </div>

                    <div class="filter-section">
                        <h3>Niveau d'étude</h3>
                            <label><input type="radio" name="diploma" value="1-3"> Pas de niveau prérequis</label>
                            <label><input type="radio" name="diploma" value="3-6">Bac, Bac Pro, CAP</label>
                            <label><input type="radio" name="diploma" value="6+">Bac+2</label>
                            <label><input type="radio" name="diploma" value="3-6">Bac+3, Bachelor</label>
                            <label><input type="radio" name="diploma" value="6+">Bac+5, Master, diplôme d'ingénieur</label>
                            <label><input type="radio" name="diploma" value="3-6">Bac+8, Doctorat</label>
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
                    </div>

                    <div class="filter-section">
                        <h3>Durée du stage</h3>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="duration" value="1-3"> 1 à 3 mois</label>
                            <label><input type="checkbox" name="duration" value="3-6"> 3 à 6 mois</label>
                            <label><input type="checkbox" name="duration" value="6+"> Plus de 6 mois</label>
                        </div>
                    </div>


                    <div class="filter-section">
                        <h3>Secteur d'activité</h3>
                        <select id="sector" name="sector">
                            <option value="">Tous les secteurs</option>
                            <option value="tech">Technologie</option>
                            <option value="finance">Finance</option>
                            <option value="marketing">Marketing</option>
                            <option value="sante">Santé</option>
                            <option value="education">Éducation</option>
                        </select>
                    </div>


                    <div class="filter-section">
                        <h3>Mots clés</h3>
                        <input type="text" id="skills" name="keywords" placeholder="Ex: JavaScript, Marketing, Finance">
                    </div>
                </form>
            </div>
            <div class="filter-panel-footer">
                <button type="submit" form="filterForm">Appliquer les filtres</button>
                <button class="close-filter" id="closeFilter" aria-label="Fermer les filtres">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
        </div>
        <?php include dirname(__FILE__) . '/../footer.php'; ?>
        <script>
            // Carousel functionality
            document.querySelectorAll('.company-carousel').forEach(carousel => {
                const images = carousel.querySelectorAll('img');
                const buttons = carousel.querySelectorAll('.carousel-nav button');
                let currentIndex = 1;

                function showImage(index) {
                    images.forEach(img => img.classList.remove('active'));
                    buttons.forEach(btn => btn.classList.remove('active'));
                    images[index].classList.add('active');
                    buttons[index].classList.add('active');
                }

                buttons.forEach((button, index) => {
                    button.addEventListener('click', () => {
                        currentIndex = index;
                        showImage(currentIndex);
                    });
                });

                setInterval(() => {
                    currentIndex = (currentIndex + 1) % images.length;
                    showImage(currentIndex);
                }, 5000);
            });

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

            // Handle form submission
            filterForm.addEventListener('submit', (event) => {
                event.preventDefault();
                const formData = new FormData(filterForm);
                const filters = Object.fromEntries(formData.entries());
                console.log('Applied filters:', filters);
                // Here you would typically send these filters to your backend or update the UI
                closeFilterPanel();
            });

            // Close filter panel when pressing Escape key
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeFilterPanel();
                }
            });

            // Create notification button functionality
            const createNotificationBtn = document.getElementById('createNotification');
            createNotificationBtn.addEventListener('click', () => {
                alert('Fonctionnalité de création de demande de notification à implémenter');
            });
        </script>
    </body>
</html>