<?php

class Database {
    private PDO $pdo;

    public function __construct() {
        // Détails de la connexion à la base de données
        $host = '141.94.245.139';
        $dbname = 's3081_BDD_Barkhane';
        $username = 'u3081_erRWAWL7zt';
        $password = 'ODyKebC@rSeyavay2Olz4!K!';
        $charset = 'utf8';

        // Data Source Name (DSN)
        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

        try {
            // Créer une instance PDO
            $this->pdo = new PDO($dsn, $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    // Fonction pour récupérer un objet Personne basé sur le login
    public function getPersonneByLogin(string $login): ?Personne
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT u.nom, u.prenom, u.telephone, u.role, u.activite, u.email
                FROM a_usersae u
                WHERE u.login = :login
            ");
            $stmt->execute([':login' => $login]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Créer et retourner un objet Personne avec les données récupérées
                return new Personne(
                    $user['nom'],
                    $user['prenom'],
                    $user['telephone'],
                    $user['role'],
                    $user['activite'],
                    $user['email']
                );
            }

            return null; // Retourne null si l'utilisateur n'existe pas
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des données utilisateur : " . $e->getMessage());
        }
    }
}
