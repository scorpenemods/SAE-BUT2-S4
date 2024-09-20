
<?php
/*
 * Page qui permet de créer l'objet en fonction du rôle
 *
 */
require "../../Service/DB.php";
session_start();


// Connexion à la base de données
//$db = new Database('localhost', 'dbsae', 'scorpene', '8172');
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Vérifier les informations de connexion
    try {
        #Secrétariat ou prof et tuteur
        if ($db->authenticateUser($username, $password) &&  (db->query("SELECT role FROM a_usersae where login = $username") == 1 ||  db->query("SELECT role FROM a_usersae where login = $username") == 2 )) {
            header("Location: ../Accueil/index.php");
            exit();
        }
        elseif ($db->authenticateUser($username, $password) && ( db->query("SELECT role FROM a_usersae where login = $username") == 1 ||  db->query("SELECT role FROM a_usersae where login = $username") == 2 )){
            header("Location: ../Accueil/index.php");
            exit();
        }

        else {
            $error = "Nom d'utilisateur ou mot de passe incorrect.";
        }
    } catch (Exception $e) {

    }
}
?>


