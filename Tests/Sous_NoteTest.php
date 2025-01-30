<?php
// Test class for Sous_Note
require_once "../vendor/autoload.php";
require_once "../Model/UnderNote.php";
use PHPUnit\Framework\TestCase;

class Sous_NoteTest extends TestCase
{
    public function testSousNoteConstructorAndGetters()
    {
        $id = 1;
        $description = "Test Description";
        $note = 15.5;

        $sousNote = new Sous_Note($id, $description, $note);

        $this->assertSame($id, $sousNote->getId());
        $this->assertSame($description, $sousNote->getDescription());
        $this->assertSame($note, $sousNote->getNote());
    }

    public function testNegativeNote()
    {
        $id = 2;
        $description = "Negative Note Test";
        $note = -5.0;

        $sousNote = new Sous_Note($id, $description, $note);

        $this->assertSame($note, $sousNote->getNote());
    }

    public function testZeroNote()
    {
        $id = 3;
        $description = "Zero Note Test";
        $note = 0.0;

        $sousNote = new Sous_Note($id, $description, $note);

        $this->assertSame($note, $sousNote->getNote());
    }

    public function testLargeNoteValue()
    {
        $id = 4;
        $description = "Large Note Test";
        $note = 9999.99;

        $sousNote = new Sous_Note($id, $description, $note);

        $this->assertSame($note, $sousNote->getNote());
    }

    public function testEmptyDescription()
    {
        $id = 5;
        $description = "";
        $note = 10.0;

        $sousNote = new Sous_Note($id, $description, $note);

        $this->assertSame($description, $sousNote->getDescription());
    }
}