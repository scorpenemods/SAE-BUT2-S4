<?php

class Database {
    private $pdo;

    public function __construct($host, $dbname, $username, $password) {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
        try {
            $this->pdo = new PDO($dsn, $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function authenticateUser($username, $password) {
        try {
            // Récupérer le mot de passe haché de l'utilisateur
            $stmt = $this->pdo->prepare("
                SELECT p.password_hash
                FROM users u
                JOIN passwords p ON u.id = p.user_id
                WHERE u.login = :login
            ");
            $stmt->execute([':login' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Vérifier le mot de passe
                if (password_verify($password, $user['password_hash'])) {
                    return true;
                }
            }
            return false;
        } catch (PDOException $e) {
            throw new Exception("Failed to authenticate user: " . $e->getMessage());
        }
    }

    // Ajouter un utilisateur
    public function addUser($login, $email, $password) {
        try {
            $this->pdo->beginTransaction();

            // Ajouter l'utilisateur dans la table users
            $stmt = $this->pdo->prepare("INSERT INTO users (login, email) VALUES (:login, :email)");
            $stmt->execute([':login' => $login, ':email' => $email]);

            // Récupérer l'ID de l'utilisateur inséré
            $userId = $this->pdo->lastInsertId();

            // Hacher le mot de passe
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            // Ajouter le mot de passe dans la table passwords
            $stmt = $this->pdo->prepare("INSERT INTO passwords (user_id, password_hash) VALUES (:user_id, :password_hash)");
            $stmt->execute([':user_id' => $userId, ':password_hash' => $passwordHash]);

            $this->pdo->commit();
            return $userId; // Retourne l'ID de l'utilisateur
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new Exception("Failed to add user: " . $e->getMessage());
        }
    }

    // Modifier le mot de passe d'un utilisateur
    public function updatePassword($userId, $newPassword) {
        try {
            $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

            $stmt = $this->pdo->prepare("UPDATE passwords SET password_hash = :password_hash WHERE user_id = :user_id");
            $stmt->execute([':password_hash' => $passwordHash, ':user_id' => $userId]);

            return true;
        } catch (PDOException $e) {
            throw new Exception("Failed to update password: " . $e->getMessage());
        }
    }

    // Récupérer les informations d'un utilisateur
    public function getUser($userId) {
        $stmt = $this->pdo->prepare("SELECT u.id, u.login, u.email, p.password_hash 
                                     FROM users u 
                                     JOIN passwords p ON u.id = p.user_id 
                                     WHERE u.id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Supprimer un utilisateur
    public function deleteUser($userId) {
        try {
            $this->pdo->beginTransaction();

            // Supprimer le mot de passe
            $stmt = $this->pdo->prepare("DELETE FROM passwords WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $userId]);

            // Supprimer l'utilisateur
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([':id' => $userId]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new Exception("Failed to delete user: " . $e->getMessage());
        }
    }
}
?>
