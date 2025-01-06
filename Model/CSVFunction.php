<?php
require_once 'Database.php';
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Import a CSV file
 * @param $filePath
 * @return void
 */
function importCsv($filePath): void
{
    $db = Database::getInstance();
    echo "Starting CSV import..." . PHP_EOL;

    if (($handle = fopen($filePath, "r")) !== FALSE) {
        echo "File opened successfully." . PHP_EOL;

        // Optional: Skip the header row if present
        // $header = fgetcsv($handle, 1000, ",");

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Debugging: Output the data read from CSV
            var_dump($data);

            // Check if the correct number of fields are present
            if (count($data) < 7) {
                echo "Incorrect number of fields in line, skipping." . PHP_EOL;
                continue;
            }

            list($email, $password, $telephone, $prenom, $activite, $role, $nom) = $data;

            // Output the data being processed
            echo "Processing user: $email" . PHP_EOL;

            // Attempt to add user and check if it was successful
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

// Path to your CSV file
importCsv('../test.csv');
