<?php
class Database
{
    private $connection;

    public function __construct()
    {
        $this->connect();
    }

    private function connect(): void
    {
        try {
            require_once __DIR__ . '/Config.php';
            $this->connection = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection error: " . $e->getMessage();
            exit;
        }
    }

    // User login verification
    public function verifyLogin($email, $password)
    {
        $sql = "SELECT User.*, Password.password_hash FROM User
            JOIN Password ON User.id = Password.user_id
            WHERE User.email = :email AND Password.actif = 1";

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $result = $stmt->fetch();

            if ($result && password_verify($password, $result['password_hash'])) {
                if (!$result['valid_email']) {
                    return ['status' => 1, 'user' => $result];
                }
                if ($result['status'] !== 'active') {
                    return ['status' => 'pending', 'user' => $result];
                }
                return ['status' => 'success', 'user' => $result];
            }
            return ['status' => 'failed'];
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }


    // ----------------------- Messenger realisation ------------------------------------------ //

    public function sendMessage($senderId, $receiverId, $message, $documentPath = null) {
        try {
            // Begin transaction
            $this->connection->beginTransaction();

            // Insert the message
            $sql = "INSERT INTO Message (sender_id, receiver_id, contenu, timestamp) VALUES (:sender_id, :receiver_id, :contenu, :timestamp)";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                ':sender_id' => $senderId,
                ':receiver_id' => $receiverId,
                ':contenu' => $message,
                ':timestamp' => date("Y-m-d H:i:s")
            ]);
            $messageId = $this->connection->lastInsertId();

            // Handle document if provided
            if ($documentPath) {
                // Insert into Document table
                $sqlDoc = "INSERT INTO Document (filepath) VALUES (:filepath)";
                $stmt = $this->connection->prepare($sqlDoc);
                $stmt->execute([
                    ':filepath' => $documentPath
                ]);
                $documentId = $this->connection->lastInsertId();

                // Link document to message
                $sqlLink = "INSERT INTO Document_Message (document_id, message_id) VALUES (:document_id, :message_id)";
                $stmt = $this->connection->prepare($sqlLink);
                $stmt->execute([
                    ':document_id' => $documentId,
                    ':message_id' => $messageId
                ]);
            }

            // Commit transaction
            $this->connection->commit();
            return true;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            echo "Error: " . $e->getMessage();
            return false;
        }
    }


    public function getMessages($senderId, $receiverId) {
        $sql = "SELECT Message.*, Document.filepath FROM Message
            LEFT JOIN Document_Message ON Message.id = Document_Message.message_id
            LEFT JOIN Document ON Document_Message.document_id = Document.id
            WHERE (Message.sender_id = :sender_id AND Message.receiver_id = :receiver_id) 
               OR (Message.sender_id = :receiver_id AND Message.receiver_id = :sender_id)
            ORDER BY Message.timestamp";

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                ':sender_id' => $senderId,
                ':receiver_id' => $receiverId
            ]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }


    public function deleteMessage($messageId) {
        $sql = "DELETE FROM Message WHERE id = :message_id";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':message_id', $messageId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function getMessageById($messageId) {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM Message WHERE id = :id");
            $stmt->bindParam(':id', $messageId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getPendingUsers()
    {
        $sql = "SELECT * FROM User WHERE status = 'pending'";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }

    public function getActiveUsers()
    {
        $sql = "SELECT * FROM User WHERE status = 'active'";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }

    public function approveUser($userId)
    {
        $sql = "UPDATE User SET status = 'active' WHERE id = :id";
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([':id' => $userId]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function rejectUser($userId)
    {
        $sql = "DELETE FROM User WHERE id = :id";
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([':id' => $userId]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function deleteUser($userId)
    {
        return $this->rejectUser($userId); // test and reusing same method
    }

    public function getUserById($userId)
    {
        $sql = "SELECT * FROM User WHERE id = :id";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':id' => $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }

    public function getLastMessageId() {
        return $this->connection->lastInsertId();
    }
    // ------------------------- Forgot password functions --------------- //

    public function getUserByEmail($email)
    {
        $sql = "SELECT * FROM User WHERE email = :email";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':email' => $email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function getPasswordResetRequest($email, $verification_code)
    {
        $sql = "SELECT * FROM password_resets WHERE email = :email AND verification_code = :verification_code";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':email' => $email, ':verification_code' => $verification_code]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function updateUserPasswordByEmail($email, $hashedPassword)
    {
        $sql = "UPDATE password SET password = :password WHERE user_id = (SELECT id FROM User WHERE email = :email)";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                ':password' => $hashedPassword,
                ':email' => $email
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteVerificationCode($email)
    {
        $sql = "DELETE FROM password_resets WHERE email = :email";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':email' => $email]);
            return true;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    // ------------------------------------------------------------------- //

    // -------------------- Email verification ------------------------------------------

    public function getVerificationCode($userId) {
        $sql = "SELECT * FROM Verification_Code WHERE user_id = :user_id AND expires_at > NOW()";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateEmailValidationStatus($userId, $status) {
        $sql = "UPDATE User SET valid_email = :status WHERE id = :user_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }


    public function getUserIdByEmail($email) {
        $query = "SELECT id FROM User WHERE email = :email";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['id'] ?? null;
    }

    public function getUserPreferences($userId)
    {
        $sql = "SELECT notification, a2f, darkmode FROM Preference WHERE user_id = :user_id";

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            echo "Error fetching preferences: " . $e->getMessage();
            return false;
        }
    }


    public function setUserPreferences($userId, $notification, $a2f, $darkmode) {
        $sql = "INSERT INTO Preference (user_id, notification, a2f, darkmode) 
            VALUES (:user_id, :notification, :a2f, :darkmode)
            ON DUPLICATE KEY UPDATE notification = :notification, a2f = :a2f, darkmode = :darkmode";

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':notification' => $notification,
                ':a2f' => $a2f,
                ':darkmode' => $darkmode
            ]);
            return true;
        } catch (PDOException $e) {
            echo "Error updating preferences: " . $e->getMessage();
            return false;
        }
    }



    public function getConnection() {
        return $this->connection;
    }

    public function closeConnection()
    {
        $this->connection = null;
    }

}
?>