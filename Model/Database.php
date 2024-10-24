<?php
class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection;
    private function __construct(){}

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
        } catch (PDOException $e) {
            echo "Connection error: " . $e->getMessage();
            exit;
        }
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
    public function addUser($email, $password, $telephone, $prenom, $activite, $role, $nom,$status)
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);


        $sqlUser = "INSERT INTO User (email, telephone, prenom, activite, role, nom, status_user, valid_email) 
                VALUES (:email, :telephone, :prenom, :activite, :role, :nom, :status, 0)";

        try {
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
            $userId = $this->connection->lastInsertId();

            $sqlPassword = "INSERT INTO Password (user_id, password_hash, actif) VALUES (:user_id, :password_hash, 1)";
            $stmt = $this->connection->prepare($sqlPassword);
            $stmt->execute([
                ':user_id' => $userId,
                ':password_hash' => $passwordHash
            ]);

            // Insert default preferences
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

    // ====== Messagerie Contacts ======= //

    public function getGroupContacts($userId) {
        $query = "
        SELECT DISTINCT User.id, User.nom, User.prenom
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

    public function getMessagesBetweenUsers($userId, $contactId) {
        $query = "
        SELECT *
        FROM Messages
        WHERE (sender_id = :user_id AND receiver_id = :contact_id)
           OR (sender_id = :contact_id AND receiver_id = :user_id)
        ORDER BY timestamp ASC;
    ";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':contact_id', $contactId, PDO::PARAM_INT);
        $stmt->execute();
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

    public function approveUser($userId)
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

    public function storeEmailVerificationCode($userId, $code, $expires_at) {
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

    public function getVerificationCode($userId) {
        $sql = "SELECT * FROM Verification_Code WHERE user_id = :user_id AND expires_at > NOW()";
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

    public function getConnection() {
        return $this->connection;
    }

    public function closeConnection(): void
    {
        $this->connection = null;
    }

    // ------------------------------------------------------------------- //


    //========================     Gropes methods        ================================== //
    public function getAllStudents(): array
    {
        $query = "SELECT User.nom, User.prenom, User.telephone, User.role, User.activite, User.email, User.id FROM User
              WHERE User.role = 1";
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

    // -------------------- Student list in professor home ------------------------------------------ //
    public function getStudents($professorId): array
    {
        $query = "SELECT User.nom, User.prenom
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

    public function getProfessor(): array
    {
        $query = "SELECT DISTINCT User.nom, User.prenom, User.telephone, User.role, User.activite, User.email, User.id from User
                    where role = 2";
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

    public function getTutor(): array
    {
        $query = "SELECT DISTINCT User.nom, User.prenom, User.telephone, User.role, User.activite, User.email, User.id from User
                    where role = 3";
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
        $sql = "UPDATE User SET last_connexion = NOW() WHERE id = :id";

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de la dernière connexion : " . $e->getMessage());
            return false;
        }
    }
}
?>