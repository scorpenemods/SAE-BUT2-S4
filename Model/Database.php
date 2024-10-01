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

    // Верификация логина пользователя
    public function verifyLogin($login, $password)
    {
        $sql = "SELECT password.password FROM User
                JOIN password ON User.id = password.user_id
                WHERE User.login = :login";

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':login', $login);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Сравнение пароля с хэшем
            if ($result && password_verify($password, $result['password'])) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    // Добавление нового пользователя
    public function addUser($login, $password, $email, $telephone, $prenom, $activite, $role, $nom)
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $sqlUser = "INSERT INTO User (login, email, telephone, prenom, activite, role, nom) VALUES (:login, :email, :telephone, :prenom, :activite, :role, :nom)";

        try {
            $stmt = $this->connection->prepare($sqlUser);
            $stmt->execute([
                ':login' => $login,
                ':email' => $email,
                ':telephone' => $telephone,
                ':prenom' => $prenom,
                ':activite' => $activite,
                ':role' => $role,
                ':nom' => $nom
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

    // Получение информации о пользователе
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

    public function getLastMessageId() {
        return $this->connection->lastInsertId();
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