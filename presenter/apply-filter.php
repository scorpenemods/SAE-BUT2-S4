<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $sort = $_POST["sort"];
    $startDate = $_POST["startDate"];
    $diploma = $_POST["diploma"];
    $minSalary = $_POST["minSalary"];
    $maxSalary = $_POST["maxSalary"];
    $city = $_POST["city"];
    $duration = $_POST["duration"];
    $sector = $_POST["sector"];
    $keywords = $_POST["keywords"];

    $filters = array(
        "sort" => $sort,
        "startDate" => $startDate,
        "diploma" => $diploma,
        "minSalary" => $minSalary,
        "maxSalary" => $maxSalary,
        "city" => $city,
        "duration" => $duration,
        "sector" => $sector,
        "keywords" => $keywords
    );

    function getQuery($filters): array
    {

        $query = "SELECT DISTINCT offers.id
              FROM offers 
              LEFT JOIN tags_offers ON offers.id = tags_offers.offer_id
              LEFT JOIN tags  ON tags_offers.tag_id = tags.id
              WHERE 1=1";

        $params = [];

        if (!empty($filters['startDate'])) {
            $query .= ' AND offers.begin_date >= :startDate';
            $params[':startDate'] = $filters['startDate'];
        }

        if (!empty($filters['diploma'])) {
            $query .= ' AND offers.study_level = :diploma';
            $params[':diploma'] = $filters['diploma'];
        }

        if (!empty($filters['minSalary'])) {
            $query .= ' AND offers.salary >= :minSalary';
            $params[':minSalary'] = $filters['minSalary'];
        }

        if (!empty($filters['maxSalary'])) {
            $query .= ' AND offers.salary <= :maxSalary';
            $params[':maxSalary'] = $filters['maxSalary'];
        }

        if (!empty($filters['city'])) {
            $query .= ' AND offers.address LIKE :city';
            $params[':city'] = '%' . $filters['city'] . '%';
        }

        if (!empty($filters['duration'])) {
            $query .= ' AND duration = :duration';
            $params[':duration'] = $filters['duration'];
        }

        if (!empty($filters['sector'])) {
            $query .= ' AND offers.job = :sector';
            $params[':sector'] = $filters['sector'];
        }

        if (!empty($filters['keywords'])) {
            $query .= ' AND tags.name LIKE :keywords';
            $params[':keywords'] = '%' . $filters['keywords'] . '%';
        }

        if (!empty($filters['sort'])) {
            if ($filters['sort'] == 'recente') {
                $query .= ' ORDER BY offers.created_at DESC';
            } elseif ($filters['sort'] == 'ancienne') {
                $query .= ' ORDER BY offers.created_at ASC';
            }
        }

        return [$query, $params];
    }


    function getFilteredOffers($filters) {
        global $db;
        list($sql, $params) = getQuery($filters);
        $stmt = $db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



}
?>
