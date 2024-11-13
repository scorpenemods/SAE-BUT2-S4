<?php
require_once 'Database.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function importCsv($filePath): void
{
    $db = (Database::getInstance());
    echo "Starting CSV import..." . PHP_EOL;

    if (($handle = fopen($filePath, "r")) !== FALSE) {
        echo "File opened successfully." . PHP_EOL;

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            var_dump($data);

            if (count($data) < 7) {
                echo "Incorrect number of fields in line, skipping." . PHP_EOL;
                continue;
            }

            list($email, $password, $telephone, $prenom, $activite, $role, $nom) = $data;

            echo "Processing user: $email" . PHP_EOL;

            if ($db->addUser($email, $password, $telephone, $prenom, $activite, $role, $nom,1)) {
                echo "User $email added successfully." . PHP_EOL;
            } else {
                echo "Failed to add user $email." . PHP_EOL;
            }
        }
        fclose($handle);
        echo "CSV import completed successfully." . PHP_EOL;
    } else {
        echo "Unable to open the CSV file." . PHP_EOL;
    }
}

importCsv('../test.csv');
