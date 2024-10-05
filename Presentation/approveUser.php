<?php
session_start();
require "../Model/Database.php";
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Проверка роли пользователя
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 4) {
    echo "Access denied.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['user_id'])) {
        $userId = $_POST['user_id'];

        $db = new Database();
        $user = $db->getUserById($userId);

        if ($user && $db->approveUser($userId)) {
            // Отправка письма с использованием PHPMailer
            require '../vendor/autoload.php'; // Подключение autoload.php Composer

            $mail = new PHPMailer(true);

            try {
                // Настройки сервера
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // SMTP-сервер вашего почтового провайдера
                $mail->SMTPAuth = true;
                $mail->Username = 'secretariat.lps.official@gmail.com'; // Need to prevent using real data in open source code
                $mail->Password = 'xtdu vchi sldx qmyi'; // IL FAUT l'AMÉLIORER APRES | Password from gmail app access
                $mail->SMTPSecure = 'tls'; // Шифрование (tls или ssl)
                $mail->Port = 587; // Порт SMTP (587 для tls, 465 для ssl)

                // Получатели
                $mail->setFrom('no-reply@seciut.com', 'Le Petit Stage Team');
                $mail->addAddress($user['email'], $user['prenom'] . ' ' . $user['nom']);

                // Содержание письма
                $mail->isHTML(false); // Установите true, если вы отправляете HTML-письмо
                $mail->Subject = 'Account Approval';
                $mail->Body = "Dear " . $user['prenom'] . ",\n\nYour account has been approved. You can now log in to the system.\n\nBest regards,\nLe Petit Stage Team";

                $mail->send();
                echo 'success';
            } catch (Exception $e) {
                // Логирование ошибки (не отображайте ошибки пользователю в продакшене)
                error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                echo 'email_error';
            }
        } else {
            echo 'error';
        }
    } else {
        echo 'error';
    }
}
?>