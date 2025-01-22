<?php
// Test of notes
require_once "../vendor/autoload.php";
require_once "..\Model\Note.php";
use PHPUnit\Framework\TestCase;

class NoteTest extends TestCase
{
    public function testNoteConstructorAndGetters()
    {
        $id = 1;
        $sujet = "Mathematiques";
        $note = 15.5;
        $coeff = 2.0;

        $noteInstance = new Note($id, $sujet, $note, $coeff);

        $this->assertSame($id, $noteInstance->getId());
        $this->assertSame($sujet, $noteInstance->getSujet());
        $this->assertSame($note, $noteInstance->getNote());
        $this->assertSame($coeff, $noteInstance->getCoeff());
    }

    public function testInvalidNoteValue()
    {
        $this->expectException(TypeError::class);

        // Essayer de passer une note non-float
        new Note(1, "Physique", "invalid", 2.0);
    }

    public function testInvalidCoeffValue()
    {
        $this->expectException(TypeError::class);

        // Essayer de passer un coefficient non-float
        new Note(1, "Physique", 12.0, "invalid");
    }

    public function testNegativeNote()
    {
        $noteInstance = new Note(1, "Chimie", -5.0, 1.5);

        $this->assertSame(-5.0, $noteInstance->getNote());
    }
}
