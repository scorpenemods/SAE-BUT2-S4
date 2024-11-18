<?php
require dirname(__FILE__)."/../presenter/database.php";

class Applications {
    private int $id_user;
    private int $id_offer;
    private string $created_at;

    public function __construct(int $id_user, int $id_offer, string $created_at) {
        $this->id_user = $id_user;
        $this->id_offer = $id_offer;
        $this->created_at = $created_at;
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

    public function getUsername(): ?string {
        global $db;
        $id_user = $this->getIdUser();

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

    public static function getAllForOffer(int $id_offer): ?array {
        global $db;

        $stmt = $db->prepare("SELECT * FROM applications WHERE idOffer = :id");
        $stmt->bindParam(":id", $id_offer);
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
            $applications[] = new Applications($row["idUser"], $row["idOffer"], $row["created_at"]);
        }
        return $applications;
    }

}