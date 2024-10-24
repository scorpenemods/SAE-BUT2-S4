<?php

require dirname(__FILE__) . '/../presenter/database.php';
require dirname(__FILE__) . '/../models/Offer.php';

//Class to manage pending offers
class PendingOffer extends Offer
{
    private int $id;
    private int $company_id;
    private string $type;

    private int $offfer_id;

    private string $status;


    public function __construct(int $id, int $company_id, string $type, Company $company, string $title, string $description, string $job, int $duration, string $begin_date, int $salary, string $address, string $study_level, string $created_at, string $email, string $phone, string $website, int $offfer_id, string $status) {
        parent::__construct($id, $company_id, $company, $title, $description, $job, $duration,  $begin_date, $salary, $address, $study_level,  TRUE, $email, $phone, $website, date("Y-m-d H:i:s"), date("Y-m-d H:i:s"));
        $this->id = $id;
        $this->company_id = $company_id;
        $this->type = $type;
        $this->offfer_id = $offfer_id;
        $this->status = $status;
    }


    public function getId(): int
    {
        return $this->id;
    }

    public function getCompanyId(): int
    {
        return $this->company_id;
    }

    public function getType(): string
    {
        return $this->type;
    }


    public function getTags(): ?array
    {
        global $db;

        $stmt = $db->prepare("SELECT * FROM tags JOIN pending_tags ON tags.id = pending_tags.tag_id WHERE pending_tags.pending_id = :offer_id");
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

    public function getOfferId(): int
    {
        return $this->offfer_id;
    }

    public function getStatus(): string
    {
        return $this->status;
    }



    //Get all pending offers
    public static function getByOfferId(int $id): ?PendingOffer
    {
        global $db;

        $stmt = $db->prepare("SELECT * FROM pending_offers WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        $result = $stmt->fetch();

        $company = Company::getById($result["company_id"]);

        if (!$company) {
            return null;
        }

        return new PendingOffer(
            $result["id"],
            $result["company_id"],
            $result["type"],
            $company,
            $result["title"],
            $result["description"],
            $result["job"],
            $result["duration"],
            $result["begin_date"],
            $result["salary"],
            $result["address"],
            $result["study_level"],
            date("Y-m-d H:i:s", strtotime($result["created_at"])),
            $result["email"],
            $result["phone"],
            $result["website"],
            $result["offer_id"],
            $result["status"]
        );
    }

    //Get all pending offers
    public static function getAll(): ?array
    {
        global $db;

        $stmt = $db->prepare("SELECT * FROM pending_offers");
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

            $offers[] = new PendingOffer(
                $row["id"],
                $row["company_id"],
                $row["type"],
                $company,
                $row["title"],
                $row["description"],
                $row["job"],
                $row["duration"],
                $row["begin_date"],
                $row["salary"],
                $row["address"],
                $row["study_level"],
                date("Y-m-d H:i:s", strtotime($row["created_at"])),
                $row["email"],
                $row["phone"],
                $row["website"],
                $row["offer_id"],
                $row["status"]
            );
        }

        return $offers;
    }

    public static function getAllNew(): ?array {
        global $db;

        $stmt = $db->prepare("SELECT * FROM pending_offers WHERE type = 'new offer' AND status = 'Pending'");
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

            $offers[] = new PendingOffer(
                $row["id"],
                $row["company_id"],
                $row["type"],
                $company,
                $row["title"],
                $row["description"],
                $row["job"],
                $row["duration"],
                $row["begin_date"],
                $row["salary"],
                $row["address"],
                $row["study_level"],
                date("Y-m-d H:i:s", strtotime($row["created_at"])),
                $row["email"],
                $row["phone"],
                $row["website"],
                $row["offer_id"],
                $row["status"]
            );
        }

        return $offers;
    }

