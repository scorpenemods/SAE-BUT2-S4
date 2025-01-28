<?php
// Test class for the CSV import functionality
require_once "../vendor/autoload.php";
require_once "../Model/Utils.php";
require_once "../Model/CreationBatch.php";
require_once '../Model/Database.php';
use PHPUnit\Framework\TestCase;

class CsvImportTest extends TestCase
{
    public function testPasswordGenerator()
    {
        $password = PasswordGenerator();

        $this->assertIsString($password);
        $this->assertEquals(10, strlen($password));
    }

    public function testImportCsvWithMalformedData()
    {
        $filePath = __DIR__ . "/malformed.csv";

        // Create a mock CSV file with missing fields
        file_put_contents($filePath, "nom;prenom;email;role;activite\nDoe;John;john.doe@example.com;1;Engineer\n");

        ob_start();
        importCsv($filePath);
        $output = ob_get_clean();

        $this->assertStringContainsString("Nombre de champs incorrect", $output);

        // Clean up
        unlink($filePath);
    }
}