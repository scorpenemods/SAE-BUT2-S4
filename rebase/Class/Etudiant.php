<?php

namespace Class;

use Person;

require_once(Person::class);

class Etudiant extends Person
{
    private string $nom;
    private string $prenom;


}