<?php
require_once dirname(__FILE__) . "/Database.php";
$db = Database::getInstance();
/**
 * Offer
 * Represents a Offer in the database
 */
class Offer {
    private int $id;
    private int $companyId;
    private Company $company;
    private string $title;
    private string $description;
    private string $job;
    private int $duration;
    private string $beginDate;
    private int $salary;
    private string $address;
    private string $studyLevel;
    private bool $isActive;
    private string $email;
    private string $phone;
    private string $website;
    private string $createdAt;
    private string $updatedAt;
    private bool $supress;
    private float $latitude;
    private float $longitude;

    /**
     * __construct
     * Constructor used to instantiate the object, used only internally
     * @param int $id
     * @param int $companyId
     * @param Company $company
     * @param string $title
     * @param string $description
     * @param string $job
     * @param int $duration
     * @param string $beginDate
     * @param int $salary
     * @param string $address
     * @param string $studyLevel
     * @param bool $isActive
     * @param string $email
     * @param string $phone
     * @param string $website
     * @param string $createdAt
     * @param string $updatedAt
     * @param bool $supress
     */
    protected function __construct(int $id, int $companyId, Company $company, string $title, string $description, string $job, int $duration, string $beginDate, int $salary, string $address, string $studyLevel, bool $isActive, string $email, string $phone, string $website, string $createdAt, string $updatedAt, bool $supress, float $latitude, float $longitude) {
        $this->id = $id;
        $this->companyId = $companyId;
        $this->company = $company;
        $this->title = $title;
        $this->description = $description;
        $this->job = $job;
        $this->duration = $duration;
        $this->beginDate = $beginDate;
        $this->salary = $salary;
        $this->address = $address;
        $this->studyLevel = $studyLevel;
        $this->isActive = $isActive;
        $this->email = $email;
        $this->phone = $phone;
        $this->website = $website;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->supress = $supress;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * instantiate_rows
     * Utility function used to instantiate the rows of the result set
     * @param false|PDOStatement $stmt
     * @return array
     */
    private static function instantiate_rows(false|PDOStatement $stmt): array {
        $result = $stmt->fetchAll();

        $offers = [];
        foreach ($result as $row) {
            $company = Company::get_by_id($row["company_id"]);

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
                $row["begin_date"],
                $row["salary"],
                $row["address"],
                $row["study_level"],
                $row["is_active"],
                $row["email"],
                $row["phone"],
                $row["website"],
                date("Y-m-d H:i:s", strtotime($row["created_at"])),
                date("Y-m-d H:i:s", strtotime($row["updated_at"])),
                $row["supress"],
                $row["latitude"],
                $row["longitude"]
            );
        }

        return $offers;
    }

    /**
     * get_id
     * Returns the id of the Offer
     * @return int
     */
    public function get_id(): int {
        return $this->id;
    }

    /**
     * get_company_id
     * Returns the id of the Company
     * @return int
     */
    public function get_company_id(): int {
        return $this->companyId;
    }

    /**
     * get_company
     * Returns the Company
     * @return Company
     */
    public function get_company(): Company {
        return $this->company;
    }

    /**
     * get_title
     * Returns the title of the Offer
     * @return string
     */
    public function get_title(): string {
        return $this->title;
    }

    /**
     * get_description
     * Returns the description of the Offer
     * @return string
     */
    public function get_description(): string {
        return $this->description;
    }

    /**
     * get_job
     * Returns the job title of the Offer
     * @return string
     */
    public function get_job(): string {
        return $this->job;
    }

    /**
     * get_duration
     * Returns the duration of the Offer
     * @return int
     */
    public function get_duration(): int {
        return $this->duration;
    }

    /**
     * get_begin_date
     * Returns the begin date of the Offer
     * @return string
     */
    public function get_begin_date(): string {
        return $this->beginDate;
    }

    /**
     * get_salary
     * Returns the salary of the Offer
     * @return int
     */
    public function get_salary(): int {
        return $this->salary;
    }

    /**
     * get_address
     * Returns the address of the Offer
     * @return string
     */
    public function get_address(): string {
        return $this->address;
    }

    /**
     * get_study_level
     * Returns the study level of the Offer
     * @return string
     */
    public function get_study_level(): string {
        return $this->studyLevel;
    }

    /**
     * get_is_active
     * Returns the is active flag of the Offer
     * @return bool
     */
    public function get_is_active(): bool {
        return $this->isActive;
    }

