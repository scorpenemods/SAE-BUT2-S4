<?php


class Professeur extends Personne
{
    private array $lstEtudiant;
    public function __construct($nom,$prenom,$telephone){
        parent::__construct($nom,$prenom,$telephone);
        $this->lstEtudiant = array();
    }

}