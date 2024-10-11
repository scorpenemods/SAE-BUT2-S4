<?php


require_once 'Person.php';

class Professeur extends Person
{
    private array $lstEtudiant;

    public function __construct($nom, $prenom, $telephone)
    {
        parent::__construct($nom, $prenom, $telephone);
        $this->lstEtudiant = array();
    }

}