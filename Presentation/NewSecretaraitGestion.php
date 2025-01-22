<?php
// Inclusion des fichiers nécessaires pour la base de données et les objets Person
require "../Model/Database.php";
require "../Model/Person.php";


// Création d'une nouvelle instance de la classe Database
$database = (Database::getInstance());
$conn = $database->getConnection();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_id']) && $_POST['form_id'] === 'create_secretary') {
    error_log("Secretariat.php POSTED");
    // Initialise l'activité du user
    $function = isset($_POST['function']) ? htmlspecialchars(trim($_POST['function'])) : '';

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $name = htmlspecialchars(trim($_POST['name']));
    $firstname = htmlspecialchars(trim($_POST['firstname']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Valide l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Adresse email invalide.';
    }

    // Valide mdp
    if ($password !== $confirmPassword) {
        $errorMessage = 'Les mots de passe ne correspondent pas!';
    }

    // Check si l'email exist déjà
    $queryCheckEmail = "SELECT COUNT(*) FROM User WHERE email = :email";
    $stmtCheck = $conn->prepare($queryCheckEmail);
    $stmtCheck->bindParam(':email', $email);
    $stmtCheck->execute();
    $emailExists = $stmtCheck->fetchColumn();

    if ($emailExists > 0) {
        $errorMessage = 'Cet email est déjà enregistré. Veuillez utiliser un autre email.';
    }

    if (!$errorMessage) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Inserer user
        $query = "INSERT INTO User (nom, prenom, email, telephone, role, activite, valid_email, status_user, last_connexion, account_creation) 
                  VALUES (:nom, :prenom, :email, :telephone, 4, :activite, 0, 1, NOW(), NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':nom', $name);
        $stmt->bindValue(':prenom', $firstname);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':telephone', $phone);
        $stmt->bindValue(':activite', $function);

        if ($stmt->execute()) {
            $userID = $conn->lastInsertId();

            // Inserer mdp
            $queryPass = "INSERT INTO Password (user_id, password_hash, actif) VALUES (:user_id, :password_hash, 1)";
            $stmtPass = $conn->prepare($queryPass);
            $stmtPass->bindValue(':user_id', $userID);
            $stmtPass->bindValue(':password_hash', $hashedPassword);

            if ($stmtPass->execute()) {
                $_SESSION['user_email'] = $email;
                $userID = $_SESSION['user_id'];
                $_SESSION['user_name'] = $name . " " . $firstname;

            } else {
                $errorMessage = 'Erreur lors de l\'insertion du mot de passe.';
            }
        } else {
            $errorMessage = 'Erreur lors de la création de l\'utilisateur.';
        }
    }
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}