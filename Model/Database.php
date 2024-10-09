<?php
class Database
{
    private $connection;

    public function __construct()
    {
        // Initialise la connexion à la base de données lors de l'instanciation de la classe
        $this->connect();
    }

    private function connect()
    {
        try {
            require_once __DIR__ . '/Config.php'; // Inclut le fichier de configuration pour les paramètres de la base de données
            // Crée une nouvelle connexion PDO avec les paramètres définis dans Config.php
            $this->connection = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Active les exceptions pour les erreurs PDO
        } catch (PDOException $e) {
            // En cas d'erreur de connexion, afficher un message et arrêter l'exécution
            echo "Erreur de connexion : " . $e->getMessage();
            exit;
        }
    }

    // Vérification de la connexion d'un utilisateur
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

            // Vérification du mot de passe
            if ($result && password_verify($password, $result['password'])) {
                if ($result['status'] !== 'active') {
                    return 'pending'; // Si le compte est en attente de validation
                }
                return $result; // Retourne les données de l'utilisateur
            }
            return false; // Mot de passe ou login incorrect
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
            return false;
        }
    }

    // Ajouter un nouvel utilisateur
    public function addUser($login, $password, $email, $telephone, $prenom, $activite, $role, $nom)
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT); // Hash le mot de passe
        $status = 'pending'; // Statut de l'utilisateur par défaut "pending" (en attente)

        $sqlUser = "INSERT INTO User (login, email, telephone, prenom, activite, role, nom, status) VALUES (:login, :email, :telephone, :prenom, :activite, :role, :nom, :status)";

        try {
            // Insère les informations de l'utilisateur dans la table User
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
            $userId = $this->connection->lastInsertId(); // Récupère l'ID du dernier utilisateur inséré

            // Insère le mot de passe de l'utilisateur dans la table password
            $sqlPassword = "INSERT INTO password (user_id, password) VALUES (:user_id, :password)";
            $stmt = $this->connection->prepare($sqlPassword);
            $stmt->execute([
                ':user_id' => $userId,
                ':password' => $passwordHash
            ]);

            return true;
        } catch (PDOException $e) {
            echo "Erreur d'insertion : " . $e->getMessage();
            return false;
        }
    }

    // Récupération des informations utilisateur par nom d'utilisateur
    public function getPersonByUsername($username)
    {
        $sql = "SELECT nom, prenom, telephone, login, role, activite, email, id as user_id FROM User WHERE login = :login";

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':login', $username, PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                require_once "Person.php"; // Charge la classe Person
                // Retourne un objet Person avec les données récupérées
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
            echo "Erreur : " . $e->getMessage();
            return null;
        }
    }

    // ----------------------- Gestion des messages ------------------------------------------

    // Envoie un message
    public function sendMessage($senderId, $receiverId, $message, $filePath = null) {
        $sql = "INSERT INTO Message (sender_id, receiver_id, contenu, file_path, timestamp) VALUES (:sender_id, :receiver_id, :contenu, :file_path, :timestamp)";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                ':sender_id' => $senderId,
                ':receiver_id' => $receiverId,
                ':contenu' => $message,
                ':file_path' => $filePath,
                ':timestamp' => date("Y-m-d H:i:s") // Ajoute une date et heure pour le message
            ]);
            return true;
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
            return false;
        }
    }

    // Récupère les messages entre deux utilisateurs
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
            echo "Erreur : " . $e->getMessage();
            return [];
        }
    }

    // Supprime un message par son ID
    public function deleteMessage($messageId) {
        $sql = "DELETE FROM Message WHERE id = :message_id";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':message_id', $messageId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
            return false;
        }
    }

    // Récupère un message par son ID
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

    // ------------------------- Gestion des utilisateurs en attente et actifs --------------- //

    // Récupère tous les utilisateurs en attente de validation
    public function getPendingUsers()
    {
        $sql = "SELECT * FROM User WHERE status = 'pending'";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
            return [];
        }
    }

    // Récupère tous les utilisateurs actifs
    public function getActiveUsers()
    {
        $sql = "SELECT * FROM User WHERE status = 'active'";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
            return [];
        }
    }

    // Approuve un utilisateur en attente
    public function approveUser($userId)
    {
        $sql = "UPDATE User SET status = 'active' WHERE id = :id";
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([':id' => $userId]);
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
            return false;
        }
    }

    // Supprime un utilisateur par son ID
    public function rejectUser($userId)
    {
        $sql = "DELETE FROM User WHERE id = :id";
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute([':id' => $userId]);
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
            return false;
        }
    }

    // Supprime un utilisateur en réutilisant la fonction de rejet
    public function deleteUser($userId)
    {
        return $this->rejectUser($userId); // Réutilise la méthode rejectUser
    }

    // Récupère un utilisateur par son ID
    public function getUserById($userId)
    {
        $sql = "SELECT * FROM User WHERE id = :id";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':id' => $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
            return null;
        }
    }

    // Récupère l'ID du dernier message inséré
    public function getLastMessageId() {
        return $this->connection->lastInsertId();
    }

    // ------------------------- Fonctions pour la gestion des mots de passe oubliés --------------- //

    // Récupère un utilisateur par son email
    public function getUserByEmail($email)
    {
        $sql = "SELECT * FROM User WHERE email = :email";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':email' => $email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur de la base de données : " . $e->getMessage());
            return false;
        }
    }

    // Stocke le code de vérification pour la réinitialisation du mot de passe
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
            error_log("Erreur de la base de données : " . $e->getMessage());
            return false;
        }
    }

    // Vérifie la demande de réinitialisation de mot de passe par email et code de vérification
    public function getPasswordResetRequest($email, $verification_code)
    {
        $sql = "SELECT * FROM password_resets WHERE email = :email AND verification_code = :verification_code";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':email' => $email, ':verification_code' => $verification_code]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur de la base de données : " . $e->getMessage());
            return false;
        }
    }

    // Met à jour le mot de passe de l'utilisateur par email
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
            error_log("Erreur de la base de données : " . $e->getMessage());
            return false;
        }
    }

    // Supprime le code de vérification après réinitialisation du mot de passe
    public function deleteVerificationCode($email)
    {
        $sql = "DELETE FROM password_resets WHERE email = :email";
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':email' => $email]);
            return true;
        } catch (PDOException $e) {
            error_log("Erreur de la base de données : " . $e->getMessage());
            return false;
        }
    }

    // -------------------- Gestion de la vérification par email ------------------------------------------

    // Récupère le code de vérification par user_id
    public function getVerificationCode($userId) {
        $sql = "SELECT * FROM verification_codes WHERE user_id = :user_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Met à jour l'état de validation de l'email de l'utilisateur
    public function updateEmailValidationStatus($userId, $status) {
        $sql = "UPDATE User SET valid_email = :status WHERE id = :user_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Stocke un code de vérification pour un utilisateur
    public function storeEmailVerificationCode($userId, $code, $expires_at) {
        $sql = "INSERT INTO verification_codes (user_id, code, expires_at) VALUES (:user_id, :code, :expires_at)";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':expires_at', $expires_at, PDO::PARAM_STR);
        $stmt->execute();
    }

    // Récupère l'ID utilisateur par son email
    public function getUserIdByEmail($email) {
        $query = "SELECT id FROM User WHERE email = :email";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['id'] ?? null;
    }

    // Retourne la connexion à la base de données
    public function getConnection() {
        return $this->connection;
    }

    // Ferme la connexion à la base de données
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
