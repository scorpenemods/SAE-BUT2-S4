<?php

use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCreationBash{
    protected $dbMock;

    protected function setUp(): void{
        // Créer un mock pour la classe Database
        $this->dbMock = $this->createMock(Database::class);
        // Remplacer l'instance unique par le mock
        Database::setInstance($this->dbMock);
    }

    public function testPasswordGeneratorReturnStringWithsCorrectLength(){
        $password = PasswordGenerator();
        $this->assertIsString($password, "Le mot de passe doit être une chaîne.");
        $this->assertEquals(10, strlen($password), "Le mot de passe doit comporter exactement 10 caractères.");
    }

    public function testPasswordGeneratorUsesAllowedCharacters(){
        $password = PasswordGenerator();
        $allowedChars = "abcdefghijklmnopqrstuvwxyz123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

        foreach (str_split($password) as $char) {
            $this->assertStringContainsString($char, $allowedChars, "Le caractère $char n'est pas autorisé.");
        }
    }

    public function testImportCsvWithValidFile(){
        $filePath = __DIR__ . "/sample.csv"; // Créez un fichier CSV de test dans ce chemin
        $csvContent = <<<CSV
                        nom;prenom;email;role;activite;telephone
                        Doe;John;john.doe@example.com;1;Dev;123456789
                        Smith;Jane;jane.smith@example.com;2;HR;
                      CSV;

        // Créer un fichier temporaire pour le test
        file_put_contents($filePath, $csvContent);

        // Configurer le mock pour vérifier les appels à `addUser`
        $this->dbMock->expects($this->exactly(2))
            ->method('addUser')
            ->withConsecutive(
                [
                    'john.doe@example.com',
                    $this->isType('string'), // Password généré
                    '123456789',
                    'John',
                    'Dev',
                    1,
                    'Doe',
                    1
                ],
                [
                    'jane.smith@example.com',
                    $this->isType('string'), // Password généré
                    null,
                    'Jane',
                    'HR',
                    2,
                    'Smith',
                    1
                ]
            );

        // Appeler la fonction
        importCsv($filePath);

        // Supprimer le fichier temporaire
        unlink($filePath);
    }

    public function testImportCsvWithInvalidFile(){
        // Capturer la sortie
        $this->expectOutputString("Chemin Invalide.<br>");
        importCsv("");
    }

    public function testImportCsvWithMalformedCsv(){
        $filePath = __DIR__ . "/malformed.csv";
        $csvContent = <<<CSV
                        nom;prenom;email;role;activite;telephone
                        Doe;John;john.doe@example.com;1
                      CSV;

        file_put_contents($filePath, $csvContent);

        $this->expectOutputRegex("/Nombre de champs incorrect sur cette ligne.<br>/");
        importCsv($filePath);

        unlink($filePath);
    }
}