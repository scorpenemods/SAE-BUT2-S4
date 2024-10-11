<?php
class Database
{
    private $connection;

    public function __construct()
    {
        $this->connect();
    }

    private function connect()
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
    public function verifyLogin($login, $password)
    {
        $sql = "SELECT User.*, password.password FROM User
        JOIN password ON User.id = password.user_id
        WHERE User.login = :login";

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':login', $login);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Проверка пароля
            if ($result && password_verify($password, $result['password'])) {
                if ($result['valid_email'] != '1') {
                    return ['status' => 'email_not_validated', 'user' => $result];
                }
                if ($result['status'] !== 'active') {
                    return ['status' => 'pending', 'user' => $result];
                }
                return ['status' => 'success', 'user' => $result];
            }
            return ['status' => 'failed']; // Неверный логин или пароль
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }


    // Adding a new user
    public function addUser($login, $password, $email, $telephone, $prenom, $activite, $role, $nom)
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $status = 'pending'; // User status by default 'pending'

        $sqlUser = "INSERT INTO User (login, email, telephone, prenom, activite, role, nom, status) VALUES (:login, :email, :telephone, :prenom, :activite, :role, :nom, :status)";

        try {
            $stmt = $this->connection->prepare($sqlUser);
            $stmt->execute([
                ':login' => $login,
                ':email' => $email,
                ':telephone' => $telephone,
                ':prenom' => $prenom,
                ':activite' => $activite,
                ':role' => $role,
                ':nom' => $nom,
                ':status' => $status
            ]);
            $userId = $this->connection->lastInsertId();

            $sqlPassword = "INSERT INTO password (user_id, password) VALUES (:user_id, :password)";
            $stmt = $this->connection->prepare($sqlPassword);
            $stmt->execute([
                ':user_id' => $userId,
                ':password' => $passwordHash
            ]);

            return true;
        } catch (PDOException $e) {
            echo "Insert error: " . $e->getMessage();
            return false;
        }
    }

    // Getting user information
    public function getPersonByUsername($username)
    {
        $sql = "SELECT nom, prenom, telephone, login, role, activite, email, id as user_id FROM User WHERE login = :login";

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':login', $username, PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                require_once "Person.php";
                return new \Person(
                    $result['nom'],
                    $result['prenom'],
                    $result['telephone'],
                    $result['login'],
                    $result['role'],
                    $result['activite'],
                    $result['email'],
                    $result['user_id']
                );
            }
            return null;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }

    // ----------------------- Messenger realisation ------------------------------------------ //

    public function sendMessage($senderId, $receiverId, $message, $filePath = null) {
        $sql = "INSERT INTO Message (sender_id, receiver_id, contenu, file_path, timestamp) VALUES (:sender_id, :receiver_id, :contenu, :file_path, :timestamp)";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                ':sender_id' => $senderId,
                ':receiver_id' => $receiverId,
                ':contenu' => $message,
                ':file_path' => $filePath,
                ':timestamp' => date("Y-m-d H:i:s") // Устанавливаем временную метку с учетом часового пояса
            ]);
            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function getMessages($senderId, $receiverId) {
        $sql = "SELECT * FROM Message WHERE (sender_id = :sender_id AND receiver_id = :receiver_id) 
            OR (sender_id = :receiver_id AND receiver_id = :sender_id) ORDER BY timestamp";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                ':sender_id' => $senderId,
                ':receiver_id' => $receiverId
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    public function storeVerificationCode($email, $verification_code, $expires_at)
    {
        $sql = "INSERT INTO password_resets (email, verification_code, expires_at) VALUES (:email, :verification_code, :expires_at)
            ON DUPLICATE KEY UPDATE verification_code = :verification_code, expires_at = :expires_at";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                ':email' => $email,
                ':verification_code' => $verification_code,
                ':expires_at' => $expires_at
            ]);
            return true;
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
        $sql = "SELECT * FROM verification_codes WHERE user_id = :user_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function isEmailValidated($userId) {
        $query = "SELECT valid_email FROM User WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':id', $userId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result && $result['valid_email'] == '1') ? true : false;
    }

    public function updateEmailValidationStatus($userId, $status) {
        $sql = "UPDATE User SET valid_email = :status WHERE id = :user_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    public function storeEmailVerificationCode($userId, $code, $expires_at) {
        // Удаление существующих кодов для пользователя
        $sql = "DELETE FROM verification_codes WHERE user_id = :user_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        // adding new code
        $sql = "INSERT INTO verification_codes (user_id, code, expires_at) VALUES (:user_id, :code, :expires_at)";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':expires_at', $expires_at, PDO::PARAM_STR);
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
        $sql = "SELECT notification, a2f FROM Preference WHERE user_id = :user_id";

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error fetching preferences: " . $e->getMessage();
            return false;
        }
    }

    public function setUserPreferences($userId, $notification, $a2f) {
        $sql = "INSERT INTO Preference (user_id, notification, a2f) 
            VALUES (:user_id, :notification, :a2f) 
            ON DUPLICATE KEY UPDATE notification = :notification, a2f = :a2f";

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':notification', $notification, PDO::PARAM_INT);
            $stmt->bindParam(':a2f', $a2f, PDO::PARAM_INT);
            $stmt->execute();
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

    // ------------------------------------------------------------------- //

    // -------------------- Student list in professor home ------------------------------------------
    // Model/Database.php
    public function getStudents() {
        $query = "SELECT * FROM User WHERE role = 1";
        $result = $this->connection->query($query);
        $students = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $students[] = new Person(
                $row['nom'],
                $row['prenom'],
                $row['telephone'],
                $row['login'],
                $row['role'],
                $row['activite'],
                $row['email'],
                $row['id']
            );
        }
        return $students;
    }
}
?>