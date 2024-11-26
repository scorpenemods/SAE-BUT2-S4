<?php
session_start();
require "../Model/Database.php";
require "../Model/Person.php";

// Set the timezone
date_default_timezone_set('Europe/Paris');

// Set response content type
header('Content-Type: application/json; charset=utf-8');

// Check user session
if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$person = unserialize($_SESSION['user']);
if (!$person instanceof Person) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid session. Please log in again.']);
    exit();
}

$senderId = $person->getId();
$senderName = htmlspecialchars($person->getPrenom() . ' ' . $person->getNom());

// Check request method
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $database = Database::getInstance();

    // Get form data
    $groupId = $_POST['group_id'] ?? null;
    $message = $_POST['message'] ?? '';
    $filePath = '';

    // Validate group ID
    if (!$groupId) {
        echo json_encode(['status' => 'error', 'message' => 'Group ID not specified.']);
        exit();
    }

    // Handle file upload if a file is sent
    if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = "../uploads/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = basename($_FILES['file']['name']);
            $fileTmpPath = $_FILES['file']['tmp_name'];

            // Validate file type
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'gif', 'mp4', 'avi', 'zip', 'rar', 'csv'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($fileExtension, $allowedExtensions)) {
                echo json_encode(['status' => 'error', 'message' => 'File type not allowed.']);
                exit();
            }

            // Validate file size (max 10 MB)
            $maxFileSize = 10 * 1024 * 1024; // 10 MB
            if ($_FILES['file']['size'] > $maxFileSize) {
                echo json_encode(['status' => 'error', 'message' => 'File is too large.']);
                exit();
            }

            // Generate a unique file name
            $newFileName = uniqid('file_', true) . '.' . $fileExtension;
            $filePath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $filePath)) {
                // File uploaded successfully
                if (empty($message)) {
                    $message = "File sent: " . $fileName;
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error uploading file.']);
                exit();
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error uploading file.']);
            exit();
        }
    }

    // Validate message content or file
    if (empty($message) && empty($filePath)) {
        echo json_encode(['status' => 'error', 'message' => 'Message is empty.']);
        exit();
    }

    // Send the message to the database
    $isMessageSent = $database->sendGroupMessage($groupId, $senderId, $message, $filePath);

    if ($isMessageSent) {
        $response = [
            'status'          => 'success',
            'message'         => htmlspecialchars($message),
            'file_path'       => $filePath ? htmlspecialchars(str_replace("../", "/", $filePath)) : null,
            'timestamp'       => date('c'), // ISO 8601 format
            'sender_id'       => $senderId,
            'sender_name'     => $senderName,
            'current_user_id' => $senderId,
        ];
        echo json_encode($response);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error sending message.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}