<?php

class Offer {
    private int $id;
    private int $company_id;
    private Company $company;
    private string $title;
    private string $description;
    private string $job;
    private int $duration;
    private int $salary;
    private string $location;
    private bool $is_active;
    private string $created_at;
    private string $updated_at;

    public function __construct(int $id, int $company_id, Company $company, string $title, string $description, string $job, int $duration, int $salary, string $location, bool $is_active, string $created_at, string $updated_at) {
        $this->id = $id;
        $this->company_id = $company_id;
        $this->company = $company;
        $this->title = $title;
        $this->description = $description;
        $this->job = $job;
        $this->duration = $duration;
        $this->salary = $salary;
        $this->location = $location;
        $this->is_active = $is_active;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getCompanyId(): int {
        return $this->company_id;
    }

    public function getCompany(): Company {
        return $this->company;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getJob(): string {
        return $this->job;
    }

    public function getDuration(): int {
        return $this->duration;
    }

    public function getSalary(): int {
        return $this->salary;
    }

    public function getLocation(): string {
        return $this->location;
    }

    public function getIsActive(): bool {
        return $this->is_active;
    }

    public function getCreatedAt(): string {
        return $this->created_at;
    }

    public function getUpdatedAt(): string {
        return $this->updated_at;
    }

    public function getTags(): ?array {
        global $db;

        $stmt = $db->prepare("SELECT * FROM tags JOIN tags_offers ON tags.id = tags_offers.tag_id WHERE tags_offers.offer_id = :offer_id");
        $stmt->bindParam(":offer_id", $this->id);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        $result = $stmt->fetchAll();

        $tags = [];
        foreach ($result as $row) {
            $tags[] = $row["tag"];
        }

        return $tags;
    }

    public function getMedias(): ?array {
        global $db;

        $stmt = $db->prepare("SELECT * FROM offers_media WHERE offer_id = :offer_id");
        $stmt->bindParam(":offer_id", $this->id);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        $result = $stmt->fetchAll();

        $medias = [];
        foreach ($result as $row) {
            $medias[] = new Media(
                $row["id"],
                $row["url"],
                $row["type"],
                $row["description"]
            );
        }

        return $medias;
    }

    public static function getById(int $id): ?Offer {
        global $db;

        $stmt = $db->prepare("SELECT * FROM offers WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $result = $stmt->fetch();

        if ($db->errorCode() != 0) {
            return null;
        }

        $company = Company::getById($result["company_id"]);

        if (!$company) {
            return null;
        }

        return new Offer(
            $result["id"],
            $result["company_id"],
            $company,
            $result["title"],
            $result["description"],
            $result["job"],
            $result["duration"],
            $result["salary"],
            $result["location"],
            $result["is_active"],
            $result["created_at"],
            $result["updated_at"]
        );
    }

    public static function getAll(): ?array {
        global $db;

        $stmt = $db->prepare("SELECT * FROM offers");
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        $result = $stmt->fetchAll();

        $offers = [];
        foreach ($result as $row) {
            $company = Company::getById($row["company_id"]);

            if (!$company) {
                continue;
            }

            $offers[] = new Offer(
                $row["id"],
                $row["company_id"],
                $company,
                $row["title"],
                $row["description"],
                $row["job"],
                $row["duration"],
                $row["salary"],
                $row["location"],
                $row["is_active"],
                $row["created_at"],
                $row["updated_at"]
            );
        }

        return $offers;
    }

    public static function create(int $company_id, Company $company, string $title, string $description, string $job, int $duration, int $salary, string $location, bool $is_active): ?Offer {
        global $db;

        $stmt = $db->prepare("INSERT INTO offers (company_id, title, description, job, duration, salary, location, is_active) VALUES (:company_id, :title, :description, :job, :duration, :salary, :location, :is_active)");
        $stmt->bindParam(":company_id", $company_id);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":job", $job);
        $stmt->bindParam(":duration", $duration);
        $stmt->bindParam(":salary", $salary);
        $stmt->bindParam(":location", $location);
        $stmt->bindParam(":is_active", $is_active);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        $id = $db->lastInsertId();

        return new Offer(
            $id,
            $company_id,
            $company,
            $title,
            $description,
            $job,
            $duration,
            $salary,
            $location,
            $is_active,
            date("Y-m-d H:i:s"),
            date("Y-m-d H:i:s")
        );
    }
}