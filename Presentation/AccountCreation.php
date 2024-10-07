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
        header("Location: ../index.php");
        exit();
    } else {
        echo "Error while registering!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../rebase/Modely/DefaultStyles/styles.css">
</head>
<body>
<header class="navbar">
    <div class="navbar-left">
        <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
        <span class="app-name">Le Petit Stage</span>
    </div>

    <div class="navbar-right">
        <p>Guest</p>
        <!-- Language Switch -->
        <label class="switch">
            <input type="checkbox" id="language-switch" onchange="toggleLanguage()">
            <span class="slider round">
                    <span class="switch-sticker">🇫🇷</span> <!-- Sticker Français -->
                    <span class="switch-sticker switch-sticker-right">🇬🇧</span> <!-- Sticker English -->
                </span>
        </label>
        <!-- Theme Switch -->
        <label class="switch">
            <input type="checkbox" id="theme-switch" onchange="toggleTheme()">
            <span class="slider round">
                    <span class="switch-sticker switch-sticker-right">🌙</span> <!-- Sticker Dark Mode -->
                    <span class="switch-sticker">☀️</span> <!-- Sticker Light Mode -->
                </span>
        </label>
    </div>
</header>

<div class="container">
    <h1>Création du compte</h1>
    <form action="" method="post"> <!-- Убедитесь, что путь правильный -->
        <p>
            <input type="radio" name="choice" value="student" id="student" required />
            <label for="student">Étudiant</label>

            <input type="radio" name="choice" value="tutorprofessor" id="tutorprofessor" required />
            <label for="tutorprofessor">Professeur referant :</label>

            <input type="radio" name="choice" value="tutorcompany" id="tutorcompany" required />
            <label for="tutorcompany">Tuteur professionnel :</label>

            <input type="radio" name="choice" value="secritariat" id="secritariat" required />
            <label for="secritariat">Secrétariat :</label>
        </p>
        <p>
            <label for="function">Activité professionnelle/universitaire :</label>
            <input name="function" id="function" type="text" required/>
        </p>
        <p>
            <label for="email">E-mail :</label>
            <input name="email" id="email" type="email" required/>
        </p>
        <p>
            <label for="name">Nom :</label>
            <input name="name" id="name" type="text" required/>
        </p>
        <p>
            <label for="firstname">Prénom :</label>
            <input name="firstname" id="firstname" type="text" required/>
        </p>
        <p>
            <label for="phone">Téléphone :</label>
            <input name="phone" id="phone" type="text" required/>
        </p>
        <p>
            <label for="password">Mot de passe :</label>
            <input name="password" id="password" type="password" required/>
        </p>
        <p>
            <label for="confirm_password">Confirmer le mot de passe :</label>
            <input name="confirm_password" id="confirm_password" type="password" required/>
        </p>

        <button type="submit">Valider</button>
    </form>
</div>
<footer class="PiedDePage">
    <img src="../Resources/Logo_UPHF.png" alt="Logo uphf" width="10%">
    <a href="Redirection.php">Informations</a>
    <a href="Redirection.php">A propos</a>
</footer>
</body>
</html>