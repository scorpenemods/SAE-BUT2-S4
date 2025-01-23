<?php
require $_SERVER['DOCUMENT_ROOT'] . "/presenter/database.php";

/**
 * Applications
 * Represent an apply for an offer
 */
class Applications {
    private int $id_user;
    private int $id_offer;
    private string $created_at;
    private string $status;

    /**
     * @param int $id_user
     * @param int $id_offer
     * @param string $created_at
     * @param string $status
     */
    public function __construct(int $id_user, int $id_offer, string $created_at, string $status) {
        $this->id_user = $id_user;
        $this->id_offer = $id_offer;
        $this->created_at = $created_at;
        $this->status = $status;
    }

    /**
     * getIdUser
     * get ID of the user who apply
     * @return int
     */
    public function getIdUser(): int {
        return $this->id_user;
    }

    /**
     * getIdOffer
     * get ID of an Offer
     * @return int
     */
    public function getIdOffer(): int {
        return $this->id_offer;
    }

    /**
     * getCreatedAt
     * get the date of creation of an Application
     * @return string
     */
    public function getCreatedAt(): string {
        return $this->created_at;
    }

    /**
     * getStatus
     * getStatus of an apply
     * @return string
     */
    public function getStatus(): string {
        return $this->status;
    }

    /**
     * getUsername
     * Get Username of the user apply
     * @param int $id_user
     * @return string|null
     */
    public static function getUsername(int $id_user): ?string {
        global $db;

        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(":id", $id_user);
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
     * getOfferName
     * Get name of an offer with is ID
     * @param int $id_offer
     * @return string|null
     */
    public static function getOfferName(int $id_offer): ?string {
        global $db;

        $stmt = $db->prepare("SELECT * FROM offers WHERE id = :id");
        $stmt->bindParam(":id", $id_offer);
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
     * getAllForOffer
     * Get All applications for an Offer
     * @param int $id_offer
     * @param string $status
     * @return array|null
     */
    public static function getAllForOffer(int $id_offer, string $status): ?array {
        global $db;

        $stmt = $db->prepare("SELECT * FROM applications WHERE idOffer = :id AND status = :status");
        $stmt->bindParam(":id", $id_offer);
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
            $applications[] = new Applications($row["idUser"], $row["idOffer"], $row["created_at"], $row["status"]);
        }
        return $applications;
    }

    /**
     * validate
     * Validate an Application
     * @param int $id_offer
     * @return bool|null
     */
    public static function validate(int $id_offer): ?bool {
        global $db;
        $stmt = $db->prepare("UPDATE applications SET status = 'Accepted' WHERE idOffer = :id;");
        $stmt->bindParam(":id", $id_offer);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        return true;
    }

    /**
     * refuse
     * Refuse an Application
     * @param int $id_offer
     * @return bool|null
     */
    public static function refuse(int $id_offer): ?bool {
        global $db;
        $stmt = $db->prepare("UPDATE applications SET status = 'Rejected' WHERE idOffer = :id;");
        $stmt->bindParam(":id", $id_offer);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        return true;
    }

    /**
     *
     */
    public static function getAllForUser(int $id_user): ?array {
        global $db;
        $stmt = $db->prepare("SELECT * FROM applications WHERE idUser = :id");
        $stmt->bindParam(":id", $id_user);
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
            $applications[] = new Applications($row["idUser"], $row["idOffer"], $row["created_at"], $row["status"]);
        }

        return $applications;
    }
}
