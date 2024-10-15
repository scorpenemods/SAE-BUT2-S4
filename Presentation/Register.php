<?php
session_start();

require_once "../Model/Database.php"; // db connect

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    echo "<script>console.error('Erreur de connexion à la base de données.');</script>";
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
        echo "<script>console.error('Adresse email invalide.');</script>";
        exit();
    }

    // Vérification de la correspondance des mots de passe
    if ($password !== $confirmPassword) {
        echo "<script>console.error('Les mots de passe ne correspondent pas!');</script>";
        exit();
    }

    // Hachage du mot de passe
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    echo "<script>console.log('Mot de passe en clair : $password');</script>";
    echo "<script>console.log('Mot de passe haché : $hashedPassword');</script>";

    // Mapping des rôles
    $roleMapping = [
        'student' => 1,
        'tutorprofessor' => 2,
        'tutorcompany' => 3,
        'secretariat' => 4,
    ];
    $roleID = isset($roleMapping[$role]) ? $roleMapping[$role] : null;

    if (!$roleID) {
        echo "<script>console.error('Rôle invalide.');</script>";
        exit();
    }

    // Vérification de la duplication d'email avant l'enregistrement
    $queryCheckEmail = "SELECT * FROM User WHERE email = :email";
    $stmtCheck = $conn->prepare($queryCheckEmail);
    $stmtCheck->bindValue(':email', $email);
    $stmtCheck->execute();

    if ($stmtCheck->rowCount() > 0) {
        echo "<script>console.error('Email déjà enregistré.');</script>";
        exit();
    }

    // Insertion de l'utilisateur dans la base de données
    $query = "INSERT INTO User (nom, prenom, email, telephone, role, activite, valid_email, status_user) 
              VALUES (:nom, :prenom, :email, :telephone, :role, :activite, 0, 0)";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':nom', $name);
    $stmt->bindValue(':prenom', $firstname);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':telephone', $phone);
    $stmt->bindValue(':role', $roleID);
    $stmt->bindValue(':activite', $function);

    if ($stmt->execute()) {
        $userID = $conn->lastInsertId();
        echo "<script>console.log('Utilisateur créé avec succès, ID : $userID');</script>";

        // Insertion du mot de passe dans la table Password
        $queryPass = "INSERT INTO Password (user_id, password_hash, actif) VALUES (:user_id, :password_hash, 1)";
        $stmtPass = $conn->prepare($queryPass);
        $stmtPass->bindValue(':user_id', $userID);
        $stmtPass->bindValue(':password_hash', $hashedPassword);

        if ($stmtPass->execute()) {
            echo "<script>console.log('Mot de passe inséré avec succès pour l\'utilisateur $userID');</script>";

            // Sauvegarde de l'email dans la session
            $_SESSION['user_email'] = $email;
            $_SESSION['user_id'] = $userID;
            $_SESSION['user_name'] = $name . " " . $firstname;

            // Redirection vers la page de validation de l'email
            header("Location: EmailValidationNotice.php");
            exit();
        } else {
            echo "<script>console.error('Erreur lors de l\'insertion du mot de passe.');</script>";
        }
    } else {
        echo "<script>console.error('Erreur lors de la création de l\'utilisateur.');</script>";
    }
}
?>
