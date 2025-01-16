<?php

require_once "Database.php";
$db = Database::getInstance();
require dirname(__FILE__) . '/../Model/Offer.php';

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


    /**
     * Get the id
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the Company id
     * @return int
     */
    public function getCompanyId(): int
    {
        return $this->company_id;
    }

    /**
     * Get the type
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get all Tag for a pending Offer
     * @return array|null
     */
    public function getTag(): ?array
    {
        global $db;

        $stmt = $db->getConnection()->prepare("SELECT * FROM Tag JOIN pending_Tag ON Tag.id = pending_Tag.tag_id WHERE pending_Tag.pending_id = :offer_id");
        $stmt->bindParam(":offer_id", $this->id);
        $stmt->execute();

        if ($db->getConnection()->errorCode() != 0) {
            return null;
        }

        $result = $stmt->fetchAll();

        $Tag = [];
        foreach ($result as $row) {
            $Tag[] = $row["tag"];
        }

        return $Tag;
    }

    /**
     * Get an offer id
     * @return int
     */
    public function getOfferId(): int {
        return $this->offfer_id;
    }

    /**
     * Get status
     * @return string
     */
    public function getStatus(): string {
        return $this->status;
    }

    /**
     * Get pending Offer by is id
     * @param int $id
     * @return PendingOffer|null
     */
    public static function getByOfferId(int $id): ?PendingOffer
    {
        global $db;

        $stmt = $db->getConnection()->prepare("SELECT * FROM Pending_Offer WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($db->getConnection()->errorCode() != 0) {
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

    /**
     * Get pending offers by Offer id
     * @param int $id
     * @return array|null
     */
    public static function getByOffer(int $id): ?array {
        global $db;
        $stmt = $db->getConnection()->prepare("SELECT * FROM Pending_Offer WHERE offer_id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($db->getConnection()->errorCode() != 0) {
            return null;
        }

        $result = $stmt->fetchAll();

        if (!$result) {
            return null;
        }

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

    /**
     * Get all pending offers
     * @return array|null
     */
    public static function getAll(): ?array
    {
        global $db;

        $stmt = $db->getConnection()->prepare("SELECT * FROM Pending_Offer");
        $stmt->execute();

        if ($db->getConnection()->errorCode() != 0) {
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

    /**
     * Get all new offers
     * @return array|null
     */
    public static function getAllNew(): ?array {
        global $db;

        $stmt = $db->getConnection()->prepare("SELECT * FROM Pending_Offer WHERE type = 'new Offer' AND status = 'Pending'");
        $stmt->execute();

        if ($db->getConnection()->errorCode() != 0) {
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

    /**
     * Get all updated offers
     * @return array|null
     */
    public static function getAllUpdated(): ?array {
        global $db;

        $stmt = $db->getConnection()->prepare("SELECT * FROM Pending_Offer WHERE type = 'updated Offer' AND status = 'Pending'");
        $stmt->execute();

        if ($db->getConnection()->errorCode() != 0) {
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

    /**
     * Create a new pending Offer
     * @param int $company_id
     * @param string $title
     * @param string $description
     * @param string $job
     * @param int $duration
     * @param int $salary
     * @param string $address
     * @param string $education
     * @param string $startDate
     * @param array $Tag
     * @param string $email
     * @param string $phone
     * @param string $website
     * @param int $user_id
     * @param int $offer_id
     * @return PendingOffer|null
     */
    public static function createPending(int $company_id, string $title, string $description, string $job, int $duration, int $salary, string $address, string $education, string $startDate, array $Tag, string $email, string $phone, string $website, int $user_id, int $offer_id): ?PendingOffer
    {
        global $db;

        //Get the type of the Offer
        if ($offer_id == 0) {
            $type = "new Offer";
        } else {
            $type = "updated Offer";
        }

        //Insert the Offer in the pending_offers table
        $stmt = $db->getConnection()->prepare("INSERT INTO Pending_Offer (user_id, type, offer_id, company_id, title, address, job, description, duration, salary,
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

        if ($db->getConnection()->errorCode() != 0) {
            return null;
        }

        $id = $db->getConnection()->lastInsertId();

        //Add Tag in pending_Tag table
        foreach ($Tag as $tag) {
            $stmt = $db->getConnection()->prepare("INSERT INTO Pending_Tag (tag_id, pending_id) VALUES ((SELECT tag FROM Tag WHERE id = :tag_id), :offer_id)");
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

    /**
     * Get the real duration of the Offer using modulo
     * @return string
     */
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

    /**
     * Get all Tag
     * @return array
     */
    public static function getAllTag(): array
    {
        global $db;

        $stmt = $db->getConnection()->prepare("SELECT * FROM Tag;");
        $stmt->execute();

        $result = $stmt->fetchAll();

        $Tag = [];
        foreach ($result as $row) {
            $Tag[] = $row["tag"];
        }

        return $Tag;
    }

    /**
     * Set the status of an Offer
     * @param int $getId
     * @param string $string
     * @return void
     */
    public static function setStatus(int $getId, string $string) {
        global $db;

        $stmt = $db->getConnection()->prepare("UPDATE Pending_Offer SET status = :status WHERE id = :id");
        $stmt->bindParam(":status", $string);
        $stmt->bindParam(":id", $getId);
        $stmt->execute();
    }
}