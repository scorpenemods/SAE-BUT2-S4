<?php
include_once("../Model/CreationBatch.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
    $filePath = $_FILES['csv_file']['tmp_name'];
    importCsv($filePath);
    echo "CSV import completed successfully.";
} else {
    echo "File upload error. Please upload a valid CSV file.";
}


?>
<h2>Import des utilisateurs terminé.</h2>

<button onclick="window.location.href = 'Secretariat.php';">Cliquer pour retourner à la Gestion des Utilisateurs</button>

