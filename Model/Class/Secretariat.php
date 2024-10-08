<?php

namespace Class;

use Person;

require_once(Person::class);

class Secretariat extends Person
{

    public function __construct($nom, $prenom, $telephone, $login, $activite, $email, $id)
    {
        parent::__construct($nom, $prenom, $telephone, $login, 1, $activite, $email, $id);

    }
}