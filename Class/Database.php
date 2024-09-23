<?php


class Database {
    private PDO $pdo;

    public function __construct() {
        // Database connection details
        $host = '141.94.245.139';
        $dbname = 's3081_BDD_Barkhane';
        $username = 'u3081_erRWAWL7zt';
        $password = 'ODyKebC@rSeyavay2Olz4!K!';
        $charset = 'utf8';

        // Data Source Name (DSN)
        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

        try {
            // Create a PDO instance (connect to the database)
            $this->pdo = new PDO($dsn, $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    // Function to authenticate the user
    public function authenticateUser($username, $password): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.passwordsae_hash 
                FROM a_usersae u 
                JOIN a_passwordsae p ON u.user_id = p.user_id 
                WHERE u.login = :login
            ");
            $stmt->execute([':login' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify the hashed password
            if ($user && password_verify($password, $user['passwordsae_hash'])) {
                return true;
            }

            return false;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de l'authentification : " . $e->getMessage());
        }
    }

    public function getUserByLogin($username) {
        $info = $this->pdo->query("SELECT * FROM a_usersae where login = $username");
        return $info->fetch(PDO::FETCH_ASSOC);
    }

    public function execute($text) {
        $a = $this->pdo->prepare($text);
        return $this->pdo->exec($a);
    }
}
