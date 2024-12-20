<?php

require_once "Database.php";
$db = Database::getInstance();
//Class to manage companies
class Company {
    private int $id;
    private string $name;
    private int $size;
    private string $address;
    private string $siren;
    private string $created_at;
    private string $updated_at;

    public function __construct(int $id, string $name, int $size, string $address, string $siren, string $created_at, string $updated_at) {
        $this->id = $id;
        $this->name = $name;
        $this->size = $size;
        $this->address = $address;
        $this->siren = $siren;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
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

    public function getSiren(): string {
        return $this->siren;
    }

    public function getCreatedAt(): string {
        return $this->created_at;
    }

    public function getUpdatedAt(): string {
        return $this->updated_at;
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
            $result["Siret"],
            $result["created_at"],
            $result["updated_at"]
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
                $row["Siret"],
                $row["created_at"],
                $row["updated_at"]
            );
        }

        return $companies;
    }

    //Create a new Company
    public static function create(string $name, int $size, string $address, string $siren): ?Company {
        global $db;

        $stmt = $db->getConnection()->prepare("INSERT INTO Company (name, size, address, siren) VALUES (:name, :size, :address, :siren)");
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":size", $size);
        $stmt->bindParam(":address", $address);
        $stmt->bindParam(":siren", $siren);
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
            $siren,
            date("Y-m-d H:i:s"),
            date("Y-m-d H:i:s")
        );
    }
}