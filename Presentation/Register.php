<?php
session_start();

require_once "../Model/Database.php"; // db connect

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    echo "Erreur de connexion à la base de données.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et sanitization des données du formulaire d'inscription
    $role = $_POST['choice'];
    $function = htmlspecialchars(trim($_POST['function']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $name = htmlspecialchars(trim($_POST['name']));
    $firstname = htmlspecialchars(trim($_POST['firstname']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validation de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Adresse email invalide.";
        exit();
    }

    // Vérification de la correspondance des mots de passe
    if ($password !== $confirmPassword) {
        echo "Les mots de passe ne correspondent pas!";
        exit();
    }

    // Hachage du mot de passe
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Mapping des rôles
    $roleMapping = [
        'student' => 1,
        'tutorprofessor' => 2,
        'tutorcompany' => 3,
        'secretariat' => 4,
    ];
    $roleID = isset($roleMapping[$role]) ? $roleMapping[$role] : null;

    if (!$roleID) {
        echo "Rôle invalide.";
        exit();
    }

    // Vérification de la duplication d'email avant l'enregistrement
    $queryCheckEmail = "SELECT * FROM User WHERE email = :email";
    $stmtCheck = $conn->prepare($queryCheckEmail);
    $stmtCheck->bindValue(':email', $email);
    $stmtCheck->execute();

    if ($stmtCheck->rowCount() > 0) {
        echo "Email déjà enregistré. Veuillez utiliser un autre email.";
        exit();
    }

    // Insertion de l'utilisateur dans la base de données
    $query = "INSERT INTO User (nom, prenom, email, telephone, role, activite, status) 
              VALUES (:nom, :prenom, :email, :telephone, :role, :activite, 'email_not_validated')";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':nom', $name);
    $stmt->bindValue(':prenom', $firstname);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':telephone', $phone);
    $stmt->bindValue(':role', $roleID);
    $stmt->bindValue(':activite', $function);

    if ($stmt->execute()) {
        $userID = $conn->lastInsertId();

        if (!$userID) {
            echo "Erreur lors de la récupération de l'ID utilisateur.";
            exit();
        }

        // Insertion du mot de passe dans la table Password
        $queryPass = "INSERT INTO Password (user_id, password_hash, actif) VALUES (:user_id, :password_hash, :actif)";
        $stmtPass = $conn->prepare($queryPass);
        $stmtPass->bindValue(':user_id', $userID);
        $stmtPass->bindValue(':password_hash', $hashedPassword);
        $stmtPass->bindValue(':actif', 1); // Utilisation de 0 pour indiquer inactif tant que l'email n'est pas validé

        if ($stmtPass->execute()) {
            // Sauvegarde de l'email dans la session pour la commodité
            $_SESSION['user_email'] = $email;

            // Sauvegarde de l'ID utilisateur dans la session
            $_SESSION['user_id'] = $userID;

            $_SESSION['user_name'] = $name . " " . $firstname;

            // Redirection vers la page de validation de l'email
            header("Location: EmailValidationNotice.php");
            exit();
        } else {
            echo "Erreur lors de l'insertion du mot de passe.";
        }
    } else {
        echo "Erreur lors de la création de l'utilisateur.";
    }
}
?>
