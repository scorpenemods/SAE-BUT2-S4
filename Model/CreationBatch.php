<?php
// Import users by CSV file
// Includes the Database class to handle database interactions.
include_once("Database.php");

// Enables display of errors for debugging.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Generate 10 characters password
 * @return string
 */
function PasswordGenerator() : String {
    $Chaine  = "abcdefghijklmnopqrstuvwxyz123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $Chaine = str_shuffle($Chaine); // Mix the characters
    $Chaine = substr($Chaine, 0, 10); // Take the fist 10 characters
    return $Chaine;
}

/**
 * Import users by CSV file
 * @param $filePath
 * @return void
 */
function importCsv($filePath): void
{
    $db = Database::getInstance(); // Retrieves a single instance of the database.
    if (empty($filePath)) {
        echo "Chemin Invalide.<br>";
        return; // Stops the function if the path is empty.
    }
    echo "Début de l'importation...<br>";
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        fgetcsv($handle, null, ";"); // Ignore the first line (CSV headers).
        while (($data = fgetcsv($handle, null, ";")) !== FALSE) {
            if (count($data) < 6) {
                echo "Nombre de champs incorrect sur cette ligne.<br>";
                continue; // Go to the next line if any fields are missing.
            }

            // Associates CSV columns with corresponding variables.
            list($nom, $prenom, $email, $role, $activite, $telephone) = $data;
            $telephone = !empty($telephone) ? $telephone : null; // Allows zero values for the phone.

            // Add new user to CSV's database
            $db->addUser($email, PasswordGenerator(), $telephone, $prenom, $activite, (int)$role, $nom, 1);
        }
        fclose($handle); // Closes the file after processing.
        echo "Import du fichier CSV terminé avec succès.<br>";
    } else {
        echo "Impossible d'ouvrir ce fichier CSV.<br>";
    }
}