    /**
     * get_email
     * Returns the email of the Offer
     * @return string
     */
    public function get_email(): string {
        return $this->email;
    }

    /**
     * get_phone
     * Returns the phone of the Offer
     * @return string
     */
    public function get_phone(): string {
        return $this->phone;
    }

    /**
     * get_created_at
     * Returns the created at date of the Offer
     * @return string
     */
    public function get_created_at(): string {
        return $this->createdAt;
    }

    /**
     * get_updated_at
     * Returns the updated at date of the Offer
     * @return string
     */
    public function get_updated_at(): string {
        return $this->updatedAt;
    }

    /**
     * get_tags
     * Returns the tags of the Offer
     * @return array|null
     */
    public function get_tags(): ?array {
        global $db;

        $stmt = $db->prepare("SELECT * FROM tags JOIN Tag_Offer ON Tag.id = Tag_Offer.tag_id WHERE Tag_Offer.offer_id = :offer_id");
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
     * get_latitude
     * Returns the latitude of the Offer
     * @return float
     */
    public function get_latitude(): float {
        return $this->latitude;
    }

    /**
     * get_longitude
     * Returns the longitude of the Offer
     * @return float
     */
    public function get_longitude(): float {
        return $this->longitude;
    }

    /**
     * get_supress
     * Returns if the Offer is supressed
     * @return bool
     */
    public function get_supress(): bool {
        return $this->supress;
    }

    /**
     * update
     * Updates the Offer with the given id with the given data in the database
     * @param int $getId
     * @param string $getTitle
     * @param string $getDescription
     * @param string $getJob
     * @param int $getDuration
     * @param int $getSalary
     * @param string $getAddress
     * @param string $getEducation
     * @param string $getBeginDate
     * @param array|null $getTags
     * @param string $getEmail
     * @param string $getPhone
     * @param string $getWebsite
     * @return Offer|null
     */
    public static function update(int $getId, string $getTitle, string $getDescription, string $getJob, int $getDuration, int $getSalary, string $getAddress, string $getEducation, string $getBeginDate, ?array $getTags, string $getEmail, string $getPhone, string $getWebsite, float $latitude, float $longitude) {
        global $db;

        $stmt = $db->prepare("UPDATE Offer SET title = :title, description = :description, job = :job, duration = :duration, salary = :salary, address = :address, study_level = :study_level, begin_date = :begin_date, email = :email, phone = :phone, website = :website, latitude = :latitude, longitude = :longitude WHERE id = :id");
        $stmt->bindParam(":title", $getTitle);
        $stmt->bindParam(":description", $getDescription);
        $stmt->bindParam(":job", $getJob);
        $stmt->bindParam(":duration", $getDuration);
        $stmt->bindParam(":salary", $getSalary);
        $stmt->bindParam(":address", $getAddress);
        $stmt->bindParam(":study_level", $getEducation);
        $stmt->bindParam(":email", $getEmail);
        $stmt->bindParam(":phone", $getPhone);
        $stmt->bindParam(":id", $getId);
        $stmt->bindParam(":begin_date", $getBeginDate);
        $stmt->bindParam(":website", $getWebsite);
        $stmt->bindParam(":latitude", $latitude);
        $stmt->bindParam(":longitude", $longitude);

        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        $stmt = $db->prepare("DELETE FROM Tag_Offer WHERE offer_id = :id");
        $stmt->bindParam(":id", $getId);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        foreach ($getTags as $tag) {
            $stmt = $db->prepare("INSERT INTO Tag_Offer (tag_id, offer_id) VALUES ((SELECT tag FROM Tag WHERE id = :tag_id), :offer_id)");
            $stmt->bindParam(":tag_id", $tag);
            $stmt->bindParam(":offer_id", $getId);
            $stmt->execute();
        }

        $offer = Offer::get_by_id($getId);
        return $offer;
    }

    /**
     * getDomain
     * Returns the domain of the website
     * @return string|null
     */
    public function getDomain(): ?string {
        $fullDomain = parse_url($this->website, PHP_URL_HOST);

        preg_match('/([a-z0-9-]+\.[a-z]{2,6})$/i', $fullDomain, $matches);
    
        return $matches[1] ?? null;
    }

    /**
     * get_image
     * Returns the image URL of the website
     * @return string|null
     */
    public function get_image(): ?string {
        $imagePath = 'https://cdn.brandfetch.io/' . $this->getDomain() . '/w/512/h/512';
        return $imagePath;
    }

    /**
     * getById
     * Returns the Offer with the given id
     * @param int $id
     * @return Offer|null
     */
    public static function get_by_id(int $id): ?Offer {
        global $db;

        $stmt = $db->prepare("SELECT * FROM Offer WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $result = $stmt->fetch();

        if ($db->errorCode() != 0) {
            return null;
        }

        $company = Company::get_by_id($result["company_id"]);

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
            $result["begin_date"],
            $result["salary"],
            $result["address"],
            $result["study_level"],
            $result["is_active"],
            $result["email"],
            $result["phone"],
            $result["website"],
            date("Y-m-d H:i:s", strtotime($result["created_at"])),
            date("Y-m-d H:i:s", strtotime($result["updated_at"])),
            $result["supress"],
            $result["latitude"],
            $result["longitude"]
        );
    }

    /**
     * getAll
     * Returns all the offers
     * @return array|null
     */
    public static function get_all(): ?array {
        global $db;

        $stmt = $db->prepare("SELECT * FROM Offer");
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        return self::instantiate_rows($stmt);
    }

    /**
     * create
     * Creates a new Offer, inserts it into the database and returns the id
     * @param int $company_id
     * @param string $title
     * @param string $description
     * @param string $job
     * @param int $duration
     * @param int $salary
     * @param string $address
     * @param string $education
     * @param string $begin_date
     * @param array $tags
     * @param string $email
     * @param string $phone
     * @param string $website
     * @return Offer|null
     */
    public static function create(int $company_id, string $title, string $description, string $job, int $duration, int $salary, string $address, string $education, string $begin_date, array $tags, string $email, string $phone, string $website, float $latitude, float $longitude) {
        global $db;

        $stmt = $db->prepare("INSERT INTO Offer (company_id, title, description, job , duration, salary, address,  study_level, begin_date,
                    email, phone, website, latitude, longitude) VALUES (:company_id, :title, :description, :job, :duration, :salary, :address, :study_level, :begin_date,
                    :email, :phone, :website, :latitude, :longitude)");
        $stmt->bindParam(":company_id", $company_id);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":job", $job);
        $stmt->bindParam(":duration", $duration);
        $stmt->bindParam(":salary", $salary);
        $stmt->bindParam(":address", $address);
        $stmt->bindParam(":study_level", $education);
        $stmt->bindParam(":begin_date", $begin_date);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":website", $website);
        $stmt->bindParam(":latitude", $latitude);
        $stmt->bindParam(":longitude", $longitude);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }


        $id = $db->lastInsertId();

        foreach ($tags as $tag) {
            $stmt = $db->prepare("INSERT INTO Tag_Offer (tag_id, offer_id) VALUES ((SELECT tag FROM tags WHERE id = :tag_id), :offer_id)");
            $stmt->bindParam(":tag_id", $tag);
            $stmt->bindParam(":offer_id", $id);
            $stmt->execute();
        }

        $offer = new Offer(
            $id,
            $company_id,
            Company::get_by_id($company_id),
            $title,
            $description,
            $job,
            $duration,
            $begin_date,
            $salary,
            $address,
            $education,
            TRUE,
            $email,
            $phone,
            $website,
            date("Y-m-d H:i:s"),
            date("Y-m-d H:i:s"),
            FALSE,
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
        $duration = $this->get_duration();

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
     * get_website
     *
     * @return string
     */
    public function get_website() {
        return $this->website;
    }

    /**
     * get_company_offers
     * Returns all the offers of the given Company
     * @param $companyId
     * @return array|null
     */
    public static function get_company_offers($companyId): ?array {
        global $db;

        $stmt = $db->prepare("SELECT * FROM Offer WHERE company_id = :company_id;");
        $stmt->bindParam(":company_id", $companyId);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        return self::instantiate_rows($stmt);
    }

    /**
     * get_filtered_offers
     * Returns the filtered + paginated offers
     * @param int $n
     * @param array $filters
     * @return array|null
     */
    public static function get_filtered_offers(int $n, array $filters): ?array {
        global $db;

        $sql = "SELECT SQL_CALC_FOUND_ROWS Offer.*, tag FROM Offer LEFT JOIN Tag_Offer ON Offer.id = Tag_Offer.offer_id LEFT JOIN Tag ON Tag.id = Tag_Offer.tag_id WHERE is_active AND begin_date >= CURDATE() AND NOT supress";
        $params = [];

        if (!empty($filters['title'])) {
            preg_match('/title\s*:\s*"(.*?)"/', $filters['title'], $titleMatches);
            if (isset($titleMatches[1])) {
                $sql .= ' AND Offer.title LIKE :titleMatch';
                $params[':titleMatch'] = '%' . $titleMatches[1] . '%';
            }

            $filters['title'] = preg_replace('/title\s*:\s*"(.*?)"/', '', $filters['title']);

            preg_match('/description\s*:\s*"(.*?)"/', $filters['title'], $descriptionMatches);
            if (isset($descriptionMatches[1])) {
                $sql .= ' AND Offer.description LIKE :descriptionMatch';
                $params[':descriptionMatch'] = '%' . $descriptionMatches[1] . '%';
            }

            $filters['title'] = preg_replace('/description\s*:\s*"(.*?)"/', '', $filters['title']);
            

            $sql .= ' AND Offer.title LIKE :title';
            $params[':title'] = '%' . $filters['title'] . '%';
        }

        if (!empty($filters['startDate'])) {
            $sql .= ' AND Offer.begin_date >= :startDate';
            $params[':startDate'] = $filters['startDate'];
        }

        if (!empty($filters['diploma'])) {
            $sql .= ' AND Offer.study_level = :diploma';
            $params[':diploma'] = $filters['diploma'];
        }

        if (!empty($filters['minSalary'])) {
            $sql .= ' AND Offer.salary >= :minSalary';
            $params[':minSalary'] = $filters['minSalary'];
        }

        if (!empty($filters['address'])) {
            $sql .= ' AND Offer.address LIKE :address';
            $params[':address'] = '%' . $filters['address'] . '%';
        }

        if (!empty($filters['duration'])) {
            switch ($filters['duration']) {
                case 1:
                    $sql .= ' AND duration >= 1 AND duration <= 3';
                    break;
                case 2:
                    $sql .= ' AND duration >= 3 AND duration <= 6';
                    break;
                case 3:
                    $sql .= ' AND duration >= 6';
                    break;
            }
        }

        if (!empty($filters['sector'])) {
            $sql .= ' AND Offer.job = :sector';
            $params[':sector'] = $filters['sector'];
        }

        if (!empty($filters['keywords'])) {
            $keywords = array_filter(array_map('trim', explode(',', $filters['keywords'])));

            foreach ($keywords as $key => $keyword) {
                $sql .= ' AND (tag LIKE :keyword' . $key . ')';
                $params[':keyword' . $key] = '%' . $keyword . '%';
            }
        }

        if (!empty($filters['company_id'])) {
            $sql .= ' AND Offer.company_id = :company_id';
            $params[':company_id'] = $filters['company_id'];
        }

        if (!empty($filters['latitude']) && !empty($filters['longitude']) && !empty($filters['distance'])) {
            $sql .= ' AND (6371 * acos(cos(radians(:latitude)) * cos(radians(offers.latitude)) * cos(radians(offers.longitude) - radians(:longitude)) + sin(radians(:latitude)) * sin(radians(offers.latitude)))) < :distance';
            $params[':longitude'] = $filters['longitude'];
            $params[':latitude'] = $filters['latitude'];
            $params[':distance'] = $filters['distance'];
        }

        if (!empty($filters['type'])) {
            if ($filters['type'] == 'new') {
                $offers = pendingOffer::get_all_new();
                return [$offers, ceil(count($offers) / 12)];
            } else if ($filters['type'] == 'updated') {
                $offers = pendingOffer::get_all_updated();
                return [$offers, ceil(count($offers) / 12)];
            } else if ($filters['type'] == 'inactive') {
                if (!empty($filters['company_id'])) {
                    $offers = offer::get_all_inactive($filters['company_id']);
                    return [$offers, ceil(count($offers) / 12)];
                } else {
                    $offers = offer::get_all_inactive();
                    return [$offers, ceil(count($offers) / 12)];
                }
            } else if ($filters['type'] == 'suppressed') {
                $offers = offer::get_suppressed();
                return [$offers, ceil(count($offers) / 12)];
            }
        }

        $sql .= " LIMIT 12 OFFSET ". ($n - 1) * 12;

        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        $stmt2 = $db->query("SELECT FOUND_ROWS() as total");
        $count = $stmt2->fetch()['total'];

        $offers = [];
        foreach ($stmt->fetchAll() as $row) {
            $company = Company::get_by_id($row["company_id"]);

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
                $row["begin_date"],
                $row["salary"],
                $row["address"],
                $row["study_level"],
                $row["is_active"],
                $row["email"],
                $row["phone"],
                $row["website"],
                date("Y-m-d H:i:s", strtotime($row["created_at"])),
                date("Y-m-d H:i:s", strtotime($row["updated_at"])),
                $row["supress"],
                $row["latitude"],
                $row["longitude"]
            );
        }

        return [$offers, ceil($count / 12)];
    }

    /**
     * hide
     * Hides the Offer with the given id
     * @param $id
     * @return true|null
     */
    public static function hide($id) {
        global $db;

        $stmt = $db->prepare("UPDATE Offer SET is_active = !is_active WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        return true;
    }

    /**
     * is_company_offer
     * Returns true if the Offer with the given id is from the given Company
     * @param int $id
     * @param int $company_id
     * @return bool|null
     */
    public static function is_company_offer(int $id, int $companyId): ?bool {
        global $db;

        $stmt = $db->prepare("SELECT * FROM Offer WHERE id = :id AND company_id = :company_id");
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":company_id", $companyId);
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

    /**
     * is_already_pending
     * Returns true if the Offer with the given id is already pending
     * @param int $id
     * @return bool|null
     */
    public static function is_already_pending(int $id): ?bool {
        global $db;

        $stmt = $db->prepare("SELECT * FROM Pending_Offer WHERE offer_id = :offer_id AND status = 'Pending'");
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

    /**
     * make_favorite
     * Adds the Offer with the given id to the user's favorite offers
     * @param int $id
     * @param int $userId
     * @return bool|null
     */
    public static function make_favorite(int $id, int $userId): ?bool {
        global $db;

        $stmt = $db->prepare("INSERT INTO Favorite_Offer (offer_id, user_id) VALUES (:offer_id, :user_id)");
        $stmt->bindParam(":offer_id", $id);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        return true;
    }

    /**
     * remove_favorite
     * Removes the Offer with the given id from the user's favorite offers
     * @param int $id
     * @param int $userId
     * @return bool|null
     */
    public static function remove_favorite(int $id, int $userId): ?bool {
        global $db;

        $stmt = $db->prepare("DELETE FROM Favorite_Offer WHERE offer_id = :offer_id AND user_id = :user_id");
        $stmt->bindParam(":offer_id", $id);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        return true;
    }

    /**
     * is_favorite
     * Returns true if the Offer with the given id is in the user's favorite offers
     * @param int $id
     * @param int $user_id
     * @return bool|null
     */
    public static function is_favorite(int $id, int $userId): ?bool {
        global $db;

        $stmt = $db->prepare("SELECT * FROM Favorite_Offer WHERE offer_id = :offer_id AND user_id = :user_id");
        $stmt->bindParam(":offer_id", $id);
        $stmt->bindParam(":user_id", $userId);
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

    /**
     * get_all_inactive
     * Returns all the inactive offers
     * @param int $companyId
     * @return array|null
     */
    public static function get_all_inactive(int $companyId = 0): ?array {
        global $db;
        if ($companyId != 0) {
            $stmt = $db->prepare("SELECT * FROM Offer WHERE is_active = 0 AND company_id = :company_id ORDER BY begin_date DESC");
            $stmt->bindParam(":company_id", $companyId);
        } else {
            $stmt = $db->prepare("SELECT * FROM Offer WHERE is_active = 0 ORDER BY begin_date DESC");
        }
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        return self::instantiate_rows($stmt);
    }

    /**
     * suppress
     * Suppress the Offer with the given id
     * @param int $id
     * @return void
     */
    public static function suppress(int $id): void {
        global $db;
        $stmt = $db->prepare("UPDATE Offer SET supress = 1 WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
    }

    /**
     * get_suppressed
     * Returns all the suppressed offers
     * @return array|null
     */
    public static function get_suppressed(): ?array {
        global $db;
        $stmt = $db->prepare("SELECT * FROM Offer WHERE supress = 1");
        $stmt->execute();
        if ($db->errorCode() != 0) {
            return null;
        }
        return self::instantiate_rows($stmt);
    }
}