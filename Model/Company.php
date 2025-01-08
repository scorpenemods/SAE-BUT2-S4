<?php
require_once dirname(__FILE__) . '/Database.php';
$db = Database::getInstance()->getConnection();

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
     * get_id
     * Returns the id of the Company
     * @return int
     */
    public function get_id(): int {
        return $this->id;
    }

    /**
     * get_name
     * Returns the name of the Company
     * @return string
     */
    public function get_name(): ?string {
        return $this->name;
    }

    /**
     * get_size
     * Returns the size of the Company
     * @return int
     */
    public function get_size(): int {
        return $this->size;
    }

    /**
     * get_address
     * Returns the address of the Company
     * @return string
     */
    public function get_address(): string {
        return $this->address;
    }

    /**
     * get_siren
     * Returns the siren of the Company
     * @return string
     */
    public function get_siren(): string {
        return $this->siren;
    }

    /**
     * get_created_at
     * Returns the creation date of the Company
     * @return string
     */
    public function get_created_at(): string {
        return $this->created_at;
    }

    /**
     * get_updated_at
     * Returns the last update date of the Company
     * @return string
     */
    public function get_updated_at(): string {
        return $this->updated_at;
    }

    /**
     * get_by_id
     * Returns a Company by its id
     * @param int $id
     * @return Company|null
     */
    public static function get_by_id(int $id): ?Company {

        global $db;
        $stmt = $db->prepare("SELECT * FROM Company WHERE id = :id");
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
     * get_all
     * Returns all companies
     * @return array|null
     */
    public static function get_all(): ?array {
        global $db;

        $stmt = $db->prepare("SELECT * FROM Company");
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
     * Updates a Company in the database
     * @param string $name
     * @param int $size
     * @param string $address
     * @param string $siren
     * @return Company|null
     */
    public static function create(string $name, int $size, string $address, string $siren): ?Company {
        global $db;

        $stmt = $db->prepare("INSERT INTO Company (name, size, address, siren) VALUES (:name, :size, :address, :siren)");
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
        $sql = "DELETE FROM Company WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($db->errorCode()) {
            return false;
        }

        return true;
    }
}