    public static function getAllUpdated(): ?array {
        global $db;

        $stmt = $db->prepare("SELECT * FROM pending_offers WHERE type = 'updated offer' AND status = 'Pending'");
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

            $offers[] = new PendingOffer(
                $row["id"],
                $row["company_id"],
                $row["type"],
                $company,
                $row["title"],
                $row["description"],
                $row["job"],
                $row["duration"],
                $row["begin_date"],
                $row["salary"],
                $row["address"],
                $row["study_level"],
                date("Y-m-d H:i:s", strtotime($row["created_at"])),
                $row["email"],
                $row["phone"],
                $row["website"],
                $row["offer_id"],
                $row["status"]
            );
        }

        return $offers;
    }


    //Create a new pending offer
    public static function createPending(int $company_id, string $title, string $description, string $job, int $duration, int $salary, string $address, string $education, string $startDate, array $tags, string $email, string $phone, string $website, int $user_id, int $offer_id): ?PendingOffer
    {
        global $db;

        //Get the type of the offer
        if ($offer_id == 0) {
            $type = "new offer";
        } else {
            $type = "updated offer";
        }

        //Insert the offer in the pending_offers table
        $stmt = $db->prepare("INSERT INTO pending_offers (user_id, type, offer_id, company_id, title, address, job, description, duration, salary,
                            study_level, email, phone, website, begin_date) VALUES (:user_id, :type, :offer_id, :company_id, :title, :address, :job, :description, :duration, :salary,
                            :study_level, :email, :phone, :website, :begin_date)");
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":type", $type);
        $stmt->bindParam(":offer_id", $offer_id);
        $stmt->bindParam(":company_id", $company_id);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":address", $address);
        $stmt->bindParam(":job", $job);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":duration", $duration);
        $stmt->bindParam(":salary", $salary);
        $stmt->bindParam(":study_level", $education);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":website", $website);
        $stmt->bindParam(":begin_date", $startDate);
        $stmt->execute();


        if ($db->errorCode() != 0) {
            return null;
        }

        $id = $db->lastInsertId();

        //Add tags in pending_tags table
        foreach ($tags as $tag) {
            $stmt = $db->prepare("INSERT INTO pending_tags (tag_id, pending_id) VALUES ((SELECT tag FROM tags WHERE id = :tag_id), :offer_id)");
            $stmt->bindParam(":tag_id", $tag);
            $stmt->bindParam(":offer_id", $id);
            $stmt->execute();
        }

        $company_id = intval($company_id);
        $company = Company::getById($company_id);
        $offer = new PendingOffer(
            $id,
            $company_id,
            $type,
            $company,
            $title,
            $description,
            $job,
            $duration,
            $startDate,
            $salary,
            $address,
            $education,
            date("Y-m-d H:i:s", strtotime($startDate)),
            $email,
            $phone,
            $website,
            $offer_id,
            "Pending"
        );

        return $offer;
    }

    //Get the real duration of the offer using modulo
    public function getRealDuration(): string
    {
        $duration = $this->getDuration();

        $years = intdiv($duration, 365);
        $remainingDays = $duration % 365;

        $months = intdiv($remainingDays, 30);
        $remainingDays = $remainingDays % 30;

        $weeks = intdiv($remainingDays, 7);
        $days = $remainingDays % 7;

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

    public static function getAllTags(): array
    {
        global $db;

        $stmt = $db->prepare("SELECT * FROM tags;");
        $stmt->execute();

        $result = $stmt->fetchAll();

        $tags = [];
        foreach ($result as $row) {
            $tags[] = $row["tag"];
        }

        return $tags;
    }

    //Set the status of an offer
    public static function setStatus(int $getId, string $string) {
        global $db;

        $stmt = $db->prepare("UPDATE pending_offers SET status = :status WHERE id = :id");
        $stmt->bindParam(":status", $string);
        $stmt->bindParam(":id", $getId);
        $stmt->execute();
    }

    public static function isAlreadyPending(int $id): ?bool {
        global $db;

        $stmt = $db->prepare("SELECT * FROM pending_offers WHERE offer_id = :offer_id AND status = 'Pending'");
        $stmt->bindParam(":offer_id", $id);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        $result = $stmt->fetch();

        if (!$result) {
            return false;
        }

        return true;
    }
}