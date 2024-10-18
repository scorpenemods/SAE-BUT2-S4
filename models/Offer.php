<?php

require dirname(__FILE__) . '/../presenter/database.php';

//Class to manage offers
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

    public function __construct(int $id, int $company_id, Company $company, string $title, string $description, string $job, int $duration, string $begin_date, int $salary, string $address, string $study_level, bool $is_active, string $email, string $phone, string $website, string $created_at, string $updated_at) {
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

    public function getBeginDate(): string {
        return $this->begin_date;
    }

    public function getSalary(): int {
        return $this->salary;
    }

    public function getAddress(): string {
        return $this->address;
    }

    public function getStudyLevel(): string {
        return $this->study_level;
    }

    public function getIsActive(): bool {
        return $this->is_active;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getPhone(): string {
        return $this->phone;
    }

    public function getCreatedAt(): string {
        return $this->created_at;
    }

    public function getUpdatedAt(): string
    {
        return $this->updated_at;
    }

    public function getTags(): ?array
    {
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

    //Update an offer
    public static function update(int $getId, string $getTitle, string $getDescription, string $getJob, int $getDuration, int $getSalary, string $getAddress, string $getEducation, string $getBeginDate, ?array $getTags, string $getEmail, string $getPhone, $getFileName, $getFileType, $getFileSize) {
        global $db;

        //Update the offer
        $stmt = $db->prepare("UPDATE offers SET title = :title, description = :description, job = :job, duration = :duration, salary = :salary, address = :address, study_level = :study_level, begin_date = :begin_date, email = :email, phone = :phone WHERE id = :id");
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

        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        //Delete the tags in the tags_offers table
        $stmt = $db->prepare("DELETE FROM tags_offers WHERE offer_id = :id");
        $stmt->bindParam(":id", $getId);
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }

        //Add the tags in the tags_offers table
        foreach ($getTags as $tag) {
            $stmt = $db->prepare("INSERT INTO tags_offers (tag_id, offer_id) VALUES ((SELECT tag FROM tags WHERE id = :tag_id), :offer_id)");
            $stmt->bindParam(":tag_id", $tag);
            $stmt->bindParam(":offer_id", $getId);
            $stmt->execute();
        }

        $offer = Offer::getById($getId);
        return $offer;
    }

    public function getDomain(): ?string {
        $fullDomain = parse_url($this->website, PHP_URL_HOST);

        preg_match('/([a-z0-9-]+\.[a-z]{2,6})$/i', $fullDomain, $matches);
    
        return $matches[1] ?? null;
    }

    public function getImage(): ?string {
        $imagePath = 'https://cdn.brandfetch.io/' . $this->getDomain() . '/w/512/h/512';
        return $imagePath;
    }

    public function getBackgroundColor() {
        $imagePath = $this->getImage();
        $image = imagecreatefromwebp($imagePath);

        $width = imagesx($image);
        $height = imagesy($image);

        $rgb = imagecolorat($image, 0, 0);

        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        imagedestroy($image);
    }

    //Get an offer by its id
    public static function getById(int $id): ?Offer
    {
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
            date("Y-m-d H:i:s", strtotime($result["updated_at"]))
        );
    }

    //Get all offers
    public static function getAll(): ?array
    {
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
                $row["begin_date"],
                $row["salary"],
                $row["address"],
                $row["study_level"],
                $row["is_active"],
                $row["email"],
                $row["phone"],
                $row["website"],
                date("Y-m-d H:i:s", strtotime($row["created_at"])),
                date("Y-m-d H:i:s", strtotime($row["updated_at"]))
            );
        }

        return $offers;
    }

    //Create a new offer
    public static function create(int $company_id, string $title, string $description, string $job, int $duration, int $salary, string $address, string $education, string $begin_date, array $tags, string $email, string $phone, string $website) {
        global $db;

        //Insert the offer in the offers table
        $stmt = $db->prepare("INSERT INTO offers (company_id, title, description, job , duration, salary, address,  study_level, begin_date,
                    email, phone) VALUES (:company_id, :title, :description, :job, :duration, :salary, :address, :study_level, :begin_date,
                    :email, :phone)");
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
        $stmt->execute();

        if ($db->errorCode() != 0) {
            return null;
        }


        $id = $db->lastInsertId();

        //Add the tags in the tags_offers table
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
            date("Y-m-d H:i:s")
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

    //Get all tags
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

    //Get all offers of a company
    public static function getCompanyOffers($companyId): ?array
    {
        global $db;

        $stmt = $db->prepare("SELECT * FROM offers WHERE company_id = :company_id;");
        $stmt->bindParam(":company_id", $companyId);
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
                $row["begin_date"],
                $row["salary"],
                $row["address"],
                $row["study_level"],
                $row["is_active"],
                $row["email"],
                $row["phone"],
                $row["website"],
                date("Y-m-d H:i:s", strtotime($row["created_at"])),
                date("Y-m-d H:i:s", strtotime($row["updated_at"]))
            );
        }

        return $offers;
    }

    //Get all offers filtered by the filters
    public static function getFilteredOffers(int $n, array $filters): ?array {
        global $db;

        $sql = "SELECT * FROM offers WHERE is_active AND begin_date >= CURDATE()";
        $params = [];

        if (!empty($filters['title'])) {
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

        if (!empty($filters['maxSalary'])) {
            $sql .= ' AND offers.salary <= :maxSalary';
            $params[':maxSalary'] = $filters['maxSalary'];
        }

        if (!empty($filters['city'])) {
            $sql .= ' AND offers.address LIKE :city';
            $params[':city'] = '%' . $filters['city'] . '%';
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

/*
        if (!empty($filters['keywords'])) {
            $sql .= ' AND tags.name LIKE :keywords';
            $params[':keywords'] = '%' . $filters['keywords'] . '%';
        }
*/

        if (!empty($filters['company_id'])) {
            $sql .= ' AND offers.company_id = :company_id';
            $params[':company_id'] = $filters['company_id'];
        }

        if (!empty($filters['type'])) {
            if ($filters['type'] == 'new') {
                return pendingOffer::getAllNew();
            } else if ($filters['type'] == 'updated') {
                return pendingOffer::getAllUpdated();
            }
        }

        $sql .= " LIMIT 12 OFFSET ". ($n - 1) * 12;

        $stmt = $db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

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
                $row["begin_date"],
                $row["salary"],
                $row["address"],
                $row["study_level"],
                $row["is_active"],
                $row["email"],
                $row["phone"],
                $row["website"],
                date("Y-m-d H:i:s", strtotime($row["created_at"])),
                date("Y-m-d H:i:s", strtotime($row["updated_at"]))
            );
        }

        return $offers;
    }

    //Hide an offer
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
}