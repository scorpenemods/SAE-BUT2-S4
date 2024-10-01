<?php
global $conn;
require_once "../Model/Database.php"; // Подключение к базе данных
$database = new Database();
$conn = $database->getConnection();

if (!$conn) { // Убедитесь, что $conn не равно null
    echo "Ошибка подключения к базе данных.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получение данных из формы
    $role = $_POST['choice'];
    $function = $_POST['function'];
    $email = $_POST['email'];
    $name = $_POST['name'];
    $firstname = $_POST['firstname'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Проверка совпадения паролей
    if ($password !== $confirmPassword) {
        echo "Passwords do not match!";
        exit();
    }

    // Генерация хэша пароля
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Определение роли
    $roleMapping = [
        'student' => 1,
        'tutorprofessor' => 2,
        'tutorcompany' => 3,
        'secritariat' => 4,
    ];
    $roleID = $roleMapping[$role];

    // Вставка пользователя в базу данных
    $query = "INSERT INTO User (nom, prenom, login, email, telephone, role, activite) VALUES (:nom, :prenom, :login, :email, :telephone, :role, :activite)";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':nom', $name);
    $stmt->bindValue(':prenom', $firstname);
    $stmt->bindValue(':login', $email); // Используем email как логин
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

        // Перенаправление в зависимости от роли
        switch ($role) {
            case 'student':
                header("Location: Student.php");
                break;
            case 'tutorprofessor':
                header("Location: Professor.php");
                break;
            case 'tutorcompany':
                header("Location: maitre_stage.php");
                break;
            case 'secritariat':
                header("Location: secritariat.php");
                break;
        }
        exit();
    } else {
        echo "Error while registering!.";
    }
}
?>