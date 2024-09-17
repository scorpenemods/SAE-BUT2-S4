<?php
include 'Service/DB.php';
class Personne
{
    private string $nom;
    private string $prenom;
    private int $telephone;

    public function __construct($nom, $prenom, $telephone){
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->telephone = $telephone;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getTelephone(): int
    {
        return $this->telephone;
    }



    public function setPassword($password){
        $pdo->prepare("UPDATE passwords SET password_hash =  WHERE (Select id from users where login =  && email = ) = user_id;")->execute([]);

    }

}