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
        $sql = "SELECT * FROM File WHERE user_id = :studentId";
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

    /**
     * Delete a user's account
     * @param $userId
     * @return bool
     */
    public function deleteUser($userId)
    {
        return $this->rejectUser($userId); // test and reusing same method
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
    public function setUserPreferences($userId, $notification, $a2f, $darkmode): bool
    {
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

            // Delete messages in MessageGroupe
            $stmt = $this->connection->prepare("DELETE FROM MessageGroupe WHERE groupe_id = :group_id");
            $stmt->bindParam(':group_id', $groupId, PDO::PARAM_INT);
            $stmt->execute();

            // Delete entries in Document_Message related to the group's messages
            $stmt = $this->connection->prepare("
            DELETE dm FROM Document_Message dm
            JOIN MessageGroupe mg ON dm.message_id = mg.id
            WHERE mg.groupe_id = :group_id
        ");
            $stmt->bindParam(':group_id', $groupId, PDO::PARAM_INT);
            $stmt->execute();

            // Optionally delete documents if they are not linked to other messages
            // (This requires additional logic to check if the document is linked elsewhere)

            // Delete group members in Groupe
            $stmt = $this->connection->prepare("DELETE FROM Groupe WHERE conv_id = :group_id");
            $stmt->bindParam(':group_id', $groupId, PDO::PARAM_INT);
            $stmt->execute();

            // Delete the group in Convention
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
     * Add a note into database
     * @param $studentId
     * @param $notesData
     * @param $pdo
     * @return void
     * @throws Exception
     */
    function addNotes($studentId, $notesData, $pdo): void
    {
        try {
            // Vérification des données en entrée
            if (!is_array($notesData)) {
                throw new Exception("Les données fournies à addNotes doivent être un tableau.");
            }

            foreach ($notesData as $note) {
                if (!isset($note['sujet'], $note['note'], $note['coeff'])) {
                    throw new Exception("Données manquantes dans une note : " . print_r($note, true));
                }

                if (empty($note['sujet']) || $note['note'] < 0 || $note['note'] > 20 || $note['coeff'] <= 0) {
                    throw new Exception("Données invalides dans une note : " . print_r($note, true));
                }
            }

            // Préparation de la requête d'insertion
            $query = $pdo->prepare("INSERT INTO Note (sujet, note, coeff, user_id)
            VALUES (:sujet, :note, :coeff, :user_id)");

            // Exécution pour chaque note
            foreach ($notesData as $note) {
                $query->execute([
                    ':sujet' => $note['sujet'],
                    ':note' => floatval($note['note']),
                    ':coeff' => floatval($note['coeff']),
                    ':user_id' => $studentId
                ]);
            }
        } catch (PDOException $e) {
            // Gérer les erreurs de la base de données
            echo "Erreur lors de l'insertion des notes : " . $e->getMessage();
            error_log($e->getMessage());
            throw $e;
        } catch (Exception $e) {
            // Gérer les autres erreurs
            echo "Erreur : " . $e->getMessage();
            error_log($e->getMessage());
            throw $e;
        }
    }


    /**
     * Modify a note into database
     * @param $noteId
     * @param $studentId
     * @param $sujet
     * @param $coeff
     * @param $pdo
     * @return void
     * @throws Exception
     */
    public function updateNote($noteId, $studentId, $sujet, $coeff, $pdo): void
    {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("
            UPDATE Note
            SET sujet = :sujet, coeff = :coeff
            WHERE id = :id AND user_id = :user_id
        ");

            $stmt->execute([
                ':sujet' => $sujet,
                ':coeff' => $coeff,
                ':id' => $noteId,
                ':user_id' => $studentId
            ]);

            if ($stmt->rowCount() === 0) {
                throw new Exception("Aucune ligne modifiée. ID incorrect ou permissions insuffisantes.");
            }

            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Erreur SQL : " . $e->getMessage());
            throw new Exception("Erreur lors de la mise à jour : " . $e->getMessage(), 0, $e);
        }
    }


    /**
     * Add under note to a specific note
     * @param array $notesData
     * @param PDO $pdo
     * @return void
     * @throws Exception
     */
    function addUnderNotes(array $notesData, PDO $pdo): void
    {
        try {
            // Verification of entry data
            if (!is_array($notesData)) {
                throw new Exception("Les données fournies à addUnderNotes doivent être un tableau.");
            }

            // Verification of every under notes
            foreach ($notesData as $note) {
                if (!isset($note['description'], $note['note_id'], $note['note'])) {
                    throw new Exception("Données manquantes dans une sous-note : " . print_r($note, true));
                }

                if (empty($note['description']) || !is_numeric($note['note']) || $note['note'] < 0 || $note['note'] > 20) {
                    throw new Exception("Données invalides dans une sous-note : " . print_r($note, true));
                }
            }

            // Preparation of insertion request
            $query = $pdo->prepare("INSERT INTO Sous_Note (description, note_id, note) VALUES (:description, :note_id, :note_value)");

            // Insertion of every under notes
            foreach ($notesData as $note) {
                $query->execute([
                    ':description' => $note['description'],
                    ':note_id' => (int)$note['note_id'],
                    ':note_value' => (int)$note['note']
                ]);
            }
        } catch (PDOException $e) {
            // Handling database errors
            echo "Erreur lors de l'insertion des sous-notes : " . $e->getMessage();
            error_log($e->getMessage());
            throw $e;
        } catch (Exception $e) {
            // Handling other errors
            echo "Erreur : " . $e->getMessage();
            error_log($e->getMessage());
            throw $e;
        }
    }


    /**
     * Delete a note
     * @param $noteId
     * @param $studentId
     * @param $pdo
     * @return void
     */
    public function deleteNote($noteId, $studentId, $pdo): void
    {
        try {
            // Preparation of the delete note request
            $stmt = $pdo->prepare("DELETE FROM Note WHERE id = :id AND user_id = :user_id");

            // Run query with parameters
            $stmt->execute([
                ':id' => $noteId,
                ':user_id' => $studentId  // Using of student id
            ]);

            // Checking the number of rows affected
            if ($stmt->rowCount() > 0) {
                echo "Note supprimée avec succès";
            } else {
                echo "Aucune note trouvée avec cet ID et cet utilisateur. Annulation de la suppression.";
            }
        } catch (PDOException $e) {
            echo "Erreur lors de la suppression de la note : " . $e->getMessage();
            throw $e;
        }
    }

    /**
     * Delete under note for a specific note
     * @param $underNoteId
     * @param $pdo
     * @return void
     * @throws Exception
     */
    public function deleteUnderNote($underNoteId, $pdo): void {
        $stmt = $pdo->prepare("DELETE FROM Sous_Note WHERE sousNote_id = :id");
        $stmt->execute([':id' => $underNoteId]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("Aucune sous-note supprimée. L'ID est peut-être incorrect.");
        }
    }

    /**
     * Get the average of student notes
     * @param $noteId
     * @param $pdo
     * @return float|int|null
     */
    public function getMainNoteAverage($noteId, $pdo): float|int|null
    {
        // Retrieve all under notes linked to this note
        $stmt = $pdo->prepare("SELECT note FROM Sous_Note WHERE note_id = :note_id");
        $stmt->execute([':note_id' => $noteId]);
        $sousNotes = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // If no under note is found, return null
        if (!$sousNotes || count($sousNotes) === 0) {
            return null;
        }

        // Retrieve the main coefficient of the note
        $stmtCoeff = $pdo->prepare("SELECT coeff FROM Note WHERE id = :id");
        $stmtCoeff->execute([':id' => $noteId]);
        $coeff = $stmtCoeff->fetchColumn();

        $totaleSumNote = 0;
        $totaleSumCoeff = 0;

        // Calculation of the weighted sum of notes
        foreach ($sousNotes as $note) {
            $note = floatval($note);

            $totaleSumNote += $note * $coeff;
            $totaleSumCoeff += $coeff;
        }

        // Check that there is a total coefficient to avoid division by zero
        if ($totaleSumCoeff === 0) {
            return null;
        }

        // Calculate the weighted average
        return $totaleSumNote / $totaleSumCoeff;
    }


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
     * Get all under notes of a student
     * @param $studentId
     * @return array
     */
    public function getUnderNotes($studentId): array
    {
        $query = "SELECT
                sn.sousNote_id AS SousNoteID,
                sn.description AS Description,
                sn.note AS SousNote,
                n.id AS NoteID
              FROM
                Sous_Note sn
                JOIN Note n ON sn.note_id = n.id
              WHERE
                n.user_id = ?";

        $stmt = $this->connection->prepare($query);
        $stmt->execute([$studentId]);

        // Get all rows as associative array
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $underNotes = [];
        foreach ($rows as $row) {
            $underNotes[$row['NoteID']][] = new Sous_Note(
                $row['SousNoteID'],
                $row['Description'],
                $row['SousNote']
            );
        }
        return $underNotes;
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
}
