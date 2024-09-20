
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
        if ($db->authenticateUser($username, $password) &&  () == 1 ||  == 2 )) {
            if ($role == 1){
                $_SESSION['user'] = new Secretariat();
            }
            header("Location: ../Principal/PrincipalAdministration.php");
            exit();
        }
        elseif ($db->authenticateUser($username, $password) && ( == 3 )){

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


