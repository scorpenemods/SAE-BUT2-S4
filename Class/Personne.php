<?php

class Personne
{
    private string $nom;
    private string $prenom;
    private int $telephone;
    private string $role;
    private string $activite;
    private string $email;

    public function __construct($nom, $prenom, $telephone, $role, $activite, $email) {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->telephone = $telephone;
        $this->role = $role;
        $this->activite = $activite;
        $this->email = $email;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function getTelephone(): int
    {
        return $this->telephone;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getActivite(): string
    {
        return $this->activite;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
