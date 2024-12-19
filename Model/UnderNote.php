<?php

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

    public function getId(): int{
        return $this->id;
    }

    public function getDescription(): string{
        return $this->description;
    }

    public function getNote(): float{
        return $this->note;
    }

}
