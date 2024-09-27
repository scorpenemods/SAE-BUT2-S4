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

        header {
            background-color: var(--card-color);
            color: var(--text-color);
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .logo {
            display: flex;
            align-items: center;
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

        .header-right {
            display: flex;
            align-items: center;
        }

        .language-select {
            margin-right: 1rem;
            padding: 0.5rem;
            border: none;
            background-color: var(--background-color);
            color: var(--text-color);
            border-radius: 4px;
            font-family: 'Poppins', sans-serif;
        }

        .user-profile {
            display: flex;
            align-items: center;
            background-color: var(--background-color);
            padding: 0.5rem;
            border-radius: 20px;
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

        .search-bar input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: none;
            background-color: var(--background-color);
            border-radius: 20px 0 0 20px;
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
            border-radius: 0 20px 20px 0;
        }

        .search-bar button:hover {
            background-color: #27ae60;
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

        .company-info h3 {
            margin-top: 0;
            color: var(--primary-color);
        }

        .company-info p {
            margin-bottom: 0.5rem;
            line-height: 1.6;
        }



        .filter-panel h2 {
            margin-top: 0;
            margin-bottom: 1rem;
            color: var(--primary-color);
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


        @media (max-width: 768px) {

            nav ul {
                flex-wrap: wrap;
            }

            nav ul li {
                margin-bottom: 0.5rem;
            }

        }
    </style>
</head>
<body>
<div class="blur-overlay" id="blurOverlay"></div>
<header>
    <div class="logo">
        <img src="/placeholder.svg" alt="Le Petit Stage Logo">
        <h1>Le Petit Stage</h1>
    </div>
    <div class="header-right">
        <select class="language-select">
            <option value="fr">FranÃ§ais</option>
            <option value="en">English</option>
        </select>
        <div class="user-profile">
            <img src="/placeholder.svg" alt="User Profile">
            <span>John Doe</span>
        </div>
    </div>
</header>

<nav>
    <ul>
        <li><a href="#accueil" class="active">Accueil</a></li>
        <li><a href="#messagerie">Messagerie</a></li>
        <li><a href="#offres">Offres</a></li>
        <li><a href="#documents">Documents</a></li>
        <li><a href="#livret">Livret de suivi</a></li>
    </ul>
</nav>
</body>
</html>