<?php
// Initialisation of Database objects
date_default_timezone_set('Europe/Paris');

class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection;

    private function __construct()
    {
    }

    /**
     * Get the instance
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
            self::$instance->connect();
        }
        return self::$instance;
    }

    /**
     * Connection to the database
     * @return void
     */
    private function connect(): void
    {
        try {
            require_once __DIR__ . '/Config.php';
            $this->connection = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->exec("SET time_zone = '+01:00'");
        } catch (PDOException $e) {
            echo "Connection error: " . $e->getMessage();
            exit;
        }
    }

    /**
     * Add a file on database
     * @param string $name
     * @param string $path
     * @param int $userId
     * @param int $size
     * @return bool
     */
    public function addFile(string $name, string $path, int $userId, int $size): bool {
        $sql = "INSERT INTO File (name, path, user_id, size) VALUES (:name, :path, :user_id, :size)";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([
            ':name' => $name,
            ':path' => $path,
            ':user_id' => $userId,
            ':size' => $size,
        ]);
    }

    /**
     * Add a booklet file to database
     * @param string $name
     * @param string $path
     * @param int $userId
     * @param int $size
     * @param int $groupId
     * @return bool
     */
    public function addLivretFile(string $name, string $path, int $userId, int $size, int $groupId): bool {
        $sql = "INSERT INTO File (name, path, user_id, size, conv_id) VALUES (:name, :path, :user_id, :size, :groupId)";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([
            ':name' => $name,
            ':path' => $path,
            ':user_id' => $userId,
            ':size' => $size,
            ':groupId' => $groupId,
        ]);
    }

    /**
     * Delete a file to database
     * @param int $fileId
     * @return bool
     */
    public function deleteFile(int $fileId): bool {
        $sql = "SELECT path FROM File WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':id' => $fileId]);
        $file = $stmt->fetch();

        if ($file && file_exists($file['path'])) {
            unlink($file['path']); // Delete file on server
        }

        $sql = "DELETE FROM File WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([':id' => $fileId]);
    }

    /**
     * Get all files on database
     * @param int $studentId
     * @return array
     */
    public function getFiles(int $studentId): array {
        $sql = "SELECT * FROM File WHERE user_id = :studentId AND conv_id IS NULL";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':studentId' => $studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Get booklet's file on database
     * @param int $groupId
     * @return array
     */
    public function getLivretFile(int $groupId): array {
        $sql = "SELECT * FROM File WHERE conv_id = :groupId";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':groupId' => $groupId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if file exist on database
     * @param string $name
     * @param int $userId
     * @return bool
     */
    public function fileExists(string $name, int $userId): bool {
        $sql = "SELECT COUNT(*) FROM File WHERE name = :name AND user_id = :userId";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':name' => $name, ':userId' => $userId]);
        return $stmt->fetchColumn() > 0;
    }


    /**
     * Check the user's login
     * @param $email
     * @param $password
     * @return array
     */
    public function verifyLogin($email, $password): array
    {
        $message = "";
        $sql = "SELECT User.*, Password.password_hash FROM User
            JOIN Password ON User.id = Password.user_id
            WHERE User.email = :email AND Password.actif = 1";

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $result = $stmt->fetch();

            if ($result) {
                if (password_verify($password, $result['password_hash'])) {
                    return [
                        'valid_email' => $result['valid_email'],
                        'status_user' => $result['status_user'],
                        'user' => $result
                    ];
                } else {
                    return [];
                }
            } else {
                return [];
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }


    /**
     * Add a new user to database
     * @param $email
     * @param $password
     * @param $telephone
     * @param $prenom
     * @param $activite
     * @param $role
     * @param $nom
     * @param $status
     * @return bool
     */
    public function addUser($email, $password, $telephone, $prenom, $activite, $role, $nom, $status)
    {
        // Hash the password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        // Define the SQL query for inserting a new user
        $sqlUser = "INSERT INTO User (email, telephone, prenom, activite, role, nom, status_user, valid_email, account_creation) 
                VALUES (:email, :telephone, :prenom, :activite, :role, :nom, :status_user, 0, NOW())";
        try {
            // Prepare and execute the user insert statement
            $stmt = $this->connection->prepare($sqlUser);
            $stmt->execute([
                ':email' => $email,
                ':telephone' => $telephone,
                ':prenom' => $prenom,
                ':activite' => $activite,
                ':role' => $role,
                ':nom' => $nom,
                ':status_user' => $status
            ]);
            // Get the last inserted user ID
            $userId = $this->connection->lastInsertId();
            // Insert the password with user_id reference
            $sqlPassword = "INSERT INTO Password (user_id, password_hash, actif) VALUES (:user_id, :password_hash, 1)";
            $stmt = $this->connection->prepare($sqlPassword);
            $stmt->execute([
                ':user_id' => $userId,
                ':password_hash' => $passwordHash
            ]);
            // Insert default preferences for the new user
            $sqlPreference = "INSERT INTO Preference (user_id, notification, a2f, darkmode) VALUES (:user_id, 1, 0, 0)";
            $stmt = $this->connection->prepare($sqlPreference);
            $stmt->execute([
                ':user_id' => $userId
            ]);
            return true;
        } catch (PDOException $e) {
            echo "Insert error: " . $e->getMessage();
            return false;
        }
    }


    /**
     * Getting user's informations
     * @param $email
     * @return Person|null
     */
    public function getPersonByUsername($email)
    {
        $sql = "SELECT nom, prenom, telephone, email, role, activite, id as user_id FROM User WHERE email = :email";

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                require_once "Person.php";
                return new \Person(
                    $result['nom'],
                    $result['prenom'],
                    $result['telephone'],
                    $result['email'],
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

    /**
     * Send a message to another user
     * @param $senderId
     * @param $receiverId
     * @param $message
     * @param $filePath
     * @return false|string
     */
    public function sendMessage($senderId, $receiverId, $message, $filePath = null)
    {
        try {
            $this->connection->beginTransaction();

            // Message insertion
            $stmt = $this->connection->prepare("
                INSERT INTO Message (sender_id, receiver_id, contenu, timestamp)
                VALUES (:sender_id, :receiver_id, :contenu, NOW())
            ");
            $stmt->bindParam(':sender_id', $senderId);
            $stmt->bindParam(':receiver_id', $receiverId);
            $stmt->bindParam(':contenu', $message);
            $stmt->execute();

            $messageId = $this->connection->lastInsertId();

            // If a file is link, insert into Document and Document_message
            if ($filePath) {
                // Insertion Into Document
                $stmt = $this->connection->prepare("
                    INSERT INTO Document (filepath)
                    VALUES (:filepath)
                ");
                $stmt->bindParam(':filepath', $filePath);
                $stmt->execute();
                $documentId = $this->connection->lastInsertId();

                // Insertion into Document_Message
                $stmt = $this->connection->prepare("
                    INSERT INTO Document_Message (document_id, message_id)
                    VALUES (:document_id, :message_id)
                ");
                $stmt->bindParam(':document_id', $documentId);
                $stmt->bindParam(':message_id', $messageId);
                $stmt->execute();
            }

            $this->connection->commit();

            return $messageId; // Return the message's id
        } catch (PDOException $e) {
            $this->connection->rollBack();
            error_log("Erreur lors de l'envoi du message : " . $e->getMessage());
            return false;
        }
    }


    /**
     * Get the messages send into 2 users
     * @param $senderId
     * @param $receiverId
     * @return array|false
     */
    public function getMessages($senderId, $receiverId)
    {
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


    /**
     * Delete a message by id
     * @param $messageId
     * @return bool
     */
    public function deleteMessage($messageId)
    {
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

    /**
     * Get a message by id
     * @param $messageId
     * @return false|mixed
     */
    public function getMessageById($messageId)
    {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM Message WHERE id = :id");
            $stmt->bindParam(':id', $messageId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // ====== Messagerie Contacts ======= //

    /**
     * Get the same group of user's contact
     * @param $userId
     * @return array|false
     */
    public function getGroupContacts($userId)
    {
        $query = "
            SELECT DISTINCT User.id, User.nom, User.prenom, User.role
            FROM User
            INNER JOIN Groupe ON User.id = Groupe.user_id
            INNER JOIN (
                SELECT conv_id
                FROM Groupe
                WHERE user_id = :user_id
            ) AS UserGroups ON Groupe.conv_id = UserGroups.conv_id
            WHERE User.id != :user_id;
        ";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all messages between 2 Users
     * @param $userId1
     * @param $userId2
     * @return array|false
     */
    public function getMessagesBetweenUsers($userId1, $userId2)
    {
        $stmt = $this->connection->prepare("
        SELECT m.*, d.filepath, CONVERT_TZ(m.timestamp, '+00:00', '+02:00') as timestamp_local
        FROM Message m
        LEFT JOIN Document_Message dm ON m.id = dm.message_id
        LEFT JOIN Document d ON dm.document_id = d.id
        WHERE (m.sender_id = :userId1 AND m.receiver_id = :userId2)
           OR (m.sender_id = :userId2 AND m.receiver_id = :userId1)
        ORDER BY m.timestamp ASC
    ");
        $stmt->execute([':userId1' => $userId1, ':userId2' => $userId2]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Get the pending user's account
     * @return array|false
     */
    public function getPendingUsers()
    {
        $sql = "SELECT * FROM User WHERE status_user = 0";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Get the active user's account
     * @return array|false
     */
    public function getActiveUsers()
    {
        $sql = "SELECT * FROM User WHERE status_user = 1";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Approuve a user's account
     * @param $userId
     * @return bool
     */
    public function approveUser($userId): bool
    {
        $sql = "UPDATE User SET status_user = 1 WHERE id = :id";
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([':id' => $userId]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Reject a user's account
     * @param $userId
     * @return bool
     */
    public function rejectUser($userId): bool
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

    /**
     * Delete a user's account
     * @param $userId
     * @return bool
     */
    public function deleteUser($userId)
    {
        return $this->rejectUser($userId);
    }

    /**
     * Get a user by id
     * @param $userId
     * @return mixed|null
     */
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

    /**
     * Get a last message of user by id
     * @return false|string
     */
    public function getLastMessageId()
    {
        return $this->connection->lastInsertId();
    }

    // ------------------------- Forgot password functions --------------- //

    /**
     * Get a user by his email
     * @param $email
     * @return false|mixed
     */
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

    /**
     * Store in database the email verification code
     * @param $userId
     * @param $code
     * @param $expires_at
     * @return bool
     */
    public function storeEmailVerificationCode($userId, $code, $expires_at)
    {
        try {
            // Start transaction
            $this->connection->beginTransaction();

            // Delete existing codes
            $sqlDelete = "DELETE FROM Verification_Code WHERE user_id = :user_id";
            $stmt = $this->connection->prepare($sqlDelete);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            // Insert new code
            $sqlInsert = "INSERT INTO Verification_Code (user_id, code, expires_at) VALUES (:user_id, :code, :expires_at)";
            $stmt = $this->connection->prepare($sqlInsert);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':code', $code, PDO::PARAM_STR);
            $stmt->bindParam(':expires_at', $expires_at, PDO::PARAM_STR);
            $stmt->execute();

            // Commit transaction
            $this->connection->commit();
            return true;
        } catch (PDOException $e) {
            // Rollback if any error occurs
            $this->connection->rollBack();
            echo "Error storing verification code: " . $e->getMessage();
            return false;
        }
    }


    /**
     * Get the password reset request
     * @param $email
     * @param $verification_code
     * @return false|mixed
     */
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

    /**
     * Update a user's password by email
     * @param $email
     * @param $hashedPassword
     * @return bool
     */
    public function updateUserPasswordByEmail($email, $hashedPassword): bool
    {
        $sql = "UPDATE Password 
            JOIN User ON Password.user_id = User.id
            SET Password.password_hash = :password
            WHERE User.email = :email AND Password.actif = 1";

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour du mot de passe : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a verification code with an id
     * @param $userId
     * @return bool
     */
    public function deleteVerificationCode($userId): bool
    {
        $sql = "DELETE FROM Verification_Code WHERE user_id = :user_id";

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression du code de vérification : " . $e->getMessage());
            return false;
        }
    }


    // ------------------------------------------------------------------- //

    // -------------------- Email verification ------------------------------------------

    /**
     * Get a user's verification code by id
     * @param $userId
     * @return false|mixed
     */
    public function getVerificationCode($userId)
    {
        $sql = "SELECT * FROM Verification_Code WHERE user_id = :user_id AND expires_at > :current_time";
        try {
            $stmt = $this->connection->prepare($sql);
            $currentTime = date("Y-m-d H:i:s"); // Heure actuelle en PHP
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':current_time', $currentTime);
            $stmt->execute();

            $rowCount = $stmt->rowCount();
            error_log("Nombre de codes de vérification trouvés pour l'utilisateur ID $userId : " . $rowCount);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération du code de vérification : " . $e->getMessage());
            return false;
        }
    }


    /**
     * Check if email is validated
     * @param $userId
     * @return bool
     */
    public function isEmailValidated($userId)
    {
        $query = "SELECT valid_email FROM User WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':id', $userId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result && $result['valid_email'] == '1') ? true : false;
    }

    //-------------- Update user status if email has been validated ------------------------- //

    /**
     * Update the status of the email validation
     * @param $userId
     * @param $status
     * @return bool
     */
    public function updateEmailValidationStatus($userId, $status)
    {
        $sql = "UPDATE User SET valid_email = :status WHERE id = :user_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // -------------------------------------------------------------------------------------- //

    /**
     * Get the user's id by email
     * @param $email
     * @return mixed|null
     */
    public function getUserIdByEmail($email)
    {
        $query = "SELECT id FROM User WHERE email = :email";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['id'] ?? null;
    }

    /**
     * Get user's preference by id
     * @param $userId
     * @return false|mixed
     */
    public function getUserPreferences($userId)
    {
        $sql = "SELECT notification, a2f, darkmode FROM Preference WHERE user_id = :user_id";

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);  // Use FETCH_ASSOC for associative array
        } catch (PDOException $e) {
            echo "Error fetching preferences: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Save the user's preference on database
     * @param $userId
     * @param $notification
     * @param $a2f
     * @param $darkmode
     * @return bool
     */
    public function setUserPreferences($userId, $notification, $darkmode): bool
    {
        $sql = "INSERT INTO Preference (notification, a2f, darkmode, user_id) 
            VALUES (:notification, :a2f, :darkmode, :user_id)
            ON DUPLICATE KEY UPDATE notification = :notification, a2f = :a2f, darkmode = :darkmode";

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':notification' => $notification,
                ':a2f' => 0,
                ':darkmode' => $darkmode
            ]);
            return true;
        } catch (PDOException $e) {
            echo "Error updating preferences: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Get the actual connection
     * @return PDO|null
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Close the actual connection
     * @return void
     */
    public function closeConnection(): void
    {
        $this->connection = null;
    }

    // ------------------------------------------------------------------- //


    //========================     Gropes methods        ================================== //
    /**
     * Get all studients
     * @param $validatedOnly
     * @return array
     */
    public function getAllStudents($validatedOnly = false): array
    {
        $query = "SELECT User.nom, User.prenom, User.telephone, User.role, User.activite, User.email, User.id FROM User
              WHERE User.role = 1";
        if ($validatedOnly) {
            $query .= " AND status_user = 1";
        }
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        $students = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $students[] = new Person(
                $row['nom'],
                $row['prenom'],
                $row['telephone'],
                $row['role'],
                $row['activite'],
                $row['email'],
                $row['id']
            );
        }
        return $students;
    }

    /**
     * Get the groups a user belongs to.
     * @param $userId
     * @return array|false
     */
    public function getUserGroups($userId)
    {
        $sql = "SELECT DISTINCT Groupe.conv_id AS id, Convention.convention
                FROM Groupe
                JOIN Convention ON Groupe.conv_id = Convention.id
                WHERE Groupe.user_id = :user_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get the group where the 3 users belong to.
     * @param $studentId
     * @param $professorId
     * @param $mentorId
     * @return mixed
     */
    public function getGroup($studentId, $professorId, $mentorId)
    {
        $sql = "SELECT Groupe.conv_id
                FROM Groupe
                JOIN User ON Groupe.user_id = User.id
                WHERE Groupe.user_id IN (:studentId, :professorId, :mentorId)
                GROUP BY conv_id
                HAVING COUNT(DISTINCT Groupe.user_id) = 3";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':studentId', $studentId, PDO::PARAM_INT);
        $stmt->bindParam(':professorId', $professorId, PDO::PARAM_INT);
        $stmt->bindParam(':mentorId', $mentorId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Get a message from a group
     * @param $groupId
     * @return array|false
     */
    public function getGroupMessages($groupId)
    {
        $sql = "SELECT MessageGroupe.*, Document.filepath, User.prenom, User.nom
            FROM MessageGroupe
            LEFT JOIN Document_Message ON MessageGroupe.id = Document_Message.message_id
            LEFT JOIN Document ON Document_Message.document_id = Document.id
            JOIN User ON MessageGroupe.sender_id = User.id
            WHERE MessageGroupe.groupe_id = :group_id
            ORDER BY MessageGroupe.timestamp ASC";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':group_id', $groupId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Send a message to a group
     * @param $groupId
     * @param $senderId
     * @param $message
     * @param $filePath
     * @return bool
     */
    public function sendGroupMessage($groupId, $senderId, $message, $filePath = null)
    {
        try {
            $this->connection->beginTransaction();

            // Insert the message
            $stmt = $this->connection->prepare("
                INSERT INTO MessageGroupe (groupe_id, sender_id, contenu, timestamp)
                VALUES (:groupe_id, :sender_id, :contenu, NOW())
            ");
            $stmt->bindParam(':groupe_id', $groupId);
            $stmt->bindParam(':sender_id', $senderId);
            $stmt->bindParam(':contenu', $message);
            $stmt->execute();

            $messageId = $this->connection->lastInsertId();

            // If a file is attached, insert into Document and Document_Message
            if ($filePath) {
                // Insert into Document
                $stmt = $this->connection->prepare("
                    INSERT INTO Document (filepath)
                    VALUES (:filepath)
                ");
                $stmt->bindParam(':filepath', $filePath);
                $stmt->execute();
                $documentId = $this->connection->lastInsertId();

                // Insert into Document_Message
                $stmt = $this->connection->prepare("
                    INSERT INTO Document_Message (document_id, message_id)
                    VALUES (:document_id, :message_id)
                ");
                $stmt->bindParam(':document_id', $documentId);
                $stmt->bindParam(':message_id', $messageId);
                $stmt->execute();
            }

            $this->connection->commit();

            return true;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            error_log("Error sending group message: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all groups with their members
     * @return array
     */
    public function getAllGroupsWithMembers()
    {
        $sql = "SELECT c.id AS group_id, c.convention AS group_name, u.id AS user_id, u.nom AS last_name, u.prenom AS first_name
                FROM Groupe g
                JOIN Convention c ON g.conv_id = c.id
                JOIN User u ON g.user_id = u.id
                ORDER BY c.id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $groups = [];
        foreach ($results as $row) {
            $groupId = $row['group_id'];
            if (!isset($groups[$groupId])) {
                $groups[$groupId] = [
                    'group_id' => $groupId,
                    'group_name' => $row['group_name'],
                    'members' => []
                ];
            }
            $groups[$groupId]['members'][] = [
                'user_id' => $row['user_id'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name']
            ];
        }
        return $groups;
    }


    /**
     * Delete a group by id
     * @param $groupId
     * @return bool
     */

    public function deleteGroup($groupId)
    {
        try {
            $this->connection->beginTransaction();

            // Supprimer les messages du groupe
            $stmt = $this->connection->prepare("DELETE FROM MessageGroupe WHERE groupe_id = :group_id");
            $stmt->bindParam(':group_id', $groupId, PDO::PARAM_INT);
            $stmt->execute();

            // Supprimer les entrées dans Document_Message liées aux messages du groupe
            $stmt = $this->connection->prepare("
            DELETE dm FROM Document_Message dm
            JOIN MessageGroupe mg ON dm.message_id = mg.id
            WHERE mg.groupe_id = :group_id
        ");
            $stmt->bindParam(':group_id', $groupId, PDO::PARAM_INT);
            $stmt->execute();

            // Supprimer les entrées dans FollowUpBook liées au groupe
            $stmt = $this->connection->prepare("DELETE FROM FollowUpBook WHERE group_id = :group_id");
            $stmt->bindParam(':group_id', $groupId, PDO::PARAM_INT);
            $stmt->execute();

            // Supprimer les membres du groupe dans la table Groupe
            $stmt = $this->connection->prepare("DELETE FROM Groupe WHERE conv_id = :group_id");
            $stmt->bindParam(':group_id', $groupId, PDO::PARAM_INT);
            $stmt->execute();

            // Supprimer le groupe dans Convention
            $stmt = $this->connection->prepare("DELETE FROM Convention WHERE id = :group_id");
            $stmt->bindParam(':group_id', $groupId, PDO::PARAM_INT);
            $stmt->execute();

            $this->connection->commit();
            return true;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            error_log("Error deleting group: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Update the member of a group
     * @param $groupId
     * @param $memberIds
     * @return bool
     */
    public function updateGroupMembers($groupId, $memberIds)
    {
        try {
            $this->connection->beginTransaction();

            // Delete existing group members
            $stmt = $this->connection->prepare("DELETE FROM Groupe WHERE conv_id = :group_id");
            $stmt->bindParam(':group_id', $groupId, PDO::PARAM_INT);
            $stmt->execute();

            // Add new group members
            foreach ($memberIds as $userId) {
                $stmt = $this->connection->prepare("INSERT INTO Groupe (conv_id, user_id) VALUES (:conv_id, :user_id)");
                $stmt->execute([':conv_id' => $groupId, ':user_id' => $userId]);
            }

            $this->connection->commit();
            return true;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            error_log("Error updating group members: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the member of one group
     * @param $groupId
     * @return array|false
     */
    public function getGroupMembers($groupId)
    {
        $sql = "SELECT user_id FROM Groupe WHERE conv_id = :group_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':group_id', $groupId, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        } else {
            return false;
        }
    }

    /**
     * Get all group's messages since a specific date
     * @param $groupId
     * @param $lastTimestamp
     * @return array|false
     */
    public function getGroupMessagesSince($groupId, $lastTimestamp)
    {
        $sql = "SELECT mg.*, d.filepath, u.prenom, u.nom
            FROM MessageGroupe mg
            LEFT JOIN Document_Message dm ON mg.id = dm.message_id
            LEFT JOIN Document d ON dm.document_id = d.id
            JOIN User u ON mg.sender_id = u.id
            WHERE mg.groupe_id = :group_id
              AND mg.timestamp > :last_timestamp
            ORDER BY mg.timestamp ASC";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':group_id', $groupId, PDO::PARAM_INT);
        $stmt->bindParam(':last_timestamp', $lastTimestamp);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -------------------- Student list in professor home ------------------------------------------ //

    /**
     * Get all students of a professor
     * @param $professorId
     * @return array
     */
    public function getStudentsProf($professorId): array
    {
        $query = "SELECT User.id, User.nom, User.prenom
                    FROM User
                    JOIN Groupe ON User.id = Groupe.user_id
                    JOIN Convention ON Groupe.conv_id = Convention.id
                    WHERE Groupe.conv_id IN (
                        SELECT Groupe.conv_id
                        FROM Groupe
                        JOIN User AS Professor ON Groupe.user_id = Professor.id
                        WHERE Professor.id = :professor_id
                        AND Professor.role = 2
                    )
                    AND User.role = 1";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':professor_id', $professorId, PDO::PARAM_INT);
        $stmt->execute();
        $students = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $students[] = new Person(
                $row['nom'] ?? '',
                $row['prenom'] ?? '',
                $row['telephone'] ?? 0,
                $row['role'] ?? '',
                $row['activite'] ?? '',
                $row['email'] ?? '',
                $row['id'] ?? 0
            );
        }
        return $students;
    }

    /**
     * Get all students of a internship supervisor
     * @param $maitreStageId
     * @return array
     */
    public function getStudentsMaitreDeStage($maitreStageId): array
    {
        $query = "SELECT  User.id ,User.nom, User.prenom
                    FROM User
                        JOIN Groupe ON User.id = Groupe.user_id
                        JOIN Convention ON Groupe.conv_id = Convention.id
                    WHERE Groupe.conv_id IN (
                            SELECT Groupe.conv_id
                            FROM Groupe
                            JOIN User AS MaitreStage ON Groupe.user_id = MaitreStage.id
                            WHERE MaitreStage.id = :maitre_stage_id
                            AND MaitreStage.role = 3
                            )
                    AND User.role = 1;";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':maitre_stage_id', $maitreStageId, PDO::PARAM_INT);
        $stmt->execute();
        $students = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $students[] = new Person(
                $row['nom'] ?? '',
                $row['prenom'] ?? '',
                $row['telephone'] ?? 0,
                $row['role'] ?? '',
                $row['activite'] ?? '',
                $row['email'] ?? '',
                $row['id'] ?? 0
            );
        }
        return $students;
    }

    // --------------------  Note in Database ------------------------------------------ //

    /**
     * Get all notes of a student
     * @param $studentId
     * @return array
     */
    public function getStudentNotes($studentId): array
    {
        $query = "SELECT * FROM Note WHERE user_id = :student_id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Return an associative array
    }


    /**
     * Crée automatiquement les 4 lignes principales pour un étudiant
     * @param int $studentId
     */
    public function createMainNotesForStudent(int $studentId): void {
        if ($studentId <= 0) {
            echo "Erreur : ID utilisateur invalide.";
            return;
        }

        // Vérifiez si les lignes principales existent déjà
        $sqlCheck = "SELECT COUNT(*) FROM Note WHERE user_id = :user_id";
        $stmtCheck = $this->connection->prepare($sqlCheck);
        $stmtCheck->bindParam(':user_id', $studentId, PDO::PARAM_INT);
        $stmtCheck->execute();
        $noteCount = $stmtCheck->fetchColumn();

        if ($noteCount == 0) {
            // Insérez les 4 lignes principales
            $sqlInsert = "
            INSERT INTO Note (sujet, note, coeff, user_id) VALUES
            ('Rapport', 0, 4, :user_id),
            ('Évaluation de l''entreprise', 0, 2, :user_id),
            ('Soutenance', 0, 3, :user_id),
            ('Technicité', 0, 1, :user_id)
        ";
            $stmtInsert = $this->connection->prepare($sqlInsert);
            $stmtInsert->bindParam(':user_id', $studentId, PDO::PARAM_INT);

            try {
                $stmtInsert->execute();
                echo "Les lignes principales ont été créées avec succès.";
            } catch (PDOException $e) {
                echo "Erreur lors de l'insertion : " . $e->getMessage();
            }
        }
    }


    public function saveSliderValue(int $noteId, string $description, int $value): void
    {
        try {
            // Vérifie si la ligne existe déjà
            $checkSql = "SELECT COUNT(*) FROM Sous_Note WHERE note_id = :note_id AND description = :description";
            $checkStmt = $this->connection->prepare($checkSql);
            $checkStmt->bindParam(':note_id', $noteId, PDO::PARAM_INT);
            $checkStmt->bindParam(':description', $description, PDO::PARAM_STR);
            $checkStmt->execute();
            $exists = $checkStmt->fetchColumn() > 0;

            if ($exists) {
                // Mise à jour si la ligne existe
                $updateSql = "UPDATE Sous_Note SET note = :note WHERE note_id = :note_id AND description = :description";
                $updateStmt = $this->connection->prepare($updateSql);
                $updateStmt->bindParam(':note_id', $noteId, PDO::PARAM_INT);
                $updateStmt->bindParam(':description', $description, PDO::PARAM_STR);
                $updateStmt->bindParam(':note', $value, PDO::PARAM_INT);
                $updateStmt->execute();
            } else {
                // Insertion si la ligne n'existe pas
                $insertSql = "INSERT INTO Sous_Note (note_id, description, note) VALUES (:note_id, :description, :note)";
                $insertStmt = $this->connection->prepare($insertSql);
                $insertStmt->bindParam(':note_id', $noteId, PDO::PARAM_INT);
                $insertStmt->bindParam(':description', $description, PDO::PARAM_STR);
                $insertStmt->bindParam(':note', $value, PDO::PARAM_INT);
                $insertStmt->execute();
            }
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de l'enregistrement : " . $e->getMessage());
        }
    }

    public function getSliderValues(int $noteId): array {
        $sql = "SELECT description, note FROM Sous_Note WHERE note_id = :note_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':note_id', $noteId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMainNoteAverage(int $noteId): float|int|null
    {
        // Récupérer toutes les sous-notes liées à cette note
        $stmt = $this->connection->prepare("SELECT note FROM Sous_Note WHERE note_id = :note_id");
        $stmt->execute([':note_id' => $noteId]);
        $sousNotes = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Si aucune sous-note n'est trouvée, retourner null
        if (!$sousNotes || count($sousNotes) === 0) {
            return null;
        }

        // Récupérer le coefficient principal de la note
        $stmtCoeff = $this->connection->prepare("SELECT coeff FROM Note WHERE id = :id");
        $stmtCoeff->execute([':id' => $noteId]);
        $coeff = $stmtCoeff->fetchColumn();

        // Vérification si le coefficient est valide
        if ($coeff === false) {
            return null;
        }

        $totalSumNote = 0;
        $totalSumCoeff = 0;

        // Calcul de la somme pondérée des sous-notes
        foreach ($sousNotes as $note) {
            $note = floatval($note); // Convertir la sous-note en float

            $totalSumNote += $note * $coeff;
            $totalSumCoeff += $coeff;
        }

        // Vérifier qu'il y a bien un coefficient total pour éviter une division par zéro
        if ($totalSumCoeff === 0) {
            return null;
        }

        // Calculer la moyenne pondérée et la ramener sur 20
        $average = ($totalSumNote / $totalSumCoeff) * (20 / 5); // Ramener sur 20

        // Mettre à jour la table Note avec la nouvelle moyenne
        $updateStmt = $this->connection->prepare("UPDATE Note SET note = :moyenne WHERE id = :id");
        $updateStmt->execute([
            ':moyenne' => $average,
            ':id' => $noteId,
        ]);
        return $average;
    }

    public function getNoteSujet(int $noteId): ?string {
        $sql = "SELECT sujet FROM Note WHERE id = :note_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':note_id' => $noteId]);
        return $stmt->fetchColumn() ?: null;
    }







    // ------------------------------------------------------------------------------------------------------- //

    /**
     * Get all professors
     * @param $validatedOnly
     * @return array
     */
    public function getProfessor($validatedOnly = false): array
    {
        $query = "SELECT DISTINCT User.nom, User.prenom, User.telephone, User.role, User.activite, User.email, User.id from User
                    where role = 2";
        if ($validatedOnly) {
            $query .= " AND status_user = 1";
        }
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        $professor = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $professor[] = new Person(
                $row['nom'],
                $row['prenom'],
                $row['telephone'],
                $row['role'],
                $row['activite'],
                $row['email'],
                $row['id']
            );
        }
        return $professor;
    }

    /**
     * Get all Tutors
     * @param $validatedOnly
     * @return array
     */
    public function getTutor($validatedOnly = false): array
    {
        $query = "SELECT DISTINCT User.nom, User.prenom, User.telephone, User.role, User.activite, User.email, User.id from User
                    where role = 3";
        if ($validatedOnly) {
            $query .= " AND status_user = 1";
        }
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        $tutor = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tutor[] = new Person(
                $row['nom'],
                $row['prenom'],
                $row['telephone'],
                $row['role'],
                $row['activite'],
                $row['email'],
                $row['id']
            );
        }
        return $tutor;
    }

    /**
     * Save the verification code of a user
     * @param $userId
     * @param $code
     * @param $expires_at
     * @return bool
     */
    public function storeVerificationCode($userId, $code, $expires_at): bool
    {
        try {
            // Start the transaction
            $this->connection->beginTransaction();

            // Removes existing verification codes for this user (just in case)
            $sqlDelete = "DELETE FROM Verification_Code WHERE user_id = :user_id";
            $stmt = $this->connection->prepare($sqlDelete);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            if (!$stmt->execute()) {
                error_log("Erreur lors de la suppression des anciens codes pour l'utilisateur $userId");
            }

            // Insert a new verification code
            $sqlInsert = "INSERT INTO Verification_Code (user_id, code, expires_at) VALUES (:user_id, :code, :expires_at)";
            $stmt = $this->connection->prepare($sqlInsert);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':code', $code, PDO::PARAM_STR);
            $stmt->bindParam(':expires_at', $expires_at, PDO::PARAM_STR);

            // Added logs to check execution
            if ($stmt->execute()) {
                error_log("Code de vérification $code inséré avec succès pour l'utilisateur $userId.");
            } else {
                error_log("Erreur lors de l'insertion du code de vérification pour l'utilisateur $userId.");
            }

            // Transaction commit
            $this->connection->commit();
            return true;
        } catch (PDOException $e) {
            // Rollback in case of error
            $this->connection->rollBack();
            error_log("Erreur lors du stockage du code de vérification : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the verification by a code
     * @param $code
     * @return false|mixed
     */
    public function getVerificationByCode($code)
    {
        $sql = "SELECT Verification_Code.user_id, Verification_Code.expires_at, User.email 
            FROM Verification_Code 
            JOIN User ON Verification_Code.user_id = User.id
            WHERE Verification_Code.code = :code";

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':code', $code, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération du code de vérification : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update the user last conection
     * @param $userId
     * @return bool
     */
    public function updateLastConnexion($userId): bool
    {
        date_default_timezone_set('Europe/Paris');
        $currentTime = date('Y-m-d H:i:s');

        $sql = "UPDATE User SET last_connexion = :currentTime WHERE id = :id";

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':currentTime', $currentTime);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de la dernière connexion : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the notes of one user
     * @param $userId
     * @return array
     */
    public function getNotes($userId): array
    {
        $sql = "SELECT Note.id, Note.sujet, Note.note, Note.coeff
                FROM Note
                WHERE Note.user_id = :user_id";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $notes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            require_once "Note.php";
            $notes[] = new Note(
                $row['id'] ?? '',
                $row['sujet'] ?? '',
                $row['note'] ?? '',
                $row['coeff'] ?? ''
            );
        }
        return $notes;
    }


    /**
     * Count all notifications of one user
     * @param $userId
     * @return int
     */
    public function getUnreadNotificationCount($userId): int
    {
        $sql = "SELECT COUNT(*) FROM Notification WHERE user_id = :user_id AND seen = 0";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error fetching unread notifications count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Check if user see the notifications
     * @param $userId
     * @return bool
     */
    public function markAllNotificationsAsSeen($userId): bool
    {
        $sql = "UPDATE Notification SET seen = 1 WHERE user_id = :user_id AND seen = 0";

        try {
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute([':user_id' => $userId]);
            // Debugging: Check if any rows were updated
            $affectedRows = $stmt->rowCount();
            error_log("Notifications marked as seen: $affectedRows");
            return $result;
        } catch (PDOException $e) {
            error_log("Error marking notifications as seen: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all notifications of one user
     * @param $userId
     * @return array
     */
    public function getNotifications($userId): array
    {
        $sql = "SELECT content, type, seen, created_at FROM Notification WHERE user_id = :user_id ORDER BY created_at DESC";

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des notifications : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Add a notification to one user
     * @param int $userId
     * @param string $content
     * @param string $type
     * @return bool
     */
    public function addNotification(int $userId, string $content, string $type): bool
    {
        date_default_timezone_set('Europe/Paris');
        require_once __DIR__ . '/Config.php';
        $sql = "INSERT INTO Notification (user_id, content, type, seen, created_at) VALUES (:user_id, :content, :type, 0, NOW())";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':content' => $content,
            ':type' => $type
        ]);

        // Check the total number of notifications
        $totalNotifications = $this->getTotalNotifications($userId);

        if ($totalNotifications > MAX_NOTIFICATIONS) {
            // Delete the oldest notification(s)
            $this->deleteOldNotifications($userId, $totalNotifications - MAX_NOTIFICATIONS);
        }

        return true;
    }

    /**
     * Count all notifications of one user
     * @param $userId
     * @return int
     */
    public function getTotalNotifications($userId): int
    {
        $sql = "SELECT COUNT(*) FROM Notification WHERE user_id = :user_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Delete old notifications
     * @param $userId
     * @param $numberToDelete
     * @return void
     */
    public function deleteOldNotifications($userId, $numberToDelete)
    {
        date_default_timezone_set('Europe/Paris');
        $sql = "DELETE FROM Notification WHERE user_id = :user_id ORDER BY created_at ASC LIMIT :limit";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $numberToDelete, PDO::PARAM_INT);
        $stmt->execute();
    }


    /**
     * Check if user had new notification
     * @param $userId
     * @return bool
     */
    public function hasNewNotifications($userId): bool
    {
        $sql = "SELECT COUNT(*) FROM Notification WHERE user_id = :user_id AND seen = 0";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            $count = $stmt->fetchColumn();
            return $count > 0;
        } catch (PDOException $e) {
            error_log("Error checking for new notifications: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if email exist
     * @param $email
     * @return bool
     */
    public function emailExists($email): bool
    {
        $sql = "SELECT COUNT(*) FROM User WHERE email = :email";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':email' => $email]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Get all stages of one user
     * @param $userId
     * @return array
     */
    public function getStages($userId): array
    {
        $query = "SELECT User.account_creation, User.id
                    FROM User
                    Join Groupe g on User.id = g.user_id
                    WHERE g.user_id = :user_id and User.role = 1";
        $stmt = $this->connection->prepare($query);
        $stmt->execute([':user_id' => $userId]);
        $stages = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stages[] = [substr($row['account_creation'], 0, 4), $row['id']];
        }
        return $stages;
    }

    //---------------- Secretariat send a message to everyone --------------------------------- //

    /**
     * Get all valid users
     * @return array|false
     */
    public function getAllValidUsers()
    {
        $conn = $this->getConnection();
        $query = "SELECT * FROM User WHERE status_user = 1";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    //---------------------- Livret de suvi --------------------------------- //

    /**
     * Get a group by user_id (student's id)
     * @param $userId
     * @return mixed
     */
    public function getGroupByUserId($userId) {
        $sql = "SELECT * FROM Groupe WHERE user_id = :uid LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetch();
    }


    /**
     * Get or created FollowUpBook for selected conv_id
     * @param $conv_id
     * @return false|mixed|string
     */
    public function getOrCreateFollowUpBook($conv_id) {
        $stmt = $this->connection->prepare("SELECT id FROM FollowUpBook WHERE group_id = :cid");
        $stmt->execute(['cid' => $conv_id]);
        $f = $stmt->fetch();
        if ($f) {
            return $f['id'];
        } else {
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d', strtotime('+3 months'));
            $stmt = $this->connection->prepare("INSERT INTO FollowUpBook (status, start_date, end_date, group_id) 
                                         VALUES ('En cours', :start, :end, :cid)");
            $stmt->execute(['start' => $start_date, 'end' => $end_date, 'cid' => $conv_id]);
            return $this->connection->lastInsertId();
        }
    }

    /**
     * Update a meeting book
     * @param $meetingId
     * @param $meetingDate
     * @return bool
     */
    public function updateMeetingBook($meetingId, $meetingDate) {
        $stmt = $this->connection->prepare("UPDATE MeetingBook SET meeting_date = :mdate WHERE id = :mid");
        return $stmt->execute([
            'mdate' => $meetingDate,
            'mid'   => $meetingId
        ]);
    }
    public function updateMeetingFields($meetingId, $texts, $qcms)
    {
        // We iterate each text data and update or insert
        foreach ($texts as $t) {
            // If we have an existing text id -> update
            if (!empty($t['id'])) {
                $this->updateMeetingText($t['id'], $t['response']);
            } else {
                // Insert as new text row
                $this->insertMeetingText($meetingId, $t['title'], $t['response']);
            }
        }
        // Same logic for QCM
        foreach ($qcms as $q) {
            if (!empty($q['id'])) {
                $this->updateMeetingQCM($q['id'], $q['other_choice']);
            } else {
                $this->insertMeetingQCM($meetingId, $q['title'], '', $q['other_choice']);
            }
        }
        return true;
    }

    /**
     * Delete meetings of a meeting book
     * @param $meetingId
     * @return bool
     */
    public function deleteMeeting($meetingId) {
        // Since MeetingQCM and MeetingTexts link to MeetingBook,
        // first delete the child entries
        $stmt = $this->connection->prepare("DELETE FROM MeetingQCM WHERE meeting_id = :mid");
        $stmt->execute(['mid' => $meetingId]);

        $stmt = $this->connection->prepare("DELETE FROM MeetingTexts WHERE meeting_id = :mid");
        $stmt->execute(['mid' => $meetingId]);

        // Now we delete the meeting itself
        $stmt = $this->connection->prepare("DELETE FROM MeetingBook WHERE id = :mid");
        return $stmt->execute(['mid' => $meetingId]);
    }

    /**
     * Update the meeting text of one metting
     * @param $textId
     * @param $newResponse
     * @return bool
     */
    public function updateMeetingText($textId, $newResponse) {
        $stmt = $this->connection->prepare("UPDATE MeetingTexts SET response = :resp WHERE id = :tid");
        return $stmt->execute([
            'resp' => $newResponse,
            'tid'  => $textId
        ]);
    }

    /**
     * Update the meeting QCM of one meeting
     * @param $qcmId
     * @param $newOtherChoice
     * @return bool
     */
    public function updateMeetingQCM($qcmId, $newOtherChoice) {
        $stmt = $this->connection->prepare("UPDATE MeetingQCM SET other_choice = :oc WHERE id = :qid");
        return $stmt->execute([
            'oc' => $newOtherChoice,
            'qid' => $qcmId
        ]);
    }

    /**
     * Getting FollowUpBook by id
     * @param $id
     * @return mixed
     */
    public function getFollowUpBook($id) {
            $stmt = $this->connection->prepare("SELECT * FROM FollowUpBook WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Insert new meeting in MeetingBook
     * @param $followUpId
     * @param $name
     * @param $startDate
     * @param $endDate
     * @param $meetingDate
     * @param $validation
     * @return false|string
     */
    public function insertMeetingBook($followUpId, $name, $startDate, $endDate, $meetingDate, $validation) {
        $stmt = $this->connection->prepare("INSERT INTO MeetingBook (followup_id, name, start_date, end_date, meeting_date, validation)
                                     VALUES (:fid, :name, :start, :end, :mdate, :val)");
        $stmt->execute([
            'fid' => $followUpId,
            'name' => $name,
            'start' => $startDate,
            'end' => $endDate,
            'mdate' => $meetingDate,
            'val' => $validation
        ]);
        return $this->connection->lastInsertId();
    }

    /**
     * Get all meetings for a follow-up id
     * @param $followUpId
     * @return array|false
     */
    public function getMeetingsByFollowUp($followUpId) {
        $stmt = $this->connection->prepare("SELECT * FROM MeetingBook WHERE followup_id = :fid");
        $stmt->execute(['fid' => $followUpId]);
        return $stmt->fetchAll();
    }

    /**
     * Insert a new meeting QCM in a meeting
     * @param $meetingId
     * @param $title
     * @param $choices
     * @param $otherChoice
     * @return void
     */
    public function insertMeetingQCM($meetingId, $title, $choices, $otherChoice) {
        $stmt = $this->connection->prepare("INSERT INTO MeetingQCM (meeting_id, title, choices, other_choice) 
                                     VALUES (:mid, :t, :c, :o)");
        $stmt->execute([
            'mid' => $meetingId,
            't' => $title,
            'c' => $choices,
            'o' => $otherChoice
        ]);
    }

    /**
     * Get the meeting QCM by an id
     * @param $meetingId
     * @return array|false
     */
    public function getQCMByMeeting($meetingId) {
        $stmt = $this->connection->prepare("SELECT * FROM MeetingQCM WHERE meeting_id = :mid");
        $stmt->execute(['mid' => $meetingId]);
        return $stmt->fetchAll();
    }

    /**
     * Insert a new meeting text int a meeting
     * @param $meetingId
     * @param $title
     * @param $response
     * @return void
     */
    public function insertMeetingText($meetingId, $title, $response) {
        $stmt = $this->connection->prepare("INSERT INTO MeetingTexts (meeting_id, title, response) VALUES (:mid, :t, :r)");
        $stmt->execute([
            'mid' => $meetingId,
            't' => $title,
            'r' => $response
        ]);
    }

    /**
     * Get the text of one metting
     * @param $meetingId
     * @return array|false
     */
    public function getTextsByMeeting($meetingId) {
        $stmt = $this->connection->prepare("SELECT * FROM MeetingTexts WHERE meeting_id = :mid");
        $stmt->execute(['mid' => $meetingId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all info of one student
     * @param $userId
     * @return array|mixed
     */
    public function getStudentInfo($userId) {
        $stmt = $this->connection->prepare("
        SELECT u.id, u.nom, u.prenom, u.email, u.telephone, u.activite
        FROM User u
        WHERE u.id = :uid AND u.role = 1
        LIMIT 1
    ");
        $stmt->execute(['uid' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result : [];
    }

    /**
     * Get all info of one professor
     * @param $userId
     * @return array|mixed
     */
    public function getProfessorInfo($userId) {
        // get associated student's group
        $group = $this->getGroupByUserId($userId);
        if ($group && $group['conv_id'] !== null) {
            // Searching a professeur, connected with the same conv_id
            $stmt = $this->connection->prepare("
            SELECT u.id, u.nom, u.prenom, u.email, u.telephone, u.activite
            FROM User u
            JOIN Groupe g ON g.user_id = u.id
            WHERE g.conv_id = :cid
              AND u.role = 2
            LIMIT 1
        ");
            $stmt->execute(['cid' => $group['conv_id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result : [];
        }
        return [];
    }

    /**
     * Get all info of one Mentor
     * @param $userId
     * @return array|mixed
     */
    public function getMentorInfo($userId) {
        // get associated student's group
        $group = $this->getGroupByUserId($userId);
        if ($group && $group['conv_id'] !== null) {
            // Searching a maître de stage, connected with the same conv_id
            $stmt = $this->connection->prepare("
            SELECT u.id, u.nom, u.prenom, u.email, u.telephone, u.activite 
            FROM User u
            JOIN Groupe g ON g.user_id = u.id
            WHERE g.conv_id = :cid
              AND u.role = 3
            LIMIT 1
        ");
            $stmt->execute(['cid' => $group['conv_id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result : [];
        }
        return [];
    }

    /**
     * Set a stage to end
     * @param $convId
     * @return void
     */
    public function setEndStage($convId): void{
        $sqlUpdate = "UPDATE Groupe SET Groupe.onStage = 0 WHERE Groupe.conv_id = :conv_id";
        $stmt = $this->connection->prepare($sqlUpdate);
        $stmt->execute([':conv_id' => $convId]);
    }

    public function getCompanyById(int $id){
        $sql = "select * from company where id = :id;";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCompanyByUserId(int $id): array {
        $sql = "SELECT Company.id, Company.name, Company.size, Company.address, Company.Siret, 
                   Company.postal_code, Company.phone_number, Company.city, Company.country, 
                   Company.APE_code, Company.legal_status
            FROM Company 
            JOIN User_Company ON User_Company.company_id = Company.id 
            WHERE User_Company.user_id = :id;";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        try {
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC); // Récupère une seule ligne

            if ($result) {
                return [
                    'id' => $result['id'],
                    'name' => $result['name'],
                    'size' => $result['size'],
                    'address' => $result['address'],
                    'siret' => $result['Siret'],
                    'postal_code' => $result['postal_code'],
                    'phone_number' => $result['phone_number'],
                    'city' => $result['city'],
                    'country' => $result['country'],
                    'APE_code' => $result['APE_code'],
                    'legal_status' => $result['legal_status']
                ];
            }

            // Si aucun résultat trouvé
            return [];
        } catch (PDOException $e) {
            // Gestion des erreurs : journalisation, levée d'exception, ou retour d'une réponse vide
            error_log("Database error: " . $e->getMessage());
            return [];
        }
    }

    public function getPreAgreementByIdGroup(int $id){
        $sql = "SELECT Convention.id FROM Convention
            WHERE Convention.id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result; // Return null if no rows are found
    }


    public function getAllMentor(): false|array
    {
        $sql = "select id, nom, prenom from User where role = 3   ";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function insertInputsPreAgreementStudent($json, int $idStudent, int $idMentor, int $idProfessor = null): void {
        if (is_array($json)) {
            $json = json_encode($json);
        }

        if ($idProfessor !== null && $idMentor !== -1) {
            $sql = "INSERT INTO Pre_Agreement (id, status, inputs, idStudent, idMentor, idProfessor) VALUES (DEFAULT, 0, :inputs, :idStudent, :idMentor, :idProfessor)";
        }
        else if ($idProfessor == null && $idMentor == -1) {
            $sql = "INSERT INTO Pre_Agreement (id, status, inputs, idStudent) VALUES (DEFAULT, 0, :inputs, :idStudent)";
        }
        else if ($idProfessor == null && $idMentor != -1){
            $sql = "INSERT INTO Pre_Agreement (id, status, inputs, idStudent, idMentor) VALUES (DEFAULT, 0, :inputs, :idStudent, :idMentor)";
        }
        else {
            $sql = "INSERT INTO Pre_Agreement (id, status, inputs, idStudent, idProfessor) VALUES (DEFAULT, 0, :inputs, :idStudent, :idProfessor)";
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':inputs', $json, PDO::PARAM_STR);
        $stmt->bindParam(':idStudent', $idStudent, PDO::PARAM_INT);
        $stmt->bindParam(':idMentor', $idMentor, PDO::PARAM_INT);

        if ($idProfessor !== null) {
            $stmt->bindParam(':idProfessor', $idProfessor, PDO::PARAM_INT);
        }
        if($idMentor !== -1){
            $stmt->bindParam(':idMentor', $idMentor, PDO::PARAM_INT);
        }

        $stmt->execute();
    }

    public function updateInputsPreAgreementStudent(int $id, $json ): void {
        $stmt = $this->connection->prepare("UPDATE Pre_Agreement set inputs = :json where id = :id;");
        $stmt->bindParam(':json', $json);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getPreAgreementFormById($id){
        $stmt = $this->connection->prepare("select id from Pre_Agreement WHERE id = :id");
        $stmt ->bindParam('id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getPreAgreementFormStudent($idPerson){
        $stmt = $this->connection->prepare("SELECT id FROM Pre_Agreement WHERE idStudent = :idPerson;");
        $stmt -> bindParam('idPerson', $idPerson);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getPreAgreementFormMentor($idPerson){
        $stmt = $this->connection->prepare("SELECT id FROM Pre_Agreement WHERE idMentor = :idPerson;");
        $stmt -> bindParam('idPerson', $idPerson);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getPreAgreementFormProfessor($idPerson){
        $stmt = $this->connection->prepare("SELECT id FROM Pre_Agreement WHERE idProfessor = :idPerson;");
        $stmt -> bindParam('idPerson', $idPerson);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getInputsPreAgreementForm($idPreAgreementForm){
        $stmt = $this->connection->prepare("SELECT inputs FROM Pre_Agreement WHERE id = :idPreAgreementForm;");
        $stmt->bindParam(':idPreAgreementForm', $idPreAgreementForm, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getInputsPreAgreementFormByUserId($idUser){
        $stmt = $this->connection->prepare("SELECT inputs FROM Pre_Agreement WHERE idStudent = :idUser and status = 1;");
        $stmt->bindParam(':idUser', $idUser, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /*public function getAllPreAgreementForm(){
        $stmt = $this->connection->prepare("select id from Pre_Agreement;");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }*/

    public function getMeetingByName($followUpId, $name) {
        $stmt = $this->connection->prepare("SELECT * FROM MeetingBook WHERE followup_id = :fid AND name = :nm LIMIT 1");
        $stmt->execute(['fid' => $followUpId, 'nm' => $name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insertCompetenceBilan($followup_id, $competence, $niveau, $commentaire) {
        $stmt = $this->connection->prepare("
        INSERT INTO Skill_Assessment (followup_id, competence, niveau, commentaire) 
        VALUES (:followup_id, :competence, :niveau, :commentaire)
    ");
        return $stmt->execute([
            'followup_id'  => $followup_id,
            'competence'   => $competence,
            'niveau'       => $niveau,
            'commentaire'  => $commentaire
        ]);
    }

    public function getCompetencesByFollowUpId($followup_id) {
        $stmt = $this->connection->prepare("
        SELECT * FROM Skill_Assessment WHERE followup_id = :followup_id
    ");
        $stmt->execute(['followup_id' => $followup_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteCompetencesByFollowUpId($followup_id) {
        $stmt = $this->connection->prepare("
        DELETE FROM Skill_Assessment WHERE followup_id = :followup_id
    ");
        return $stmt->execute(['followup_id' => $followup_id]);
    }


    public function getStudentsWithPreAgreementFormValid(): false|array
    {
        $stmt = $this->connection->prepare("select PA.id, User.nom, User.prenom from User Join sae.Pre_Agreement PA on User.id = PA.idStudent where PA.status=1;");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudentsWithPreAgreementFormInvalid(): false|array
    {
        $stmt = $this->connection->prepare("select PA.id, User.nom, User.prenom from User Join sae.Pre_Agreement PA on User.id = PA.idStudent where PA.status=0;");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function addConventionToGroup(int $groupId, string $path): void {
        $sql = "update Convention set path_convention = :path where id = :groupId";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':path', $path, PDO::PARAM_STR);
        $stmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getUserGroupByIdUser(int $id): ?int
    {
        $sql = "select conv_id from Groupe where user_id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['conv_id'] : null;

    }

    public function getAgreementByGroupId(int $id): ?string
    {
        $sql = "select path_convention from Convention where id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['path_convention'] : null;
    }

    public function PreAgreementIsValid(int $id): int
    {
        $stmt = $this->connection->prepare("select status from Pre_Agreement where id = :id;");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['status'];
    }

    public function setValidPreAgreement(int $id): void
    {
        $stmt = $this->connection->prepare("update Pre_Agreement set status = 1 where id = :id;");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * @throws Exception
     */
    public function addAlert($userId, $duration, $address, $study_level, $salary, $begin_date)
    {

        $query = 'insert into Alert (user_id, duration, address, study_level, salary, begin_date) VALUES (:user_id, :duration, :address, :study_level, :salary, :begin_date);';
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':duration', $duration, is_null($duration) ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':study_level', $study_level);
        $stmt->bindParam(':salary', $salary, is_null($salary) ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindParam(':begin_date', $begin_date);
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de l'insertion : " . $e->getMessage());
        }
    }



    /**
     * @throws Exception
     */
    public function deleteAlert($id): void
    {

        $query = 'delete from Alert where id = :id;';
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bindParam(':id', $id);
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression : " . $e->getMessage());
        }
    }

    public function getAlert(): array
    {

        $stmt = $this->getConnection()->prepare('select * from Alert;');
        $stmt->execute();
        $alerts = [];
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $alerts[] = $row;
        }

        return $alerts;
    }

    public function getAlertByUser($user_id): array
    {
        $stmt = $this->getConnection()->prepare('select * from Alert where user_id = :user_id;');
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $alerts = [];
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $alerts[] = $row;
        }
        return $alerts;
    }


    public function updateCompetenceBilan($competenceId, $niveau, $commentaire) {
        $stmt = $this->connection->prepare("
        UPDATE Skill_Assessment 
        SET niveau = :niveau, commentaire = :commentaire 
        WHERE id = :competenceId
    ");

        return $stmt->execute([
            'niveau'       => $niveau,
            'commentaire'  => $commentaire,
            'competenceId' => $competenceId
        ]);
    }

    public function deleteMeetingQCM($qcmId) {
        $stmt = $this->connection->prepare("
        DELETE FROM MeetingQCM 
        WHERE id = :qcmId
    ");
        return $stmt->execute(['qcmId' => $qcmId]);
    }


}