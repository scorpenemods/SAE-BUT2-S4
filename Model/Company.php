<?php
require_once "Database.php";
$db = Database::getInstance();
//Class to manage companies
class Company {
    private int $id;
    private string $name;
    private int $size;
    private string $address;
    private string $Siret ;
    private string $created_at;
    private string $updated_at;

    public function __construct(int $id, string $name, int $size, string $address, string $Siret, string $created_at, string $updated_at) {
        $this->id = $id;
        $this->name = $name;
        $this->size = $size;
        $this->address = $address;
        $this->Siret = $Siret;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    /**
     * Get the id of the company
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * Get the name of the company
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * Get the size of the company
     * @return int
     */
    public function getSize(): int {
        return $this->size;
    }

    /**
     * Get the address of the company
     * @return string
     */
    public function getAddress(): string {
        return $this->address;
    }

    /**
     * Get the siret of the company
     * @return string
     */
    public function getSiret(): string {
        return $this->Siret;
    }

    /**
     * Get the creation date of the company
     * @return string
     */
    public function getCreatedAt(): string {
        return $this->created_at;
    }

    /**
     * Get the modification date of the company
     * @return string
     */
    public function getUpdatedAt(): string {
        return $this->updated_at;
    }

    /**
     * Get a Company by his id
     * @return Company
     */
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

    /**
     * Get all companies
     * @return array
     */
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

    /**
     * Create a new Company
     * @return Company
     */
    public static function create(string $name, int $size, string $address, string $Siret): ?Company {
        global $db;

        $stmt = $db->getConnection()->prepare("INSERT INTO Company (name, size, address, Siret) VALUES (:name, :size, :address, :Siret)");
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":size", $size);
        $stmt->bindParam(":address", $address);
        $stmt->bindParam(":Siret", $Siret);
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
            $Siret,
            date("Y-m-d H:i:s"),
            date("Y-m-d H:i:s")
        );
    }
}