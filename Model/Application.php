<?php
require_once dirname(__FILE__) . '/Database.php';
$db = Database::getInstance()->getConnection();

/**
 * Application
 * Represent an apply for an Offer
 */
class Application {
    private int $idUser;
    private int $idOffer;
    private string $createdAt;
    private string $status;

    /**
     * @param int $idUser
     * @param int $idOffer
     * @param string $createdAt
     * @param string $status
     */
    public function __construct(int $idUser, int $idOffer, string $createdAt, string $status) {
        $this->idUser = $idUser;
        $this->idOffer = $idOffer;
        $this->createdAt = $createdAt;
        $this->status = $status;
    }

    /**
     * get_id_user
     * get ID of the user who apply
     * @return int
     */
    public function get_id_user(): int {
        return $this->idUser;
    }

    /**
     * get_id_offer
     * get ID of an Offer
     * @return int
     */
    public function get_id_offer(): int {
        return $this->idOffer;
    }

    /**
     * get_created_at
     * get the date of creation of an Application
     * @return string
     */
    public function get_created_at(): string {
        return $this->createdAt;
    }

    /**
     * get_status
     * get_status of an apply
     * @return string
     */
    public function get_status(): string {
        return $this->status;
    }

    /**
     * get_username
     * Get Username of the user apply
     * @param int $idUser
     * @return string|null
     */
    public static function get_username(int $idUser): ?string {
        global $db;

        $stmt = $db->prepare("SELECT * FROM User WHERE id = :id");
        $stmt->bindParam(":id", $idUser);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        $result = $stmt->fetch();

        if (!$result) {
            return null;
        }

        return $result["username"];
    }

    /**
     * get_offer_name
     * Get name of an Offer with is ID
     * @param int $idOffer
     * @return string|null
     */
    public static function get_offer_name(int $idOffer): ?string {
        global $db;

        $stmt = $db->prepare("SELECT * FROM Offer WHERE id = :id");
        $stmt->bindParam(":id", $idOffer);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        $result = $stmt->fetch();

        if (!$result) {
            return null;
        }

        return $result["title"];
    }

    /**
     * get_all_for_offer
     * Get All Application for an Offer
     * @param int $idOffer
     * @param string $status
     * @return array|null
     */
    public static function get_all_for_offer(int $idOffer, string $status): ?array {
        global $db;

        $stmt = $db->prepare("SELECT * FROM Application WHERE id_offer = :id AND status = :status");
        $stmt->bindParam(":id", $idOffer);
        $stmt->bindParam(":status", $status);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        $result = $stmt->fetchAll();

        if (!$result) {
            return null;
        }

        $applications = [];
        foreach ($result as $row) {
            $applications[] = new Application($row["idUser"], $row["idOffer"], $row["created_at"], $row["status"]);
        }
        return $applications;
    }

    /**
     * validate
     * Validate an Application
     * @param int $idOffer
     * @return bool|null
     */
    public static function validate(int $idOffer): ?bool {
        global $db;
        $stmt = $db->prepare("UPDATE Application SET status = 'Accepted' WHERE id_offer = :id;");
        $stmt->bindParam(":id", $idOffer);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        return true;
    }

    /**
     * refuse
     * Refuse an Application
     * @param int $idOffer
     * @return bool|null
     */
    public static function refuse(int $idOffer): ?bool {
        global $db;
        $stmt = $db->prepare("UPDATE Application SET status = 'Rejected' WHERE id_offer = :id;");
        $stmt->bindParam(":id", $idOffer);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        return true;
    }

    /**
     *
     */
    public static function get_all_for_user(int $idUser): ?array {
        global $db;
        $stmt = $db->prepare("SELECT * FROM Application WHERE id_user = :id");
        $stmt->bindParam(":id", $idUser);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        $result = $stmt->fetchAll();
        if (!$result) {
            return null;
        }

        $applications = [];
        foreach ($result as $row) {
            $applications[] = new Application($row["idUser"], $row["idOffer"], $row["created_at"], $row["status"]);
        }

        return $applications;
    }
}
