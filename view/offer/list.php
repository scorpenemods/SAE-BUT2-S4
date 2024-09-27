<?php
include "header&nav.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Petit Stage - Advanced</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --accent-color: #e74c3c;
            --background-color: #f4f6f8;
            --text-color: #34495e;
            --card-color: #ffffff;
            --menu-width: 440px;
            --menu-background: #ffffff;
            --menu-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            color: var(--text-color);
            background-color: var(--background-color);
        }

        .blur-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            backdrop-filter: blur(5px);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .blur-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .logo img {
            width: 40px;
            height: 40px;
            margin-right: 1rem;
        }

        .logo h1 {
            font-size: 1.5rem;
            margin: 0;
            font-weight: 600;
            color: var(--primary-color);
        }

        .user-profile img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }

        nav {
            background-color: var(--primary-color);
            padding: 1rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        nav ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
        }

        nav ul li {
            margin: 0 0.5rem;
        }

        nav ul li a {
            text-decoration: none;
            color: white;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            transition: background-color 0.3s ease;
        }

        nav ul li a:hover, nav ul li a.active {
            background-color: rgba(255, 255, 255, 0.2);
        }

        main {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .search-filter {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            background-color: var(--card-color);
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .search-bar {
            flex-grow: 1;
            position: relative;
            display: flex;
        }

        .search-bar input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: none;
            background-color: var(--background-color);
            border-radius: 10px 0 0 10px;
            font-family: 'Poppins', sans-serif;
        }

        .search-bar button {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 0.75rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .search-bar button:not(:last-child) {
            border-radius: 0;
        }

        .search-bar button:last-child {
            border-radius: 0 10px 10px 0;
        }

        .search-bar button:hover {
            background-color: #27ae60;
        }

        .company-listings {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .company-card {
            background-color: var(--card-color);
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .company-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .company-carousel {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .company-carousel img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .company-carousel img.active {
            opacity: 1;
        }

        .carousel-nav {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 0.5rem;
        }

        .carousel-nav button {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            border: none;
            background-color: rgba(255, 255, 255, 0.5);
            cursor: pointer;
        }

        .carousel-nav button.active {
            background-color: white;
        }

        .company-info {
            padding: 1.5rem;
        }

        .company-info h3 {
            margin-top: 0;
            color: var(--primary-color);
        }

        .company-info p {
            margin-bottom: 0.5rem;
            line-height: 1.6;
        }

        .company-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #7f8c8d;
        }

        .filter-panel {
            position: fixed;
            top: 0;
            right: calc(-1 * var(--menu-width));
            width: var(--menu-width);
            height: 100%;
            background-color: var(--menu-background);
            box-shadow: var(--menu-shadow);
            transition: transform 0.3s ease;
            z-index: 1001;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .filter-panel.open {
            transform: translateX(calc(-1 * var(--menu-width)));
        }

        .filter-panel-content {
            padding: 2rem;
            flex-grow: 1;
            overflow-y: auto;
        }

        .filter-panel h2 {
            margin-top: 0;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .filter-section {
            margin-bottom: 1.5rem;
        }

        .filter-section h3 {
            margin-bottom: 0.5rem;
            font-size: 1rem;
            color: var(--text-color);
        }

        .filter-panel label {
            display: block;
            margin-bottom: 0.5rem;
        }

        .filter-panel input[type="text"],
        .filter-panel input[type="number"],
        .filter-panel select {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
        }

        .filter-panel .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .filter-panel .checkbox-group label {
            display: flex;
            align-items: center;
            margin-bottom: 0;
        }

        .filter-panel .checkbox-group input[type="checkbox"] {
            margin-right: 0.5rem;
        }

        .filter-panel-footer {
            padding: 1rem 2rem;
            background-color: var(--background-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .filter-panel button[type="submit"] {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            flex-grow: 1;
            margin-right: 1rem;
        }

        .filter-panel button[type="submit"]:hover {
            background-color: #2980b9;
        }

        .close-filter {
            background-color: var(--accent-color);
            color: white;
            border: none;
            width: 48px;
            height: 48px;
            border-radius: 4px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 1.5rem;
            padding: 0;
        }

        .close-filter:hover {
            background-color: #c0392b;
        }

        @media (max-width: 768px) {
            .search-filter {
                flex-direction: column;
            }

            .search-bar {
                width: 100%;
                margin-bottom: 1rem;
            }

            nav ul {
                flex-wrap: wrap;
            }

            nav ul li {
                margin-bottom: 0.5rem;
            }

            .filter-panel {
                width: 100%;
                right: -100%;
            }

            .filter-panel.open {
                transform: translateX(-100%);
            }
        }
    </style>
</head>
<body>
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
        <div class="company-card">
            <div class="company-carousel">
                <img src="/placeholder.svg" alt="Entreprise Innovante 1" class="active">
                <img src="/placeholder.svg" alt="Entreprise Innovante 2">
                <img src="/placeholder.svg" alt="Entreprise Innovante 3">
                <div class="carousel-nav">
                    <button class="active"></button>
                    <button></button>
                    <button></button>
                </div>
            </div>
            <div class="company-info">
                <h3>Entreprise Innovante</h3>
                <p>Stage passionnant dans une startup dynamique spécialisée dans l'intelligence artificielle. Vous travaillerez sur des projets cutting-edge et aurez l'opportunité d'apprendre des meilleurs dans le domaine.</p>
                <div class="company-meta">
                    <span>Paris</span>
                    <span>6 mois</span>
                </div>
            </div>
        </div>
        <div class="company-card">
            <div class="company-carousel">
                <img src="/placeholder.svg" alt="Agence Créative 1" class="active">
                <img src="/placeholder.svg" alt="Agence Créative 2">
                <img src="/placeholder.svg" alt="Agence Créative 3">
                <div class="carousel-nav">
                    <button class="active"></button>
                    <button></button>
                    <button></button>
                </div>
            </div>
            <div class="company-info">
                <h3>Agence Créative</h3>
                <p>Rejoignez notre équipe de designers et développeurs talentueux pour créer des expériences web uniques. Vous participerez à des projets variés pour des clients internationaux.</p>
                <div class="company-meta">
                    <span>Lyon</span>
                    <span>3 mois</span>
                </div>
            </div>
        </div>
        <div class="company-card">
            <div class="company-carousel">
                <img src="/placeholder.svg" alt="Tech Géante 1" class="active">
                <img src="/placeholder.svg" alt="Tech Géante 2">
                <img src="/placeholder.svg" alt="Tech Géante 3">
                <div class="carousel-nav">
                    <button class="active"></button>
                    <button></button>
                    <button></button>
                </div>
            </div>
            <div class="company-info">
                <h3>Tech Géante</h3>
                <p>Opportunité exceptionnelle de stage au sein d'une des plus grandes entreprises tech. Vous serez immergé dans un environnement de travail stimulant et collaborerez sur des produits utilisés par des millions.</p>
                <div class="company-meta">
                    <span>Bordeaux</span>
                    <span>4 mois</span>
                </div>
            </div>
        </div>
    </div>
</main>

<div class="filter-panel" id="filterPanel">
    <div class="filter-panel-content">
        <h2>Filtres avancés</h2>
        <form id="filterForm">
            <div class="filter-section">
                <h3>Localisation</h3>
                <label for="city">Ville</label>
                <input type="text" id="city" name="city" placeholder="Entrez une ville">

                <label for="distance">Distance (km)</label>
                <input type="number" id="distance" name="distance" min="0" max="500" step="10" value="50">
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
                <h3>Type de stage</h3>
                <div class="checkbox-group">
                    <label><input type="checkbox" name="type" value="fulltime"> Temps plein</label>
                    <label><input type="checkbox" name="type" value="parttime"> Temps partiel</label>
                    <label><input type="checkbox" name="type" value="remote"> Télétravail</label>
                </div>
            </div>

            <div class="filter-section">
                <h3>Compétences requises</h3>
                <input type="text" id="skills" name="skills" placeholder="Ex: JavaScript, Marketing, Finance">
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

<?php
include "footer.php";
?>

<script>
    // Carousel functionality
    document.querySelectorAll('.company-carousel').forEach(carousel => {
        const images = carousel.querySelectorAll('img');
        const buttons = carousel.querySelectorAll('.carousel-nav button');
        let currentIndex = 0;

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