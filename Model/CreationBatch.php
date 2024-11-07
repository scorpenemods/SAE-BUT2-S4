<?php
include_once("Database.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function PasswordGenerator() : String {
    $Chaine  = "abcdefghijklmnopqrstuvwxyz123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $Chaine = str_shuffle($Chaine);
    $Chaine = substr($Chaine, 0, 10);
    return $Chaine;
}

function importCsv($filePath): void
{
    $db = Database::getInstance();
    if (empty($filePath)) {
        echo "Chemin Invalide.<br>";
        return;
    }
    echo "Début de l'importation...<br>" ;
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        echo "Fichier ouvert avec succès.<br>" ;
        // Skip the header row
        fgetcsv($handle, null, ";");
        while (($data = fgetcsv($handle, null, ";")) !== FALSE) {
            var_dump($data);
            echo "<br>";
            // Check if the correct number of fields are present
            if (count($data) < 6) {
                echo "Nombre de champs incorrect sur cette ligne.<br>" ;
                continue;
            }

            list($nom, $prenom, $email, $role, $activite, $telephone) = $data;

            // Allow telephone to be null if it is an empty string
            $telephone = !empty($telephone) ? $telephone : null;

            // Insert the user with potential null value for telephone
            $db->addUser($email, PasswordGenerator(), $telephone, $prenom, $activite, (int)$role, $nom, 1);
            echo "<br>";
        }
        fclose($handle);
        echo "Import du fichier CSV terminé avec succès.<br>";
    } else {
        echo "Impossible d'ouvrir ce fichier CSV.<br>";
    }
}


