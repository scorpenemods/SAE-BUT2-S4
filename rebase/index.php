


<?php

require_once '../Class/Database.php';

$database = new Database();

$isValid = $database->verifyLogin("sae","sae");

#$isValid = $database->addUser("root","root","noahlemr@gmaiL.com","0783940317","root","admin",0,"root");

if ($isValid) {
    echo "Connexion réussie : Les identifiants sont corrects.";
} else {
    echo "Connexion échouée : Les identifiants sont incorrects.";
}

$database->closeConnection();

?>