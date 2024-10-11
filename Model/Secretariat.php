<?php
require_once 'Person.php';

class Secretariat extends Person
{

    public function __construct($nom, $prenom, $telephone, $login, $activite, $email, $id)
    {
        parent::__construct($nom, $prenom, $telephone, $login, 4, $activite, $email, $id);

    }
}