<?php
include_once("../Model/Database.php");


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function  PasswordGenerator() : String {
    $Chaine  = "abcdefghijklmnopqrstuvwxyz123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $Chaine->str_shuffle($Chaine);
    $Chaine = substr($Chaine,0,10);
    return $Chaine;
};


function importCsv($filePath): void
{
    $db = (Database::getInstance()) ;

    echo "Starting CSV import..." . PHP_EOL;
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        echo "File opened successfully." . PHP_EOL;

        while (($data = fgetcsv($handle, null, ";")) !== FALSE) {
            // Debugging: Output the data read from CSV
            var_dump($data);
            // Check if the correct number of fields are present

            if (count($data) < 6) {
                echo "Incorrect number of fields in line, skipping." . PHP_EOL;
                continue;
            }
            list($nom,$prenom,$email,$role,$activite,$telephone) = $data;
            $db->addUser($email, PasswordGenerator(), $telephone, $prenom, $activite, $role, $nom,1);
        }
        fclose($handle);
        echo "CSV import completed successfully." . PHP_EOL;
    } else {
        echo "Unable to open the CSV file." . PHP_EOL;
    }
}
echo"ALED";
importCsv("testCSV2.csv");