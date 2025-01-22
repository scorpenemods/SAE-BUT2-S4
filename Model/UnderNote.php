<?php
// Under-notes managment
class Sous_Note
{
    private int $id;
    private string $description;
    private float $note;

    public function __construct($id, $description, $note){
        $this->id = $id;
        $this->description = $description;
        $this->note = $note;
    }

    /**
     * Get the id
     * @return int
     */
    public function getId(): int{
        return $this->id;
    }

    /**
     * Get the Description
     * @return string
     */
    public function getDescription(): string{
        return $this->description;
    }

    /**
     * Get the note
     * @return float
     */
    public function getNote(): float{
        return $this->note;
    }

}
