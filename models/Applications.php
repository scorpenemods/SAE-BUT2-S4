<?php
require dirname(__FILE__)."/../presenter/database.php";

class Applications {
    private int $id_user;
    private int $id_offer;
    private string $created_at;
    private string $status;
    private bool $favorite;

    public function __construct(int $id_user, int $id_offer, string $created_at, string $status, bool $favorite) {
        $this->id_user = $id_user;
        $this->id_offer = $id_offer;
        $this->created_at = $created_at;
        $this->status = $status;
        $this->favorite = $favorite;
    }

    public function getIdUser(): int {
        return $this->id_user;
    }

    public function getIdOffer(): int {
        return $this->id_offer;
    }

    public function getCreatedAt(): string {
        return $this->created_at;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function getFavorite(): bool {
        return $this->favorite;
    }

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
            $applications[] = new Applications($row["idUser"], $row["idOffer"], $row["created_at"], $row["status"], $row["favorite"]);
        }
        return $applications;
    }

    public static function setFavorite(int $id_offer): ?bool {
        global $db;
        $stmt = $db->prepare("UPDATE applications SET favorite = NOT favorite WHERE idOffer = :id;");
        $stmt->bindParam(":id", $id_offer);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }
        return true;
    }

    public static function validate(int $id_offer): ?bool {
        global $db;
        $stmt = $db->prepare("UPDATE status SET status = 'Accepted' WHERE idOffer = :id;");
        $stmt->bindParam(":id", $id_offer);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        return true;
    }

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
}
