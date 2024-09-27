<?php

class Offer {
    private int $id;
    private string $title;
    private string $description;
    private string $job;
    private int $duration;
    private int $salary;
    private bool $is_active;
    private string $created_at;
    private string $updated_at;

    public function __construct(int $id, string $title, string $description, string $job, int $duration, int $salary, bool $is_active, string $created_at, string $updated_at) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->job = $job;
        $this->duration = $duration;
        $this->salary = $salary;
        $this->is_active = $is_active;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    public function getId(): int {
        return $this->id;
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

    public function getIsActive(): bool {
        return $this->is_active;
    }

    public function getCreatedAt(): string {
        return $this->created_at;
    }

    public function getUpdatedAt(): string {
        return $this->updated_at;
    }

    public static function getById(int $id): Offer {
        global $db;

        $stmt = $db->prepare("SELECT * FROM offers WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $result = $stmt->fetch();

        return new Offer(
            $result["id"],
            $result["title"],
            $result["description"],
            $result["job"],
            $result["duration"],
            $result["salary"],
            $result["is_active"],
            $result["created_at"],
            $result["updated_at"]
        );
    }

    public static function getAll(): array {
        global $db;

        $stmt = $db->prepare("SELECT * FROM offers");
        $stmt->execute();
        $result = $stmt->fetchAll();

        $offers = [];
        foreach ($result as $row) {
            $offers[] = new Offer(
                $row["id"],
                $row["title"],
                $row["description"],
                $row["job"],
                $row["duration"],
                $row["salary"],
                $row["is_active"],
                $row["created_at"],
                $row["updated_at"]
            );
        }

        return $offers;
    }

    public static function create(string $title, string $description, string $job, int $duration, int $salary, bool $is_active): Offer {
        global $db;

        $stmt = $db->prepare("INSERT INTO offers (title, description, job, duration, salary, is_active) VALUES (:title, :description, :job, :duration, :salary, :is_active)");
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":job", $job);
        $stmt->bindParam(":duration", $duration);
        $stmt->bindParam(":salary", $salary);
        $stmt->bindParam(":is_active", $is_active);
        $stmt->execute();

        $id = $db->lastInsertId();

        return new Offer(
            $id,
            $title,
            $description,
            $job,
            $duration,
            $salary,
            $is_active,
            date("Y-m-d H:i:s"),
            date("Y-m-d H:i:s")
        );
    }

    public function getRealDuration() {
        $duration = $this->getDuration();

        //années
        $years = intdiv($duration, 365);
        $remainingDays = $duration % 365;

        //mois
        $months = intdiv($remainingDays, 30);
        $remainingDays = $remainingDays % 30;

        //semaines
        $weeks = intdiv($remainingDays, 7);
        $days = $remainingDays % 7;

        //on retourne la bonne chaine
        $result = '';

        if ($years > 0) {
            $result .= $years . ' an' . ($years > 1 ? 's' : '') . ', ';
        }
        if ($months > 0) {
            $result .= $months . ' mois, ';
        }
        if ($weeks > 0) {
            $result .= $weeks . ' semaine' . ($weeks > 1 ? 's' : '') . ', ';
        }
        if ($days > 0) {
            $result .= $days . ' jour' . ($days > 1 ? 's' : '');
        }

        return rtrim($result, ', ');
    }


}