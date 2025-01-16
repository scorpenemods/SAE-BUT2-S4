<?php
date_default_timezone_set('Europe/Paris');

class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection;

    private function __construct()
    {
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
            self::$instance->connect();
        }
        return self::$instance;
    }

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

    public function deleteFile(int $fileId): bool {
        $sql = "SELECT path FROM File WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':id' => $fileId]);
        $file = $stmt->fetch();

        if ($file && file_exists($file['path'])) {
            unlink($file['path']); // Supprime le fichier du serveur
        }

        $sql = "DELETE FROM File WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([':id' => $fileId]);
    }

    public function getFiles(int $studentId): array {
        $sql = "SELECT * FROM File WHERE user_id = :studentId";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':studentId' => $studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fileExists(string $name, int $userId): bool {
        $sql = "SELECT COUNT(*) FROM File WHERE name = :name AND user_id = :userId";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':name' => $name, ':userId' => $userId]);
        return $stmt->fetchColumn() > 0;
    }

    // User login verification
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


    // Adding a new user
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


    // Getting user information
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

    public function sendMessage($senderId, $receiverId, $message, $filePath = null)
    {
        try {
            $this->connection->beginTransaction();

            // Insertion du message
            $stmt = $this->connection->prepare("
                INSERT INTO Message (sender_id, receiver_id, contenu, timestamp)
                VALUES (:sender_id, :receiver_id, :contenu, NOW())
            ");
            $stmt->bindParam(':sender_id', $senderId);
            $stmt->bindParam(':receiver_id', $receiverId);
            $stmt->bindParam(':contenu', $message);
            $stmt->execute();

            $messageId = $this->connection->lastInsertId();

            // Si un fichier est associé, insérer dans Document et Document_Message
            if ($filePath) {
                // Insertion dans Document
                $stmt = $this->connection->prepare("
                    INSERT INTO Document (filepath)
                    VALUES (:filepath)
                ");
                $stmt->bindParam(':filepath', $filePath);
                $stmt->execute();
                $documentId = $this->connection->lastInsertId();

                // Insertion dans Document_Message
                $stmt = $this->connection->prepare("
                    INSERT INTO Document_Message (document_id, message_id)
                    VALUES (:document_id, :message_id)
                ");
                $stmt->bindParam(':document_id', $documentId);
                $stmt->bindParam(':message_id', $messageId);
                $stmt->execute();
            }

            $this->connection->commit();

            return $messageId; // Retourne l'ID du message
        } catch (PDOException $e) {
            $this->connection->rollBack();
            error_log("Erreur lors de l'envoi du message : " . $e->getMessage());
            return false;
        }
    }


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

    // Récupérer les contacts du même groupe que l'utilisateur
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

    public function getLastMessageId()
    {
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
    public function updateEmailValidationStatus($userId, $status)
    {
        $sql = "UPDATE User SET valid_email = :status WHERE id = :user_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // -------------------------------------------------------------------------------------- //

    public function getUserIdByEmail($email)
    {
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
            return $stmt->fetch(PDO::FETCH_ASSOC);  // Use FETCH_ASSOC for associative array
        } catch (PDOException $e) {
            echo "Error fetching preferences: " . $e->getMessage();
            return false;
        }
    }

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

    public function getConnection()
    {
        return $this->connection;
    }

    public function closeConnection(): void
    {
        $this->connection = null;
    }

    // ------------------------------------------------------------------- //


    //========================     Gropes methods        ================================== //
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

    //Get the groups a user belongs to.
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

    // get a message from a group
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

    // message to a group
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

    // get all groups with their members
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

    // -------------------- Add Note in Database ------------------------------------------ //

    function addNotes($studentId, $notesData, $pdo): void
    {
        try {
            // Vérification des données en entrée
            if (!is_array($notesData)) {
                throw new Exception("Les données fournies à addNotes doivent être un tableau.");
            }

            foreach ($notesData as $note) {
                if (!isset($note['sujet'], $note['appreciation'], $note['note'], $note['coeff'])) {
                    throw new Exception("Données manquantes dans une note : " . print_r($note, true));
                }

                if (empty($note['sujet']) || $note['note'] < 0 || $note['note'] > 20 || $note['coeff'] <= 0) {
                    throw new Exception("Données invalides dans une note : " . print_r($note, true));
                }
            }

            // Préparation de la requête d'insertion
            $query = $pdo->prepare("INSERT INTO Note (sujet, appreciation, note, coeff, user_id)
            VALUES (:sujet, :appreciation, :note, :coeff, :user_id)");

            // Exécution pour chaque note
            foreach ($notesData as $note) {
                $query->execute([
                    ':sujet' => $note['sujet'],
                    ':appreciation' => $note['appreciation'],
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


    public function updateNote($noteId, $userId, $sujet, $appreciation, $note, $coeff, $pdo)
    {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("
            UPDATE Note
            SET sujet = :sujet, appreciation = :appreciation, note = :note, coeff = :coeff
            WHERE id = :id AND user_id = :user_id
        ");

            $stmt->execute([
                ':sujet' => $sujet,
                ':appreciation' => $appreciation,
                ':note' => $note,
                ':coeff' => $coeff,
                ':id' => $noteId,
                ':user_id' => $userId
            ]);

            if ($stmt->rowCount() === 0) {
                throw new Exception("Aucune ligne modifiée. L'ID de la note est peut-être incorrect ou l'utilisateur n'a pas la permission.");
            }

            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw new Exception("Erreur lors de la mise à jour de la note : " . $e->getMessage(), 0, $e);
        }
    }


    public function deleteNote($noteId, $studentId, $pdo): void
    {
        try {
            // Préparer la requête pour supprimer la note
            $stmt = $pdo->prepare("DELETE FROM Note WHERE id = :id AND user_id = :user_id");

            // Exécuter la requête avec les paramètres
            $stmt->execute([
                ':id' => $noteId,
                ':user_id' => $studentId  // Utiliser l'ID de l'étudiant
            ]);

            // Vérification du nombre de lignes affectées
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


    public function getStudentNotes($studentId): array
    {
        $query = "SELECT * FROM Note WHERE user_id = :student_id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retourne un tableau associatif
    }





    // ------------------------------------------------------------------------------------------------------- //

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

    public function storeVerificationCode($userId, $code, $expires_at): bool
    {
        try {
            // Commence une transaction
            $this->connection->beginTransaction();

            // Supprime les codes de vérification existants pour cet utilisateur (au cas où)
            $sqlDelete = "DELETE FROM Verification_Code WHERE user_id = :user_id";
            $stmt = $this->connection->prepare($sqlDelete);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            if (!$stmt->execute()) {
                error_log("Erreur lors de la suppression des anciens codes pour l'utilisateur $userId");
            }

            // Insère un nouveau code de vérification
            $sqlInsert = "INSERT INTO Verification_Code (user_id, code, expires_at) VALUES (:user_id, :code, :expires_at)";
            $stmt = $this->connection->prepare($sqlInsert);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':code', $code, PDO::PARAM_STR);
            $stmt->bindParam(':expires_at', $expires_at, PDO::PARAM_STR);

            // Ajout de logs pour vérifier l'exécution
            if ($stmt->execute()) {
                error_log("Code de vérification $code inséré avec succès pour l'utilisateur $userId.");
            } else {
                error_log("Erreur lors de l'insertion du code de vérification pour l'utilisateur $userId.");
            }

            // Commit de la transaction
            $this->connection->commit();
            return true;
        } catch (PDOException $e) {
            // Rollback en cas d'erreur
            $this->connection->rollBack();
            error_log("Erreur lors du stockage du code de vérification : " . $e->getMessage());
            return false;
        }
    }

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

    public function getNotes($userId): array
    {
        $sql = "SELECT Note.id, Note.sujet, Note.appreciation, Note.note, Note.coeff
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
                $row['appreciation'] ?? '',
                $row['note'] ?? '',
                $row['coeff'] ?? ''
            );
        }
        return $notes;
    }


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

    public function getTotalNotifications($userId): int
    {
        $sql = "SELECT COUNT(*) FROM Notification WHERE user_id = :user_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    public function deleteOldNotifications($userId, $numberToDelete)
    {
        date_default_timezone_set('Europe/Paris');
        $sql = "DELETE FROM Notification WHERE user_id = :user_id ORDER BY created_at ASC LIMIT :limit";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $numberToDelete, PDO::PARAM_INT);
        $stmt->execute();
    }


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

    public function emailExists($email): bool
    {
        $sql = "SELECT COUNT(*) FROM User WHERE email = :email";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':email' => $email]);
        return (bool)$stmt->fetchColumn();
    }

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
    public function getAllValidUsers()
    {
        $conn = $this->getConnection();
        $query = "SELECT * FROM User WHERE status_user = 1";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    //---------------------- Livret de suvi --------------------------------- //

    public function getStudentInfo($userId): array
    {
        $sql = "
    SELECT 
        u.id AS etudiant_id,
        u.nom AS etudiant_nom,
        u.prenom AS etudiant_prenom,
        u.email AS etudiant_email,
        u.telephone AS etudiant_phone,
        u.activite AS etudiant_activity
    FROM Groupe g
    INNER JOIN User u ON g.user_id = u.id
    WHERE u.role = 1
    AND g.conv_id IN (
        SELECT g1.conv_id
        FROM Groupe g1
        WHERE g1.user_id = :user_id
    );
    ";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return ['error' => 'Aucune information trouvée pour l\'étudiant'];
        }

        return [
            'id' => $data['etudiant_id'],
            'nom' => $data['etudiant_nom'],
            'prenom' => $data['etudiant_prenom'],
            'email' => $data['etudiant_email'],
            'telephone' => $data['etudiant_phone'],
            'activite' => $data['etudiant_activity'],
        ];
    }


    public function getProfessorInfo($userId) {
        $sql = "
        SELECT 
            u.id AS professeur_id,
            u.nom AS nom,
            u.prenom AS prenom,
            u.email AS email,
            u.telephone AS telephone,
            u.activite AS activite
        FROM Groupe g
        LEFT JOIN User u ON g.conv_id = g.conv_id AND u.role = 2
        WHERE g.conv_id IN (
            SELECT g1.conv_id
            FROM Groupe g1
            WHERE g1.user_id = :user_id
        );
    ";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }





    public function getMentorInfo($userId): array
    {
        $sql = "
    SELECT 
        u.id AS maitre_stage_id,
        u.nom AS maitre_stage_nom,
        u.prenom AS maitre_stage_prenom,
        u.email AS maitre_stage_email,
        u.telephone AS maitre_stage_phone,
        u.activite AS maitre_stage_activity
    FROM Groupe g
    LEFT JOIN User u ON g.user_id = u.id
    WHERE u.role = 3
    AND g.conv_id IN (
        SELECT g1.conv_id
        FROM Groupe g1
        WHERE g1.user_id = :user_id
    );
    ";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data || empty($data['maitre_stage_id'])) {
            return ['error' => 'Aucune information trouvée pour le maître de stage'];
        }

        return [
            'id' => $data['maitre_stage_id'],
            'nom' => $data['maitre_stage_nom'],
            'prenom' => $data['maitre_stage_prenom'],
            'email' => $data['maitre_stage_email'],
            'telephone' => $data['maitre_stage_phone'],
            'activite' => $data['maitre_stage_activity'],
        ];
    }






    public function getFollowBookByUser($userId): array
    {
        $studentInfo = $this->getStudentInfo($userId);
        $professorInfo = $this->getProfessorInfo($userId);
        $mentorInfo = $this->getMentorInfo($userId);

        return [
            'etudiant' => $studentInfo,
            'professeur' => $professorInfo,
            'maitre_stage' => $mentorInfo,
        ];
    }

    public function setEndStage($userId): void
    {
        $sql = "UPDATE Groupe AS g
                SET g.onStage = 0
                WHERE g.conv_id = (
                    SELECT @test = conv_id
                    FROM Groupe
                    WHERE user_id = :user_id);";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
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
        $sql = "select id from Pre_Agreement where idGroup = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllMentor(): false|array
    {
        $sql = "select id, nom, prenom from User where role = 3";
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

    /*public function getAllPreAgreementForm(){
        $stmt = $this->connection->prepare("select id from Pre_Agreement;");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }*/

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

    public function PreAgreementIsValid(int $id){
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

}
