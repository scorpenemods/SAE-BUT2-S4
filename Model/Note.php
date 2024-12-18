<?php

class Note
{
    private int $id;        // Identifiant de la note
    private string $sujet;        // Sujet de la note
    private float $note;     // Note
    private float $coeff;       // Coefficient de la note

    public function __construct($id, $sujet, $note, $coeff){
        $this->id = $id;
        $this->sujet = $sujet;
        $this->note = $note;
        $this->coeff = $coeff;
    }

    public function getId(): int{
        return $this->id;
    }

    public function getSujet(): string{
        return $this->sujet;
    }

    public function getNote(): float{
        return $this->note;
    }

    public function getCoeff(): float{
        return $this->coeff;
    }
}