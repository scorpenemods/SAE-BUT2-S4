<?php
session_start();
require "../Model/Database.php";
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Временно включаем отображение ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

        if ($user && $db->deleteUser($userId)) {
            // Отправка письма с использованием PHPMailer
            $mail = new PHPMailer(true);

            try {
                // Настройки сервера
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // SMTP-сервер вашего почтового провайдера
                $mail->SMTPAuth = true;
                $mail->Username = 'secretariat.lps.official@gmail.com'; // Ваш адрес электронной почты
                $mail->Password = 'xtdu vchi sldx qmyi'; // Пароль или пароль приложения
                $mail->SMTPSecure = 'tls'; // Шифрование (tls или ssl)
                $mail->Port = 587; // Порт SMTP (587 для tls, 465 для ssl)

                // Получатели
                $mail->setFrom('no-reply@seciut.com', 'Le Petit Stage Team');
                $mail->addAddress($user['email'], $user['prenom'] . ' ' . $user['nom']);

                // Содержание письма
                $mail->isHTML(false);
                $mail->Subject = 'Suppression de votre compte';
                $mail->Body = "Cher " . $user['prenom'] . ",\n\nVotre compte a été supprimé du système.\n\nCordialement,\nL'équipe Le Petit Stage";

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