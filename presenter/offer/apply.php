<?php
session_start();

require dirname(__DIR__) . '/../presenter/database.php';

global $db;

$idUser = $_SESSION["user"];
$uploadDir = 'uploads/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $offre = $_POST['offre'];

    if (!isset($_SESSION["user"])) {
        header('Location: ' . $_SERVER["HTTP_REFERER"] ?? "/");
        die();
    } else {
        $stmt = $db->prepare("select * from applications where idUser = :idUser and idOffer = :idOffre");
        $stmt->bindParam(":idUser", $idUser);
        $stmt->bindParam(":idOffre", $offre);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result){
            if (isset($_FILES['cv']) && $_FILES['cv']['error'] === 0) {
                $cvName = basename($_FILES['cv']['name']);
                $cvTmpName = $_FILES['cv']['tmp_name'];
                $cvPath = $uploadDir . uniqid() . '-' . $cvName;

                move_uploaded_file($cvTmpName, $cvPath);
            }

            if (isset($_FILES['motivation']) && $_FILES['motivation']['error'] === 0) {
                $letterName = basename($_FILES['motivation']['name']);
                $letterTmpName = $_FILES['motivation']['tmp_name'];
                $letterPath = $uploadDir . uniqid() . '-' . $letterName;

                move_uploaded_file($letterTmpName, $letterPath);
            }

            if (isset($cvPath) && isset($letterPath)) {
                $stmt = $db->prepare("INSERT INTO applications (idUser, idOffer, cv, motivation_letter) VALUES (:idUser, :idOffer,:cv, :letter)");
                $stmt->bindParam(':idUser', $idUser);
                $stmt->bindParam(':idOffer', $offre);
                $stmt->bindParam(':cv', $cvPath);
                $stmt->bindParam(':letter', $letterPath);
                $stmt->execute();
            }

            header("Location: /view/offer/detail.php?id=$offre&status=success");
        } else {
            header("Location: /view/offer/detail.php?id=$offre&status=already_applied");
        }

        die();
    }
}