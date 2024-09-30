<?php

class Person
{
    private string $nom;
    private string $prenom;
    private int $telephone;
    private string $login;
    private string $role;
    private string $activite;
    private int $id;
    private string $email;

    public function __construct($nom, $prenom, $telephone, $login, $role, $activite, $email, $id)
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

    public function getUserId()
    {
        return $this->id;
    }


}