<?php
//Class to manage Person
class Person
{
    // Setting private properties of the class
    private string $nom;        // first name of the user
    private string $prenom;     // last name of the User
    private ?int $telephone;     // phone number of the user , "?" if is null
    private string $role;       // role of the user
    private string $activite;   // Professional activity of the user
    private int $id;            // Id of user
    private string $email;      // Email address of the user

    /**
     * Constructor to initialize an object of type Person with the provided values
     * @param $nom
     * @param $prenom
     * @param $telephone
     * @param $role
     * @param $activite
     * @param $email
     * @param $id
     */
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

    /**
     * Get the id
     * @return int
     */
    public function getId(): int{
        return $this->id;
    }

    /**
     * Get the last name of user
     * @return string
     */
    public function getNom(): string
    {
        return $this->nom;
    }

    /**
     * Get the fist name of user
     * @return string
     */
    public function getPrenom(): string
    {
        return $this->prenom;
    }

    /**
     * Get the role of user
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * Get the activity of user
     * @return string
     */
    public function getActivite(): string
    {
        return $this->activite;
    }

    /**
     * Get the phone number of user
     * @return int|null
     */
    public function getTelephone(): ?int
    {
        return $this->telephone;
    }

    /**
     * Get the Email address of user
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

}