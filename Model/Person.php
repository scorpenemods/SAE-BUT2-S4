<?php

class Person
{
    // Définition des propriétés privées de la classe
    private string $nom;        // Nom de l'utilisateur
    private string $prenom;     // Prénom de l'utilisateur
    private int $telephone;     // Numéro de téléphone de l'utilisateur
    private string $role;       // Rôle de l'utilisateur (par exemple, étudiant, tuteur)
    private string $activite;   // Activité professionnelle ou académique de l'utilisateur
    private int $id;            // Identifiant unique de l'utilisateur
    private string $email;      // Adresse e-mail de l'utilisateur

    // Constructeur pour initialiser un objet de type Person avec les valeurs fournies
    public function __construct($nom, $prenom, $telephone, $role, $activite, $email, $id)
    {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->telephone = $telephone;
        $this->role = $role;
        $this->activite = $activite;
        $this->email = $email;
        $this->id = $id;
    }
    //getter pour l'id
    public function getId(): int{
        return $this->id;
    }

    // Méthode pour obtenir le nom de l'utilisateur
    public function getNom(): string
    {
        return $this->nom;
    }

    // Méthode pour obtenir le prénom de l'utilisateur
    public function getPrenom(): string
    {
        return $this->prenom;
    }


    // Méthode pour obtenir le rôle de l'utilisateur
    public function getRole(): string
    {
        return $this->role;
    }

    // Méthode pour obtenir l'activité de l'utilisateur
    public function getActivite(): string
    {
        return $this->activite;
    }

    // Méthode pour obtenir le numéro de téléphone de l'utilisateur
    public function getTelephone(): int
    {
        return $this->telephone;
    }

    // Méthode pour obtenir l'adresse e-mail de l'utilisateur
    public function getEmail(): string
    {
        return $this->email;
    }

    // Méthode pour obtenir l'identifiant unique de l'utilisateur
    public function getUserId(): int
    {
        return $this->id;
    }
}
