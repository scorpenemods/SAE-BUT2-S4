<?php
session_start();

require_once "../Model/Database.php"; // db connect
require '../vendor/autoload.php'; // php mailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    echo "Error connecting to database.";
    exit;
}
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 4) {
    // Si l'utilisateur n'a pas le rôle requis (ici 4), on bloque l'accès
    header('location: AccessDenied.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire d'inscription
    $role = $_POST['choice'];
    $function = $_POST['function'];
    $email = $_POST['email'];
    $name = $_POST['name'];
    $firstname = $_POST['firstname'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Checking password matches
    if ($password !== $confirmPassword) {
        echo "Passwords do not match!";
        exit();
    }

    // Password Hashing
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Role Definition
    $roleMapping = [
        'student' => 1,
        'tutorprofessor' => 2,
        'tutorcompany' => 3,
        'secritariat' => 4,
    ];
    $roleID = $roleMapping[$role];

    // Check for duplicate email before registration
    $queryCheckEmail = "SELECT * FROM User WHERE email = :email";
    $stmtCheck = $conn->prepare($queryCheckEmail);
    $stmtCheck->bindValue(':email', $email);
    $stmtCheck->execute();

    // Если email уже существует, выводим ошибку
    if ($stmtCheck->rowCount() > 0) {
        echo "Email déjà enregistré. Veuillez utiliser un autre email.";
        exit();
    }

    // Inserting a user into the database
    $query = "INSERT INTO User (nom, prenom, login, email, telephone, role, activite, status) VALUES (:nom, :prenom, :login, :email, :telephone, :role, :activite, 'Pending')";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':nom', $name);
    $stmt->bindValue(':prenom', $firstname);
    $stmt->bindValue(':login', $email);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':telephone', $phone);
    $stmt->bindValue(':role', $roleID);
    $stmt->bindValue(':activite', $function);

    if ($stmt->execute()) {
        $userID = $conn->lastInsertId();

        // Inserting a password into the password table
        $queryPass = "INSERT INTO password (user_id, password) VALUES (:user_id, :password)";
        $stmtPass = $conn->prepare($queryPass);
        $stmtPass->bindValue(':user_id', $userID);
        $stmtPass->bindValue(':password', $hashedPassword);
        $stmtPass->execute();

        // Verification code generation
        date_default_timezone_set('Europe/Paris'); //
        $verification_code = random_int(100000, 999999);
        $expires_at = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // Saving verification code
        $database->storeEmailVerificationCode($userID, $verification_code, $expires_at);

        // Sending code to email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'secretariat.lps.official@gmail.com';
            $mail->Password = 'xtdu vchi sldx qmyi';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('no-reply@seciut.com', 'Le Petit Stage Team');
            $mail->addAddress($email, $firstname . ' ' . $name);

            $mail->isHTML(true);
            $mail->Subject = 'Code de verification pour activer votre compte';
            $mail->Body = "Bonjour " . htmlspecialchars($firstname) . ",<br><br>Votre code de vérification est : <strong>" . $verification_code . "</strong><br>Ce code expirera dans 1 heure.<br><br>Cordialement,<br>L'équipe de Le Petit Stage.";

            $mail->send();
            $_SESSION['user_id'] = $userID;
            // save email in the session for convenience
            $_SESSION['user_email'] = $email;
            header("Location: ./EmailValidationNotice.php"); // Move to confirmation page
            exit();
        } catch (Exception $e) {
            echo "Erreur lors de l'envoi de l'email.";
        }
    } else {
        echo "Erreur lors de la création de l'utilisateur.";
    }
}