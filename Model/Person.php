<?php

class Person
{
    // Définition des propriétés privées de la classe
    private string $nom;        // Nom de l'utilisateur
    private string $prenom;     // Prénom de l'utilisateur
    private ?int $telephone;     // Numéro de téléphone de l'utilisateur ? pour accepter null
    private string $role;       // Rôle de l'utilisateur (par exemple, étudiant, tuteur)
    private string $activite;   // Activité professionnelle ou académique de l'utilisateur
    private int $id;            // Identifiant unique de l'utilisateur
    private string $email;      // Adresse e-mail de l'utilisateur


    // Constructeur pour initialiser un objet de type Person avec les valeurs fournies
    public function __construct($nom, $prenom, $telephone, $role, $activite, $email, $id)
    {
        $this->nom = $nom;
        $this->prenom = $prenom;
        // Check if tel is an int and not void
        if (!empty($telephone) && is_numeric($telephone)) {
            $this->telephone = (int)$telephone;
        } else {
            $this->telephone = null; // we can set 0 if null is not valid
        }
        $this->role = $role;
        $this->activite = $activite;
        $this->email = $email;
        $this->id = $id;

    }
    //getter pour l'id
    public function getId(): int{
        return $this->id;
    }

    public function __toString(): string{
        return $this->nom.' '.$this->prenom.' '.$this->role.' '.$this->activite.' '.$this->telephone.' '.$this->email.' '.$this->id;
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
    public function getTelephone(): ?int
    {
        return $this->telephone;
    }

    // Méthode pour obtenir l'adresse e-mail de l'utilisateur
    public function getEmail(): string
    {
        return $this->email;
    }

}