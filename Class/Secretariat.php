<?php
require_once (Personne::class);
class Secretariat extends Personne
{

    public function __construct( $nom, $prenom, $telephone,$login, $activite,$email,$id){
        parent::__construct($nom, $prenom, $telephone,$login, 1, $activite,$email,$id);

    }
}