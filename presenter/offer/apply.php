<?php
/*
* apply.php
* Allows the user to apply for an offer.
*/
session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/presenter/database.php';

global $db;

$idUser = $_SESSION["user"];

$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0644, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $offer = $_POST['offre'];

    if (!isset($_SESSION["user"])) {
        echo json_encode(array("status" => "not_logged"));
    } else {
        $stmt = $db->prepare("select * from applications where idUser = :idUser and idOffer = :idOffre");
        $stmt->bindParam(":idUser", $idUser);
        $stmt->bindParam(":idOffre", $offer);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result){
            if (isset($_FILES['cv']) && $_FILES['cv']['error'] === 0) {
                $fileExt = pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION);
                if ($fileExt !== "pdf") {
                    echo json_encode(array("status" => "file_error"));
                }

                $tempName = $_FILES['cv']['tmp_name'];
                $newName = md5($idUser . ":" . $offer . ":cv");

                move_uploaded_file($tempName, $uploadDir . $newName . "." . $fileExt);
            }

            if (isset($_FILES['motivation']) && $_FILES['motivation']['error'] === 0) {
                $fileExt = pathinfo($_FILES['motivation']['name'], PATHINFO_EXTENSION);
                if ($fileExt !== "pdf") {
                    echo json_encode(array("status" => "file_error"));
                }

                $tempName = $_FILES['motivation']['tmp_name'];
                $newName = md5($idUser . ":" . $offer . ":motivation");

                move_uploaded_file($tempName, $uploadDir . $newName . "." .$fileExt);
            }

            $stmt = $db->prepare("INSERT INTO applications (idUser, idOffer) VALUES (:idUser, :idOffer)");
            $stmt->bindParam(':idUser', $idUser);
            $stmt->bindParam(':idOffer', $offer);
            $stmt->execute();

            echo json_encode(array("status" => "success"));
        } else {
            echo json_encode(array("status" => "already_applied"));
        }
    }
}
