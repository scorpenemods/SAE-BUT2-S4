<?php

session_start();
require dirname(__DIR__) . '/../presenter/database.php';
global $db;

$idUser = $_SESSION["user"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cv = $_FILES['cv'];
    $motivation = $_FILES['motivation'];
    $offre = $_POST['offre'];

    if (!isset($_SESSION["user"])){
        header('Location: /view/offer/list.php'); //devrait renvoyer à l'accueil?
        exit();
    }

    else{
        $stmt = $db->prepare("select * from applications where idUser = :idUser and idOffer = :idOffre");
        $stmt->bindParam(":idUser", $idUser);
        $stmt->bindParam(":idOffre", $offre);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            //ajout dans la table applications
            $stmt = $db->prepare("insert into applications (idUser, idOffer) values (:idUser, :idOffre)");
            $stmt->bindParam(':idUser', $idUser);
            $stmt->bindParam(':idOffre', $offre);
            $stmt->execute();
            header("Location: /view/offer/detail.php?id=$offre&status=success");
            exit();

        }
        else{
            header("Location: /view/offer/detail.php?id=$offre&status=already_applied");
            exit();
        }

    }



}