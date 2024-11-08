<?php

class Note
{
    private string $sujet;        // Sujet de la note
    private string $appreciation;     // Appréciation de la note
    private float $note;     // Note
    private float $coeff;       // Coefficient de la note

    public function __construct($sujet, $appreciation, $note, $coeff){
        $this->sujet = $sujet;
        $this->appreciation = $appreciation;
        $this->note = $note;
        $this->coeff = $coeff;
    }

    public function getSujet(): string{
        return $this->sujet;
    }

    public function getAppreciation(): string{
        return $this->appreciation;
    }

    public function getNote(): float{
        return $this->note;
    }

    public function getCoeff(): float{
        return $this->coeff;
    }
}