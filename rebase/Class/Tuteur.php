<?php

namespace Class;

use Person;

require_once(Person::class);

class Tuteur extends Person
{
    private array $lstEtudiant;

    public function __construct($nom, $prenom, $telephone)
    {
        parent::__construct($nom, $prenom, $telephone);
        $this->lstEtudiant = array();
    }
}