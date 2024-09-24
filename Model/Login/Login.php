
<?php
/*
 * Page qui permet de créer l'objet en fonction du rôle
 *
 */
require "../../Service/DB.php";
session_start();

echo($_SESSION['user']);

include "../../Model/DB.php";
require "../../Class/Personne.php";
require "../../Class/Etudiant.php";
require "../../Class/Professeur.php";
require "../../Class/Tuteur.php";
require "../../Class/Secretariat.php";




/*
require_once 'Database.php';

// Connexion à la base de données
//$db = new Database('localhost', 'dbsae', 'scorpene', '8172');
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Vérifier les informations de connexion
    if ($db->authenticateUser($username, $password)) {
        header("Location: acc.php");
        exit();
    } else {
        $error = "Nom d'utilisateur ou mot de passe incorrect.";
    }
}*/
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Simple Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        header {
            background-color: #333;
            color: #fff;
            padding: 10px 0;
            text-align: center;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            margin: 0;
        }
        p {
            line-height: 1.6;
        }
        footer {
            text-align: center;
            padding: 10px;
            background-color: #333;
            color: #fff;
            position: absolute;
            width: 100%;
            bottom: 0;
        }
    </style>
</head>
<body>
<header>
    <h1>Bienvenue sur ma page</h1>
</header>
<div class="container">
    <h2>À propos de cette page</h2>
    <p>
        Ce fichier HTML est un exemple simple pour vous aider à démarrer rapidement avec une page web basique. Il comprend un en-tête, un contenu principal et un pied de page.
    </p>
    <p>
        Vous pouvez personnaliser le texte, les couleurs et le style selon vos besoins. Ajoutez du contenu, des images, des liens, ou tout ce dont vous avez besoin pour votre projet.
    </p>
</div>
<footer>
    <p>&copy; 2024 Mon Site Web</p>
</footer>
</body>
</html>


