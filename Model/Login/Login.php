
<?php
/*
 * Page qui permet de créer l'objet en fonction du rôle
 *
 */
require "../../Service/DB.php";
session_start();

echo($_SESSION['user']);

include "../../Model/DB.php";
include "../../Class/Personne.php";
include "../../Class/Etudiant.php";
include "../../Class/Professeur.php";
include "../../Class/Tuteur.php";
include "../../Class/Secretariat.php";

$role = $DB $_SESSION['user'];
if ()



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


