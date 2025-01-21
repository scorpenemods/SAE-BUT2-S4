<?php
require_once "../vendor/autoload.php";
require_once "..\Model\Person.php";
use PHPUnit\Framework\TestCase;

class PersonTest extends TestCase
{
    public function testPersonConstructorAndGetters()
    {
        // Créer un objet Person
        $nom = "Dupont";
        $prenom = "Jean";
        $telephone = "0123456789";
        $role = "Etudiant";
        $activite = "Informatique";
        $email = "jean.dupont@example.com";
        $id = 1;

        $person = new Person($nom, $prenom, $telephone, $role, $activite, $email, $id);

        // Vérifier les valeurs des propriétés
        $this->assertSame($nom, $person->getNom());
        $this->assertSame($prenom, $person->getPrenom());
        $this->assertSame((int)$telephone, $person->getTelephone());
        $this->assertSame($role, $person->getRole());
        $this->assertSame($activite, $person->getActivite());
        $this->assertSame($email, $person->getEmail());
        $this->assertSame($id, $person->getId());
    }

    public function testPersonWithNullTelephone()
    {
        // Créer un objet Person avec un téléphone nul
        $nom = "Martin";
        $prenom = "Alice";
        $telephone = null;
        $role = "Tuteur";
        $activite = "Mathématiques";
        $email = "alice.martin@example.com";
        $id = 2;

        $person = new Person($nom, $prenom, $telephone, $role, $activite, $email, $id);

        // Vérifier que le téléphone est nul
        $this->assertNull($person->getTelephone());
    }

    public function testPersonWithInvalidTelephone()
    {
        // Créer un objet Person avec un téléphone invalide
        $nom = "Roche";
        $prenom = "Paul";
        $telephone = "abc123";
        $role = "Professeur";
        $activite = "Physique";
        $email = "paul.roche@example.com";
        $id = 3;

        $person = new Person($nom, $prenom, $telephone, $role, $activite, $email, $id);

        // Vérifier que le téléphone est nul
        $this->assertNull($person->getTelephone());
    }
}
