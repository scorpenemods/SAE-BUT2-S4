
<?php
/*
 * Page qui permet de créer l'objet en fonction du rôle
 *
 */
require_once "../../Class/Database.php";
session_start();


// Connexion à la base de données
$db = new Database();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Vérifier les informations de connexion
    try {
        #Secrétariat ou prof et tuteur
        if ($db->authenticateUser($username, $password) &&  ($db->query("SELECT role FROM a_usersae where login = $username") == 1 ||  db->query("SELECT role FROM a_usersae where login = $username") == 2 )) {
            header("Location: ../Principal/PrincipalAdministration.php");
            exit();
        }
        elseif ($db->authenticateUser($username, $password) && ( db->query("SELECT role FROM a_usersae where login = $username") == 1 ||  db->query("SELECT role FROM a_usersae where login = $username") == 2 )){

            header("Location: ../Principal/PrincipalStudent.php");
            exit();
        }

        else {
            $error = "Nom d'utilisateur ou mot de passe incorrect.";
        }
    } catch (Exception $e) {

    }
}
?>


