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
            require'Share/Config.php';
            $this->connection = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection error: " . $e->getMessage();
            exit;
        }
    }

    public function verifyLogin($login, $password)
    {
        $sql = "SELECT a_passwordsae.passwordsae_hash FROM a_usersae
                JOIN a_passwordsae ON a_usersae.user_id = a_passwordsae.user_id
                WHERE a_usersae.login = :login";

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':login', $login);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && password_verify($password, $result['passwordsae_hash'])) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function addUser($login, $password, $email, $telephone, $prenom, $activite, $role, $nom)
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $sqlUser = "INSERT INTO a_usersae (login, email, telephone, prenom, activite, role, nom) VALUES (:login, :email, :telephone, :prenom, :activite, :role, :nom)";

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

            $sqlPassword = "INSERT INTO a_passwordsae (user_id, passwordsae_hash) VALUES (:user_id, :passwordsae_hash)";
            $stmt = $this->connection->prepare($sqlPassword);
            $stmt->execute([
                ':user_id' => $userId,
                ':passwordsae_hash' => $passwordHash
            ]);

            return true;
        } catch (PDOException $e) {
            echo "Insert error: " . $e->getMessage();
            return false;
        }
    }

    public function getPersonByUsername($username)
    {
        // Requête SQL pour récupérer les données d'une personne par son username
        $sql = "SELECT nom, prenom, telephone, login, role, activite, email, user_id FROM a_usersae WHERE login = :login";

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':login', $username, PDO::PARAM_STR); // Lie le username à la requête
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                require_once "Person.php";
                // Créer une instance de Person avec les données récupérées
                return new \Person(
                    $result['nom'],
                    $result['prenom'],
                    $result['telephone'],
                    $result['login'],
                    $result['role'],
                    $result['activite'],
                    $result['email'],
                    $result['user_id'] // Utiliser user_id récupéré de la base de données
                );
            }
            return null; // Retourne null si aucun utilisateur n'est trouvé
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }


    public function closeConnection()
    {
        $this->connection = null;
    }
}

?>
