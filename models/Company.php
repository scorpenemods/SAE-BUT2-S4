<?php
require dirname(__FILE__)."/../presenter/database.php";

/**
 * Company
 * Represents a Company in the database
 */
class Company {
    private int $id;
    private string $name;
    private int $size;
    private string $address;
    private string $siren;
    private string $created_at;
    private string $updated_at;

    /**
     * __construct
     * Constructor used to instantiate the object, used only internally
     * @param int $id
     * @param string $name
     * @param int $size
     * @param string $address
     * @param string $siren
     * @param string $created_at
     * @param string $updated_at
     */
    private function __construct(int $id, string $name, int $size, string $address, string $siren, string $created_at, string $updated_at) {
        $this->id = $id;
        $this->name = $name;
        $this->size = $size;
        $this->address = $address;
        $this->siren = $siren;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    /**
     * getId
     * Returns the id of the company
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * getName
     * Returns the name of the company
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * getSize
     * Returns the size of the company
     * @return int
     */
    public function getSize(): int {
        return $this->size;
    }

    /**
     * getAddress
     * Returns the address of the company
     * @return string
     */
    public function getAddress(): string {
        return $this->address;
    }

    /**
     * getSiren
     * Returns the siren of the company
     * @return string
     */
    public function getSiren(): string {
        return $this->siren;
    }

    /**
     * getCreatedAt
     * Returns the creation date of the company
     * @return string
     */
    public function getCreatedAt(): string {
        return $this->created_at;
    }

    /**
     * getUpdatedAt
     * Returns the last update date of the company
     * @return string
     */
    public function getUpdatedAt(): string {
        return $this->updated_at;
    }

    /**
     * getById
     * Returns a company by its id
     * @param int $id
     * @return Company|null
     */
    public static function getById(int $id): ?Company {
        global $db;

        $stmt = $db->prepare("SELECT * FROM companies WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($db->errorCode() != 0) {
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
            $result["siren"],
            $result["created_at"],
            $result["updated_at"]
        );
    }

    /**
     * getAll
     * Returns all companies
     * @return array|null
     */
    public static function getAll(): ?array {
        global $db;

        $stmt = $db->prepare("SELECT * FROM companies");
        $stmt->execute();

        if ($db->errorCode() != 0) {
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
                $row["siren"],
                $row["created_at"],
                $row["updated_at"]
            );
        }

        return $companies;
    }

    /**
     * update
     * Updates a company in the database
     * @param string $name
     * @param int $size
     * @param string $address
     * @param string $siren
     * @return Company|null
     */
    public static function create(string $name, int $size, string $address, string $siren): ?Company {
        global $db;

        $stmt = $db->prepare("INSERT INTO companies (name, size, address, siren) VALUES (:name, :size, :address, :siren)");
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":size", $size);
        $stmt->bindParam(":address", $address);
        $stmt->bindParam(":siren", $siren);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        $id = $db->lastInsertId();

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

    public static function delete(int $id): ?bool {
        global $db;
        $sql = "DELETE FROM companies WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($db->errorCode()) {
            return false;
        }

        return true;
    }
}