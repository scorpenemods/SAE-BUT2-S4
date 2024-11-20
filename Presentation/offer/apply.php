<?php
session_start();

require dirname(__DIR__) . '/../Presentation/database.php';

global $db;

$idUser = $_SESSION["user"];
$uploadDir = 'uploads/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $offer = $_POST['offre'];

    if (!isset($_SESSION["user"])) {
        header('Location: ' . $_SERVER["HTTP_REFERER"] ?? "/");
        die();
    } else {
        $stmt = $db->getConnection()->prepare("select * from Application where idUser = :idUser and idOffer = :idOffre");
        $stmt->bindParam(":idUser", $idUser);
        $stmt->bindParam(":idOffre", $offer);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result){
            if (isset($_FILES['cv']) && $_FILES['cv']['error'] === 0) {
                $fileExt = pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION);
                if ($fileExt !== "pdf") {
                    header("Location: /View/offer/detail.php?id=$offer&status=file_error");
                    die();
                }

                $tempName = $_FILES['cv']['tmp_name'];
                $newName = md5($idUser . ":" . $offer . ":cv");

                move_uploaded_file($tempName, $uploadDir . $newName . "." . $fileExt);
            }

            if (isset($_FILES['motivation']) && $_FILES['motivation']['error'] === 0) {
                $fileExt = pathinfo($_FILES['motivation']['name'], PATHINFO_EXTENSION);
                if ($fileExt !== "pdf") {
                    header("Location: /View/offer/detail.php?id=$offer&status=file_error");
                    die();
                }

                $tempName = $_FILES['motivation']['tmp_name'];
                $newName = md5($idUser . ":" . $offer . ":motivation");

                move_uploaded_file($tempName, $uploadDir . $newName . "." .$fileExt);
            }

            $stmt = $db->getConnection()->prepare("INSERT INTO Application (idUser, idOffer) VALUES (:idUser, :idOffer)");
            $stmt->bindParam(':idUser', $idUser);
            $stmt->bindParam(':idOffer', $offer);
            $stmt->execute();

            header("Location: /View/offer/detail.php?id=$offer&status=success");
        } else {
            header("Location: /View/offer/detail.php?id=$offer&status=already_applied");
        }

        die();
    }
}