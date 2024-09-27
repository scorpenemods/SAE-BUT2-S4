<?php
$offerId = $_GET['id'];
if (!isset($offerId)) {
    exit();
}

//$offer = Offer::getById($offerId);
$offer = new Offer(1, "developpeur web", "developpeur web pour faire un site de gestion d'offres de stage", "developpeur", 30, 300, true, "27-09-2024", "27-09-2024");
?>



<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Détails de l'offre - Le Petit Stage</title>
        <style>
            /* Reset and base styles */
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                background-color: #f5f5f5;
            }
            a {
                text-decoration: none;
                color: inherit;
            }

            /* Main content styles */
            main {
                max-width: 800px;
                margin: 2rem auto;
                padding: 0 1rem;
            }
            .offer-card {
                background-color: #fff;
                border-radius: 0.5rem;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            .offer-header {
                background: linear-gradient(to right, #2563eb, #3b82f6);
                color: #fff;
                padding: 2rem;
            }
            .offer-title {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
            }
            .offer-badge {
                background-color: #e5e7eb;
                color: #4b5563;
                padding: 0.25rem 0.5rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 600;
                margin-bottom: 0.5rem;
            }
            .offer-title h2 {
                font-size: 1.875rem;
                margin-bottom: 0.5rem;
            }
            .offer-date {
                font-size: 0.875rem;
                color: #e5e7eb;
            }
            .apply-button {
                background-color: #fff;
                color: #2563eb;
                padding: 0.75rem 1.5rem;
                border: none;
                border-radius: 0.25rem;
                font-weight: 600;
                cursor: pointer;
                transition: background-color 0.3s;
            }
            .apply-button:hover {
                background-color: #f3f4f6;
            }
            .offer-content {
                padding: 2rem;
            }
            .company-name {
                font-size: 1.25rem;
                font-weight: 600;
                margin-bottom: 1rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            .offer-details {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
                margin-bottom: 1.5rem;
            }
            .detail-item {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            .detail-item i {
                color: #6b7280;
            }
            .separator {
                height: 1px;
                background-color: #e5e7eb;
                margin: 1.5rem 0;
            }
            .offer-description h3 {
                font-size: 1.25rem;
                margin-bottom: 1rem;
            }
            .offer-description p {
                margin-bottom: 1.5rem;
            }
            .offer-description h4 {
                font-size: 1.125rem;
                margin-bottom: 0.75rem;
            }
            .offer-description ul {
                list-style-type: disc;
                padding-left: 1.5rem;
                margin-bottom: 1.5rem;
            }
            .offer-description li {
                margin-bottom: 0.5rem;
            }

            /* Footer styles */
            footer {
                background-color: #fff;
                border-top: 1px solid #e5e7eb;
                padding: 2rem 0;
                margin-top: 3rem;
            }


            .footer-logo img {
                width: 40px;
                height: 40px;
            }

            .footer-links a {
                color: #6b7280;
                transition: color 0.3s;
            }
            .footer-links a:hover {
                color: #4b5563;
            }

            /* Responsive styles */
            @media (max-width: 768px) {

                .offer-title {
                    flex-direction: column;
                    gap: 1rem;
                }
                .apply-button {
                    width: 100%;
                }
                .offer-details {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>
    <body>
        <?php include dirname(__FILE__) . '/../header.php'; ?>
        <main>
            <div class="offer-card">
                <div class="offer-header">
                    <div class="offer-title">
                        <div>
                            <span class="offer-badge">Stage</span>
                            <h2>Développeur Full Stack</h2>
                            <p class="offer-date">Publiée le 15 juin 2023</p>
                        </div>
                        <button class="apply-button">Postuler</button>
                    </div>
                </div>
                <div class="offer-content">
                    <h3 class="company-name">
                        <i class="fas fa-building"></i>
                        TechInnovate Solutions
                    </h3>
                    <div class="offer-details">
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <span>6 mois</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Paris, France</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar"></i>
                            <span>Début : 1 septembre 2023</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-graduation-cap"></i>
                            <span>Bac +4/5</span>
                        </div>
                    </div>
                    <div class="separator"></div>
                    <div class="offer-description">
                        <h3>Description de l'offre</h3>
                        <p>
                            Nous recherchons un stagiaire développeur Full Stack passionné pour rejoindre notre équipe dynamique.
                            Vous travaillerez sur des projets innovants utilisant les dernières technologies web.
                            C'est une opportunité unique d'apprendre et de grandir dans un environnement stimulant.
                        </p>
                        <h4>Responsabilités :</h4>
                        <ul>
                            <li>Développer et maintenir des applications web full stack</li>
                            <li>Collaborer avec l'équipe de design pour implémenter des interfaces utilisateur réactives</li>
                            <li>Participer à la conception et à l'optimisation de bases de données</li>
                            <li>Contribuer à l'amélioration continue de nos processus de développement</li>
                        </ul>
                        <h4>Compétences requises :</h4>
                        <ul>
                            <li>Connaissance en HTML, CSS, JavaScript, et frameworks modernes (React, Vue.js)</li>
                            <li>Expérience avec Node.js et bases de données SQL/NoSQL</li>
                            <li>Familiarité avec les principes de conception responsive et les bonnes pratiques UX</li>
                            <li>Capacité à travailler en équipe et à communiquer efficacement</li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
        <?php include dirname(__FILE__) . '/../footer.php'; ?>
        <script src="https://kit.fontawesome.com/your-font-awesome-kit.js" crossorigin="anonymous"></script>
    </body>
</html>