//Ce fichier permet de gérer les notes

<?php
//Class to manage Notes
class Note
{
    private int $id;        // Id of the note
    private string $sujet;        // Subject of the note
    private float $note;     // Note
    private float $coeff;       // Coefficient of the note

    public function __construct($id, $sujet, $note, $coeff){
        $this->id = $id;
        $this->sujet = $sujet;
        $this->note = $note;
        $this->coeff = $coeff;
    }

    /**
     * Get the id of the note
     * @return int
     */
    public function getId(): int{
        return $this->id;
    }

    /**
     * Get the subject of the note
     * @return string
     */
    public function getSujet(): string{
        return $this->sujet;
    }

    /**
     * Get the note
     * @return float
     */
    public function getNote(): float{
        return $this->note;
    }

    /**
     * get the coefficient of the note
     * @return float
     */
    public function getCoeff(): float{
        return $this->coeff;
    }
}