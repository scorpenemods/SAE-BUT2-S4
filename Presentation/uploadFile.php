<?php
session_start();
require "../Model/Database.php";

if (isset($_FILES['file']) && isset($_POST['receiver_id'])) {
    $database = new Database();
    $senderId = $_SESSION['user_id'] ?? null;
    $receiverId = $_POST['receiver_id'];

    if (!$senderId || !$receiverId) {
        echo json_encode(['status' => 'error', 'message' => 'ID отправителя или получателя не установлены.']);
        exit();
    }

    $uploadDir = "../uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = basename($_FILES['file']['name']);
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
        $message = "Файл загружен: $fileName";
        $database->sendMessage($senderId, $receiverId, $message, $filePath);
        echo json_encode(['status' => 'success', 'file_path' => $filePath, 'timestamp' => date("H:i")]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Ошибка при загрузке файла.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Файл или ID получателя не были отправлены.']);
}
?>