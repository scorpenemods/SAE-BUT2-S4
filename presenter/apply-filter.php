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

    function getQuery($filters)
    {

        $query = "SELECT id FROM offers";

        if (!empty($filters['sort'])) { //revoir valeurs du filtre
            if ($filters['sort'] == 'recente') {
                $query .= ' ORDER BY created_at DESC';
            }
            if ($filters['sort'] == 'ancienne') {
                $query .= ' ORDER BY created_at ASC';
            }
        }

        if (!empty($filters['startDate'])) {
            $query .= ' WHERE created_at >= "' . $filters['startDate'] . '"';
        }

        if (!empty($filters["diploma"])) {
            $query .= ' AND study_level = "' . $filters["diploma"] . '"';
        }

        if (!empty($filters["minSalary"])) {
            $query .= ' AND salary >= "' . $filters["minSalary"] . '"';
        }

        if (!empty($filters["maxSalary"])) {
            $query .= ' AND salary <= "' . $filters["maxSalary"] . '"';
        }

        if (!empty($filters["city"])) {
            $query .= ' AND address LIKE "%' . $filters["city"] . '%"';
        }

        if (!empty($filters["duration"])) {
            //revoir valeurs du filtre
        }

        if (!empty($filters["sector"])) {
            //revoir le filtre
        }

        if (!empty($filters["keywords"])) {
            //Ya pas les tags dans la classe Offer.php
        }

        return $query;
    }

    function getFilteredOffers($filters){
        $sql = getQuery($filters);
        global $db;

    }

}
