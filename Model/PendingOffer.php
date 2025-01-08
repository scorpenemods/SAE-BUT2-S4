<?php

require_once dirname(__FILE__) . '/Database.php';
require_once dirname(__FILE__) . '/../Model/Offer.php';
$db = Database::getInstance()->getConnection();

/**
 * PendingOffer
 * Represents a PendingOffer in the database
 */
class PendingOffer extends Offer {
    private int $id;
    private int $company_id;
    private string $type;
    private int $offer_id;
    private string $status;

    /**
     * __construct
     * Constructor used to instantiate the object, used only internally
     * @param int $id
     * @param int $company_id
     * @param string $type
     * @param Company $company
     * @param string $title
     * @param string $description
     * @param string $job
     * @param int $duration
     * @param string $begin_date
     * @param int $salary
     * @param string $address
     * @param string $study_level
     * @param string $created_at
     * @param string $email
     * @param string $phone
     * @param string $website
     * @param int $offfer_id
     * @param string $status
     * @param float $latitude
     * @param float $longitude
     */
    public function __construct(int $id, int $company_id, string $type, Company $company, string $title, string $description, string $job, int $duration, string $begin_date, int $salary, string $address, string $study_level, string $created_at, string $email, string $phone, string $website, int $offfer_id, string $status, float $latitude, float $longitude) {
        parent::__construct($id, $company_id, $company, $title, $description, $job, $duration,  $begin_date, $salary, $address, $study_level,  TRUE, $email, $phone, $website, date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), false,$latitude, $longitude);
        $this->id = $id;
        $this->company_id = $company_id;
        $this->type = $type;
        $this->offer_id = $offfer_id;
        $this->status = $status;
    }

    /**
     * instantiate_rows
     * Instantiates the rows of the statement
     * @param false|PDOStatement $stmt
     * @return array|null
     */
    private static function instantiate_rows(false|PDOStatement $stmt): ?array {
        $result = $stmt->fetchAll();

        if (!$result) {
            return null;
        }

        foreach ($result as $row) {
            $company = Company::get_by_id($row["company_id"]);

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
                $row["status"],
                $row["latitude"],
                $row["longitude"]
            );
        }
        return $offers;
    }


    /**
     * getId
     * Returns the id of the Offer
     * @return int
     */
    public function get_id(): int {
        return $this->id;
    }

    /**
     * getCompanyId
     * Returns the id of the Company
     * @return int
     */
    public function get_company_id(): int {
        return $this->company_id;
    }

    /**
     * get_type
     * Returns the type of the Offer
     * @return string
     */
    public function get_type(): string {
        return $this->type;
    }

    /**
     * getTags
     * Returns the tags of the Offer
     * @return array|null
     */
    public function get_tags(): ?array {
        global $db;

        $stmt = $db->prepare("SELECT * FROM Tag JOIN Pending_Tag ON Tag.id = Pending_Tag.tag_id WHERE Pending_Tag.pending_id = :offer_id");
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

    /**
     * get_offer_id
     * Returns the id of the Offer
     * @return int
     */
    public function get_offer_id(): int {
        return $this->offer_id;
    }

    /**
     * get_status
     * Returns the status of the Offer
     * @return string
     */
    public function get_status(): string {
        return $this->status;
    }


    /**
     * get_by_offer_id
     * Returns the Offer with the given id
     * @param int $id
     * @return PendingOffer|null
     */
    public static function get_by_offer_id(int $id): ?PendingOffer {
        global $db;

        $stmt = $db->prepare("SELECT * FROM Pending_Offer WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        $result = $stmt->fetch();

        $company = Company::get_by_id($result["company_id"]);

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
            $result["status"],
            $result["latitude"],
            $result["longitude"]
        );
    }

    /**
     * get_by_offer
     * Returns all the offers of the given Offer id
     * @param int $id
     * @return array|null
     */
    public static function get_by_offer(int $id): ?array {
        global $db;
        $stmt = $db->prepare("SELECT * FROM Pending_Offer WHERE offer_id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        return self::instantiate_rows($stmt);
    }

    /**
     * get_all
     * Returns all the pending offers
     * @return array|null
     */
    public static function get_all(): ?array {
        global $db;

        $stmt = $db->prepare("SELECT * FROM Pending_Offer");
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        $result = $stmt->fetchAll();

        $offers = [];
        foreach ($result as $row) {
            $company = Company::get_by_id($row["company_id"]);

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
                $row["status"],
                $row["latitude"],
                $row["longitude"]
            );
        }

        return $offers;
    }

    /**
     * get_all_new
     * Returns all the new offers
     * @return array|null
     */
    public static function get_all_new(): ?array {
        global $db;

        $stmt = $db->prepare("SELECT * FROM Pending_Offer WHERE type = 'new offer' AND status = 'pending'");
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        $result = $stmt->fetchAll();

        $offers = [];
        foreach ($result as $row) {
            $company = Company::get_by_id($row["company_id"]);

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
                $row["status"],
                $row["latitude"],
                $row["longitude"]
            );
        }

        return $offers;
    }

    /**
     * get_all_updated
     * Returns all the updated offers
     * @return array|null
     */
    public static function get_all_updated(): ?array {
        global $db;

        $stmt = $db->prepare("SELECT * FROM Pending_Offer WHERE type = 'updated offer' AND status = 'pending'");
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        $result = $stmt->fetchAll();

        $offers = [];
        foreach ($result as $row) {
            $company = Company::get_by_id($row["company_id"]);

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
                $row["status"],
                $row["latitude"],
                $row["longitude"]
            );
        }

        return $offers;
    }

    /**
     * create_pending
     * Creates a new pending Offer
     * @param int $company_id
     * @param string $title
     * @param string $description
     * @param string $job
     * @param int $duration
     * @param int $salary
     * @param string $address
     * @param string $education
     * @param string $startDate
     * @param array $tags
     * @param string $email
     * @param string $phone
     * @param string $website
     * @param int $user_id
     * @param int $offer_id
     * @return PendingOffer|null
     */
    public static function create_pending(int $company_id, string $title, string $description, string $job, int $duration, int $salary, string $address, string $education, string $startDate, array $tags, string $email, string $phone, string $website, int $user_id, int $offer_id): ?PendingOffer {
        global $db;

        if ($offer_id == 0) {
            $type = "new offer";
        } else {
            $type = "updated offer";
        }

        $stmt = $db->prepare("INSERT INTO Pending_Offer (user_id, type, offer_id, company_id, title, address, job, description, duration, salary,
                            study_level, email, phone, website, begin_date, latitude, longitude) VALUES (:user_id, :type, :offer_id, :company_id, :title, :address, :job, :description, :duration, :salary,
                            :study_level, :email, :phone, :website, :begin_date, :latitude, :longitude)");
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
        $stmt->bindParam(":latitude", $latitude);
        $stmt->bindParam(":longitude", $longitude);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        $id = $db->lastInsertId();

        foreach ($tags as $tag) {
            $stmt = $db->prepare("INSERT INTO Pending_Tag (tag_id, pending_id) VALUES ((SELECT tag FROM Tag WHERE id = :tag_id), :offer_id)");
            $stmt->bindParam(":tag_id", $tag);
            $stmt->bindParam(":offer_id", $id);
            $stmt->execute();
        }
        $company_id = intval($company_id);
        $company = Company::get_by_id($company_id);
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
            "pending",
            $latitude,
            $longitude
        );

        return $offer;
    }

    /**
     * get_real_duration
     * Get a human-readable duration of the Offer using modulo to get the years, months and days
     * @return string
     */
    public function get_real_duration(): string {
        $duration = $this->get_duration() * 7;

        $years = intdiv($duration, 365);
        $remainingDays = $duration % 365;

        $months = intdiv($remainingDays, 30);
        $remainingDays = $remainingDays % 30;

        $weeks = intdiv($remainingDays, 7);

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

        return rtrim($result, ', ');
    }

    /**
     * get_all_tags
     * Returns all the tags
     * @return array
     */
    public static function get_all_tags(): array {
        global $db;

        $stmt = $db->prepare("SELECT * FROM Tag;");
        $stmt->execute();

        $result = $stmt->fetchAll();

        $tags = [];
        foreach ($result as $row) {
            $tags[] = $row["tag"];
        }

        return $tags;
    }

    /**
     * set_status
     * Sets the status of the Offer
     * @param int $getId
     * @param string $string
     * @return void
     */
    public static function set_status(int $getId, string $string) {
        global $db;

        $stmt = $db->prepare("UPDATE Pending_Offer SET status = :status WHERE id = :id");
        $stmt->bindParam(":status", $string);
        $stmt->bindParam(":id", $getId);
        $stmt->execute();
    }
}