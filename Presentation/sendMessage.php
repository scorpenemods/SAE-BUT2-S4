<?php
session_start();
require_once '../Model/Database.php';

$database = new Database();
$senderId = $_SESSION['user_id'];  // Get user's id from session
$receiverId = $_POST['receiver_id'];
$message = $_POST['message'];
$filePath = null;

// Загрузка файла
if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/';
    $fileName = basename($_FILES['file']['name']);
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
        $filePath = $filePath;
    } else {
        echo "Ошибка при загрузке файла.";
    }
}

// Сохранение сообщения в базе данных
if ($database->sendMessage($senderId, $receiverId, $message, $filePath)) {
    header("Location: ../Presentation/professeur.php");  // Перенаправление после успешной отправки
} else {
    echo "Ошибка при отправке сообщения.";
}

$database->closeConnection();
?>