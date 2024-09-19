<?php

class Database {
    private PDO $pdo;

    public function __construct($host, $dbname, $username, $password) {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
        try {
            $this->pdo = new PDO($dsn, $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function authenticateUser($username, $password): bool
    {
        try {
            $stmt = $this->pdo->prepare("
            SELECT p.password_hash 
            FROM user u 
            JOIN password p ON u.user_id = p.user_id 
            WHERE u.login = :login
        ");
            $stmt->execute([':login' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Si l'utilisateur existe et que le mot de passe est correct
            if ($user && password_verify($password, $user['password_hash'])) {
                return true;
            }

            return false;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de l'authentification : " . $e->getMessage());
        }
    }


    // Ajouter d'autres fonctions ici, comme l'ajout d'utilisateur, modification de mot de passe, etc.
}
?>
