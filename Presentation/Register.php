<?php
session_start();

require_once "../Model/Database.php"; // db connect

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    echo "Erreur de connexion à la base de données.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et санитизация des données du formulaire d'inscription
    $role = $_POST['choice'];
    $function = htmlspecialchars(trim($_POST['function']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $name = htmlspecialchars(trim($_POST['name']));
    $firstname = htmlspecialchars(trim($_POST['firstname']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Валидация email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Adresse email invalide.";
        exit();
    }

    // Проверка совпадения паролей
    if ($password !== $confirmPassword) {
        echo "Les mots de passe ne correspondent pas!";
        exit();
    }

    // Хеширование пароля
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Определение роли
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

    // Проверка на дублирование email перед регистрацией
    $queryCheckEmail = "SELECT * FROM User WHERE email = :email";
    $stmtCheck = $conn->prepare($queryCheckEmail);
    $stmtCheck->bindValue(':email', $email);
    $stmtCheck->execute();

    if ($stmtCheck->rowCount() > 0) {
        echo "Email déjà enregistré. Veuillez utiliser un autre email.";
        exit();
    }

    // Вставка пользователя в базу данных
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

        // Вставка пароля в таблицу password
        $queryPass = "INSERT INTO password (user_id, password) VALUES (:user_id, :password)";
        $stmtPass = $conn->prepare($queryPass);
        $stmtPass->bindValue(':user_id', $userID);
        $stmtPass->bindValue(':password', $hashedPassword);
        $stmtPass->execute();

        // Сохраняем email в сессии для удобства
        $_SESSION['user_email'] = $email;

        // Сохраняем user_id в сессии
        $_SESSION['user_id'] = $userID;

        $_SESSION['user_name'] = $name . " " . $firstname;

        // Перенаправление на страницу подтверждения без отправки письма
        header("Location: ./EmailValidationNotice.php");
        exit();
    } else {
        echo "Erreur lors de la création de l'utilisateur.";
    }
}
?>
