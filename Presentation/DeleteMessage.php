<?php
require "../Model/Database.php";
require "../Model/Person.php";

session_start();

if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    if ($person instanceof Person) {
        $currentUserId = $person->getUserId();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid user session.']);
        exit();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_id'], $_POST['message_type'])) {
    $messageId = $_POST['message_id'];
    $messageType = $_POST['message_type'];

    $database = Database::getInstance();

    if ($messageType === 'private') {
        // Handle deletion of private message
        $message = $database->getMessageById($messageId);

        if ($message && $message['sender_id'] == $currentUserId) {
            if ($database->deleteMessage($messageId)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error deleting message from database.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'You do not have permission to delete this message.']);
        }
    } elseif ($messageType === 'group') {
        // Handle deletion of group message
        $stmt = $database->getConnection()->prepare("SELECT * FROM MessageGroupe WHERE id = :message_id");
        $stmt->bindParam(':message_id', $messageId, PDO::PARAM_INT);
        $stmt->execute();
        $message = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($message && $message['sender_id'] == $currentUserId) {
            // Delete the message
            $deleteStmt = $database->getConnection()->prepare("DELETE FROM MessageGroupe WHERE id = :message_id");
            $deleteStmt->bindParam(':message_id', $messageId, PDO::PARAM_INT);
            if ($deleteStmt->execute()) {
                // Optionally, delete any associated files
                // Check if there is a file associated
                $fileStmt = $database->getConnection()->prepare("
                    SELECT d.id AS document_id, d.filepath
                    FROM Document_Message dm
                    JOIN Document d ON dm.document_id = d.id
                    WHERE dm.message_id = :message_id
                ");
                $fileStmt->bindParam(':message_id', $messageId, PDO::PARAM_INT);
                $fileStmt->execute();
                $file = $fileStmt->fetch(PDO::FETCH_ASSOC);

                if ($file) {
                    // Delete the file from the server
                    $filePath = $file['filepath'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    // Delete the file record from the database
                    $deleteFileStmt = $database->getConnection()->prepare("DELETE FROM Document WHERE id = :document_id");
                    $deleteFileStmt->bindParam(':document_id', $file['document_id'], PDO::PARAM_INT);
                    $deleteFileStmt->execute();
                }

                // Delete the record from Document_Message
                $deleteDMStmt = $database->getConnection()->prepare("DELETE FROM Document_Message WHERE message_id = :message_id");
                $deleteDMStmt->bindParam(':message_id', $messageId, PDO::PARAM_INT);
                $deleteDMStmt->execute();

                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error deleting message from database.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'You do not have permission to delete this message.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid message type.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Message ID or type not provided.']);
}
