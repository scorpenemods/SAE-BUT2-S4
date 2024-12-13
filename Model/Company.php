<?php

require_once "Database.php";
$db = Database::getInstance();
//Class to manage companies
class Company {
    private int $id;
    private string $name;
    private int $size;
    private string $address;
    private string $siret;
    private string $phone_number;
    private int $postal_code;
    private string $city;
    private string $country;
    private string $ape_code;
    private string $legal_status;
    private string $created_at;
    private string $updated_at;

    public function __construct(int $id, int $postal_code, string $name, int $size, string $address, string $siret, string $created_at, string $updated_at, string $phone_number, string $city, string $country, string $ape_code,  string $legal_status) {
        $this->id = $id;
        $this->name = $name;
        $this->size = $size;
        $this->address = $address;
        $this->siret = $siret;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
        $this->phone_number = $phone_number;
        $this->postal_code = $postal_code;
        $this->city = $city;
        $this->country = $country;
        $this->ape_code = $ape_code;
        $this->legal_status = $legal_status;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getSize(): int {
        return $this->size;
    }

    public function getAddress(): string {
        return $this->address;
    }

    public function getSiret(): string {
        return $this->siret;
    }

    public function getCreatedAt(): string {
        return $this->created_at;
    }

    public function getUpdatedAt(): string {
        return $this->updated_at;
    }

    public function getPhoneNumber(): string {
        return $this->phone_number;
    }

    public function getPostalCode(): string {
        return $this->postal_code;
    }

    public function getCity(): string {
        return $this->city;
    }

    public function getCountry(): string {
        return $this->country;
    }

    public function getApeCode(): string {
        return $this->ape_code;
    }

    public function getLegalStatus(): string {
        return $this->legal_status;
    }


    //Get a Company by its id
    public static function getById(int $id): ?Company {
        global $db;
        $stmt = $db->getConnection()->prepare("SELECT * FROM Company WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        if ($db->getConnection()->errorCode() != 0) {
            return null;
        }
        $result = $stmt->fetch();

        if (!$result) {
            return null;
        }

        return new Company(
            $result["id"],
            $result["name"],
            $result["size"],
            $result["address"],
            $result["siret"],
            $result["created_at"],
            $result["updated_at"],
            $result["phone_number"],
            $result["postal_code"],
            $result["city"],
            $result["country"],
            $result["ape_code"],
            $result["legal_status"]
        );
    }

    //Get all companies
    public static function getAll(): ?array {
        global $db;

        $stmt = $db->getConnection()->prepare("SELECT * FROM Company");
        $stmt->execute();

        if ($db->getConnection()->errorCode() != 0) {
            return null;
        }

        $result = $stmt->fetchAll();

        $companies = [];
        foreach ($result as $row) {
            $companies[] = new Company(
                $row["id"],
                $row["name"],
                $row["size"],
                $row["address"],
                $row["siret"],
                $row["created_at"],
                $row["updated_at"],
                $row["phone_number"],
                $row["postal_code"],
                $row["city"],
                $row["country"],
                $row["ape_code"],
                $row["legal_status"]

            );
        }

        return $companies;
    }

    //Create a new Company
    public static function create(string $name, int $size, string $address, string $siret, string $phone_number, int $postal_code, string $city, string $country, string $ape_code, string $legal_status): ?Company {
        global $db;

        $stmt = $db->getConnection()->prepare("INSERT INTO Company (name, size, address, siret, postal_code, phone_number, city, country, APE_code, legal_status) VALUES (:name, :size, :address, :siret, :postal_code, :phone_number, :city, :country, :ape_code, :legal_status)");
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":size", $size);
        $stmt->bindParam(":address", $address);
        $stmt->bindParam(":siret", $siret);
        $stmt->bindParam(":postal_code", $postal_code);
        $stmt->bindParam(":phone_number", $phone_number);
        $stmt->bindParam(":city", $city);
        $stmt->bindParam(":country", $country);
        $stmt->bindParam(":ape_code", $ape_code);
        $stmt->bindParam(":legal_status", $legal_status);
        $stmt->execute();

        if ($db->getConnection()->errorCode() != 0) {
            return null;
        }

        $id = $db->getConnection()->lastInsertId();

        return new Company(
            $id,
            $name,
            $size,
            $address,
            $siret,
            date("Y-m-d H:i:s"),
            date("Y-m-d H:i:s"),
            $postal_code,
            $phone_number,
            $city,
            $country,
            $ape_code,
            $legal_status
        );
    }
}