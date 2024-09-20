<?php

use Couchbase\Role;

include 'Service/DB.php';
class Personne
{
    private string $nom;
    private string $prenom;
    private int $telephone;
    private string $login;
    private string $role;
    private string $activite;
    private int $id;

    public function __construct($nom, $prenom, $telephone,$login, $role, $activite,$email,$id)
    {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->telephone = $telephone;
        $this->role = $role;
        $this->activite = $activite;
        $this->email = $email;
        $this->id = $id;
        $this->login = $login;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getActivite(): string
    {
        return $this->activite;
    }



    public function getTelephone(): int
    {
        return $this->telephone;
    }
    public function getEmail(): string
    {
        return $this->email;
    }


    #a tester avec la bdd
  #  public function setPassword($password){
   #     password_hash($password, PASSWORD_BCRYPT);
    #    $pdo->prepare("UPDATE passwords SET password_hash =  WHERE (Select id from users where login = this->getNom() && email = this->getEmail()) = user_id;")->execute([]);

    #}
    #  public function setEmail($email){
    #    $pdo->prepare("UPDATE users SET email = $email WHERE this->getId() = user_id ->execute([]);

    #}

}