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

    if (!isset($_SESSION["user"])){
        header('Location: /view/offer/list.php'); //devrait renvoyer à la page de connexion
        exit();
    }
    else{
        //on verifie si la personne à déjà postuler avant
        $stmt = $db->prepare("select * from applications where idUser = :idUser and idOffer = :idOffre");
        $stmt->bindParam(":idUser", $idUser);
        $stmt->bindParam(":idOffre", $offre);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        //si elle n'a pas postuler:
        if (!$result){

            //cv
            if (isset($_FILES['cv']) && $_FILES['cv']['error'] === 0) {
                $cvName = basename($_FILES['cv']['name']);
                $cvTmpName = $_FILES['cv']['tmp_name'];
                $cvPath = $uploadDir . uniqid() . '-' . $cvName;

                // Déplacement du fichier vers le dossier de destination
                move_uploaded_file($cvTmpName, $cvPath);

            }

            //lettre de motivation
            if (isset($_FILES['motivation']) && $_FILES['motivation']['error'] === 0) {
                $lettreName = basename($_FILES['motivation']['name']);
                $lettreTmpName = $_FILES['motivation']['tmp_name'];
                $lettrePath = $uploadDir . uniqid() . '-' . $lettreName;

                // Déplacement du fichier vers le dossier de destination
                move_uploaded_file($lettreTmpName, $lettrePath);
            }

            if (isset($cvPath) && isset($lettrePath)) {
                $stmt = $db->prepare("INSERT INTO applications (idUser, idOffer, cv, motivation_letter) VALUES (:idUser, :idOffer,:cv, :lettre)");
                $stmt->bindParam(':idUser', $idUser);
                $stmt->bindParam(':idOffer', $offre);
                $stmt->bindParam(':cv', $cvPath);
                $stmt->bindParam(':lettre', $lettrePath);
                $stmt->execute();
            }

            header("Location: /view/offer/detail.php?id=$offre&status=success");
            exit();
        }

        //si la personne a postulé avant
        else{
            header("Location: /view/offer/detail.php?id=$offre&status=already_applied");
            exit();
        }
    }
}