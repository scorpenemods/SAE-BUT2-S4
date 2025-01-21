<?php
//Class to manage Participant
class Participant {
    private $name;
    private $speciality;
    private $email;
    private $telephone;

    public function __construct($name, $speciality, $email, $telephone = null) {
        $this->name = $name;
        $this->speciality = $speciality;
        $this->email = $email;
        $this->telephone = $telephone;
    }

    /**
     * Shows information of participant
     * @param $role
     * @return string
     */
    public function render($role) {
        return "
            <div class='participants'>
                <h3>{$role} :</h3><br>
                <p>Nom prénom : <label>{$this->name}</label></p>
                <p>Spécialité : <label>{$this->speciality}</label></p>
                <p>Email : <label>{$this->email}</label></p>
                " . ($this->telephone ? "<p>Téléphone : <label>{$this->telephone}</label></p>" : "") . "
            </div>
        ";
    }
}
