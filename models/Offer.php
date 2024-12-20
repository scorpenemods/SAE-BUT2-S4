<?php
require dirname(__FILE__) . '/../presenter/database.php';

/**
 * Offer
 * Represents a Offer in the database
 */
class Offer {
    private int $id;
    private int $company_id;
    private Company $company;
    private string $title;
    private string $description;
    private string $job;
    private int $duration;
    private string $begin_date;
    private int $salary;
    private string $address;
    private string $study_level;
    private bool $is_active;
    private string $email;
    private string $phone;
    private string $website;
    private string $created_at;
    private string $updated_at;
    private bool $supress;
    private float $latitude;
    private float $longitude;

    /**
     * __construct
     * Constructor used to instantiate the object, used only internally
     * @param int $id
     * @param int $company_id
     * @param Company $company
     * @param string $title
     * @param string $description
     * @param string $job
     * @param int $duration
     * @param string $begin_date
     * @param int $salary
     * @param string $address
     * @param string $study_level
     * @param bool $is_active
     * @param string $email
     * @param string $phone
     * @param string $website
     * @param string $created_at
     * @param string $updated_at
     * @param bool $supress
     */
    protected function __construct(int $id, int $company_id, Company $company, string $title, string $description, string $job, int $duration, string $begin_date, int $salary, string $address, string $study_level, bool $is_active, string $email, string $phone, string $website, string $created_at, string $updated_at, bool $supress, float $latitude, float $longitude) {
        $this->id = $id;
        $this->company_id = $company_id;
        $this->company = $company;
        $this->title = $title;
        $this->description = $description;
        $this->job = $job;
        $this->duration = $duration;
        $this->begin_date = $begin_date;
        $this->salary = $salary;
        $this->address = $address;
        $this->study_level = $study_level;
        $this->is_active = $is_active;
        $this->email = $email;
        $this->phone = $phone;
        $this->website = $website;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
        $this->supress = $supress;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * instantiateRows
     * Utility function used to instantiate the rows of the result set
     * @param false|PDOStatement $stmt
     * @return array
     */
    private static function instantiateRows(false|PDOStatement $stmt): array {
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
     * getId
     * Returns the id of the offer
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * getCompanyId
     * Returns the id of the company
     * @return int
     */
    public function getCompanyId(): int {
        return $this->company_id;
    }

    /**
     * getCompany
     * Returns the company
     * @return Company
     */
    public function getCompany(): Company {
        return $this->company;
    }

    /**
     * getTitle
     * Returns the title of the offer
     * @return string
     */
    public function getTitle(): string {
        return $this->title;
    }

    /**
     * getDescription
     * Returns the description of the offer
     * @return string
     */
    public function getDescription(): string {
        return $this->description;
    }

    /**
     * getJob
     * Returns the job title of the offer
     * @return string
     */
    public function getJob(): string {
        return $this->job;
    }

    /**
     * getDuration
     * Returns the duration of the offer
     * @return int
     */
    public function getDuration(): int {
        return $this->duration;
    }

    /**
     * getBeginDate
     * Returns the begin date of the offer
     * @return string
     */
    public function getBeginDate(): string {
        return $this->begin_date;
    }

    /**
     * getSalary
     * Returns the salary of the offer
     * @return int
     */
    public function getSalary(): int {
        return $this->salary;
    }

    /**
     * getAddress
     * Returns the address of the offer
     * @return string
     */
    public function getAddress(): string {
        return $this->address;
    }

    /**
     * getStudyLevel
     * Returns the study level of the offer
     * @return string
     */
    public function getStudyLevel(): string {
        return $this->study_level;
    }

    /**
     * getIsActive
     * Returns the is active flag of the offer
     * @return bool
     */
    public function getIsActive(): bool {
        return $this->is_active;
    }

    /**
     * getEmail
     * Returns the email of the offer
     * @return string
     */
    public function getEmail(): string {
        return $this->email;
    }

    /**
     * getPhone
     * Returns the phone of the offer
     * @return string
     */
    public function getPhone(): string {
        return $this->phone;
    }

    /**
     * getCreatedAt
     * Returns the created at date of the offer
     * @return string
     */
    public function getCreatedAt(): string {
        return $this->created_at;
    }

    /**
     * getUpdatedAt
     * Returns the updated at date of the offer
     * @return string
     */
    public function getUpdatedAt(): string {
        return $this->updated_at;
    }

    /**
     * getTags
     * Returns the tags of the offer
     * @return array|null
     */
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

    /**
     * getLatitude
     * Returns the latitude of the offer
     * @return float
     */
    public function getLatitude(): float {
        return $this->latitude;
    }

    /**
     * getLongitude
     * Returns the longitude of the offer
     * @return float
     */
    public function getLongitude(): float {
        return $this->longitude;
    }

    /**
     * getSupress
     * Returns if the offer is supressed
     * @return bool
     */
    public function getSupress(): bool {
        return $this->supress;
    }

    /**
     * update
     * Updates the offer with the given id with the given data in the database
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

        $stmt = $db->prepare("UPDATE offers SET title = :title, description = :description, job = :job, duration = :duration, salary = :salary, address = :address, study_level = :study_level, begin_date = :begin_date, email = :email, phone = :phone, website = :website, latitude = :latitude, longitude = :longitude WHERE id = :id");
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

        $stmt = $db->prepare("DELETE FROM tags_offers WHERE offer_id = :id");
        $stmt->bindParam(":id", $getId);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        foreach ($getTags as $tag) {
            $stmt = $db->prepare("INSERT INTO tags_offers (tag_id, offer_id) VALUES ((SELECT tag FROM tags WHERE id = :tag_id), :offer_id)");
            $stmt->bindParam(":tag_id", $tag);
            $stmt->bindParam(":offer_id", $getId);
            $stmt->execute();
        }

        $offer = Offer::getById($getId);
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
     * getImage
     * Returns the image URL of the website
     * @return string|null
     */
    public function getImage(): ?string {
        $imagePath = 'https://cdn.brandfetch.io/' . $this->getDomain() . '/w/512/h/512';
        return $imagePath;
    }

    /**
     * getById
     * Returns the offer with the given id
     * @param int $id
     * @return Offer|null
     */
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
    public static function getAll(): ?array {
        global $db;

        $stmt = $db->prepare("SELECT * FROM offers");
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        return self::instantiateRows($stmt);
    }

    /**
     * create
     * Creates a new offer, inserts it into the database and returns the id
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

        $stmt = $db->prepare("INSERT INTO offers (company_id, title, description, job , duration, salary, address,  study_level, begin_date,
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
            $stmt = $db->prepare("INSERT INTO tags_offers (tag_id, offer_id) VALUES ((SELECT tag FROM tags WHERE id = :tag_id), :offer_id)");
            $stmt->bindParam(":tag_id", $tag);
            $stmt->bindParam(":offer_id", $id);
            $stmt->execute();
        }

        $offer = new Offer(
            $id,
            $company_id,
            Company::getById($company_id),
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
     * getRealDuration
     * Get a human-readable duration of the offer using modulo to get the years, months and days
     * @return string
     */
    public function getRealDuration(): string {
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

        return rtrim($result, ', ');
    }

    /**
     * getAllTags
     * Returns all the tags
     * @return array
     */
    public static function getAllTags(): array {
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

    /**
     * @return string
     */
    public function getWebsite() {
        return $this->website;
    }

    /**
     * getCompanyOffers
     * Returns all the offers of the given company
     * @param $companyId
     * @return array|null
     */
    public static function getCompanyOffers($companyId): ?array {
        global $db;

        $stmt = $db->prepare("SELECT * FROM offers WHERE company_id = :company_id;");
        $stmt->bindParam(":company_id", $companyId);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        return self::instantiateRows($stmt);
    }

    /**
     * getFilteredOffers
     * Returns the filtered + paginated offers
     * @param int $n
     * @param array $filters
     * @return array|null
     */
    public static function getFilteredOffers(int $n, array $filters): ?array {
        global $db;

        $sql = "SELECT SQL_CALC_FOUND_ROWS offers.*, tag FROM offers LEFT JOIN tags_offers ON offers.id = tags_offers.offer_id LEFT JOIN tags ON tags.id = tags_offers.tag_id WHERE is_active AND begin_date >= CURDATE() AND NOT supress";
        $params = [];

        if (!empty($filters['title'])) {
            preg_match('/title\s*:\s*"(.*?)"/', $filters['title'], $titleMatches);
            if (isset($titleMatches[1])) {
                $sql .= ' AND offers.title LIKE :titleMatch';
                $params[':titleMatch'] = '%' . $titleMatches[1] . '%';
            }

            $filters['title'] = preg_replace('/title\s*:\s*"(.*?)"/', '', $filters['title']);

            preg_match('/description\s*:\s*"(.*?)"/', $filters['title'], $descriptionMatches);
            if (isset($descriptionMatches[1])) {
                $sql .= ' AND offers.description LIKE :descriptionMatch';
                $params[':descriptionMatch'] = '%' . $descriptionMatches[1] . '%';
            }

            $filters['title'] = preg_replace('/description\s*:\s*"(.*?)"/', '', $filters['title']);
            

            $sql .= ' AND offers.title LIKE :title';
            $params[':title'] = '%' . $filters['title'] . '%';
        }

        if (!empty($filters['startDate'])) {
            $sql .= ' AND offers.begin_date >= :startDate';
            $params[':startDate'] = $filters['startDate'];
        }

        if (!empty($filters['diploma'])) {
            $sql .= ' AND offers.study_level = :diploma';
            $params[':diploma'] = $filters['diploma'];
        }

        if (!empty($filters['minSalary'])) {
            $sql .= ' AND offers.salary >= :minSalary';
            $params[':minSalary'] = $filters['minSalary'];
        }

        if (!empty($filters['address'])) {
            $sql .= ' AND offers.address LIKE :address';
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
            $sql .= ' AND offers.job = :sector';
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
            $sql .= ' AND offers.company_id = :company_id';
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
                $offers = pendingOffer::getAllNew();
                return [$offers, ceil(count($offers) / 12)];
            } else if ($filters['type'] == 'updated') {
                $offers = pendingOffer::getAllUpdated();
                return [$offers, ceil(count($offers) / 12)];
            } else if ($filters['type'] == 'inactive') {
                if (!empty($filters['company_id'])) {
                    $offers = offer::getAllInactive($filters['company_id']);
                    return [$offers, ceil(count($offers) / 12)];
                } else {
                    $offers = offer::getAllInactive();
                    return [$offers, ceil(count($offers) / 12)];
                }
            } else if ($filters['type'] == 'suppressed') {
                $offers = offer::getSuppressed();
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
     * Hides the offer with the given id
     * @param $id
     * @return true|null
     */
    public static function hide($id) {
        global $db;

        $stmt = $db->prepare("UPDATE offers SET is_active = !is_active WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        return true;
    }

    /**
     * isCompanyOffer
     * Returns true if the offer with the given id is from the given company
     * @param int $id
     * @param int $company_id
     * @return bool|null
     */
    public static function isCompanyOffer(int $id, int $company_id): ?bool {
        global $db;

        $stmt = $db->prepare("SELECT * FROM offers WHERE id = :id AND company_id = :company_id");
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":company_id", $company_id);
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
     * isAlreadyPending
     * Returns true if the offer with the given id is already pending
     * @param int $id
     * @return bool|null
     */
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

    /**
     * makeFavorite
     * Adds the offer with the given id to the user's favorite offers
     * @param int $id
     * @param int $user_id
     * @return bool|null
     */
    public static function makeFavorite(int $id, int $user_id): ?bool {
        global $db;

        $stmt = $db->prepare("INSERT INTO favorite_offers (offer_id, user_id) VALUES (:offer_id, :user_id)");
        $stmt->bindParam(":offer_id", $id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        return true;
    }

    /**
     * removeFavorite
     * Removes the offer with the given id from the user's favorite offers
     * @param int $id
     * @param int $user_id
     * @return bool|null
     */
    public static function removeFavorite(int $id, int $user_id): ?bool {
        global $db;

        $stmt = $db->prepare("DELETE FROM favorite_offers WHERE offer_id = :offer_id AND user_id = :user_id");
        $stmt->bindParam(":offer_id", $id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        return true;
    }

    /**
     * isFavorite
     * Returns true if the offer with the given id is in the user's favorite offers
     * @param int $id
     * @param int $user_id
     * @return bool|null
     */
    public static function isFavorite(int $id, int $user_id): ?bool {
        global $db;

        $stmt = $db->prepare("SELECT * FROM favorite_offers WHERE offer_id = :offer_id AND user_id = :user_id");
        $stmt->bindParam(":offer_id", $id);
        $stmt->bindParam(":user_id", $user_id);
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
     * getAllInactive
     * Returns all the inactive offers
     * @param int $company_id
     * @return array|null
     */
    public static function getAllInactive(int $company_id = 0): ?array {
        global $db;
        if ($company_id != 0) {
            $stmt = $db->prepare("SELECT * FROM offers WHERE is_active = 0 AND company_id = :company_id ORDER BY begin_date DESC");
            $stmt->bindParam(":company_id", $company_id);
        } else {
            $stmt = $db->prepare("SELECT * FROM offers WHERE is_active = 0 ORDER BY begin_date DESC");
        }
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        return self::instantiateRows($stmt);
    }

    /**
     * suppress
     * Suppress the offer with the given id
     * @param int $id
     * @return void
     */
    public static function suppress(int $id): void {
        global $db;
        $stmt = $db->prepare("UPDATE offers SET supress = 1 WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
    }

    /**
     * getSuppressed
     * Returns all the suppressed offers
     * @return array|null
     */
    public static function getSuppressed(): ?array {
        global $db;
        $stmt = $db->prepare("SELECT * FROM offers WHERE supress = 1");
        $stmt->execute();
        if ($db->errorCode() != 0) {
            return null;
        }
        return self::instantiateRows($stmt);
    }
}