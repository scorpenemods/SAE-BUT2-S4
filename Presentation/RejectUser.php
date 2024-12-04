<?php
session_start();
require "../Model/Database.php";
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] != 4 && $_SESSION['user_role'] != 5)) {
    // Si l'utilisateur n'a pas le rôle requis (ici 4), on bloque l'accès
    header('location: AccessDenied.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['user_id'])) {
        $userId = $_POST['user_id'];

        $db = (Database::getInstance());
        $user = $db->getUserById($userId);

        if ($user && $db->rejectUser($userId)) {
            // Отправка письма с использованием PHPMailer
            $mail = new PHPMailer(true);

            try {
                // Настройки сервера
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // SMTP-сервер вашего почтового провайдера
                $mail->SMTPAuth = true;
                $mail->Username = 'Secretariat.lps.official@gmail.com'; // Ваш адрес электронной почты
                $mail->Password = 'xtdu vchi sldx qmyi'; // Пароль или пароль приложения
                $mail->SMTPSecure = 'tls'; // Шифрование (tls или ssl)
                $mail->Port = 587; // Порт SMTP (587 для tls, 465 для ssl)

                // Получатели
                $mail->setFrom('no-reply@seciut.com', 'Le Petit Stage Team');
                $mail->addAddress($user['email'], $user['prenom'] . ' ' . $user['nom']);

                // Содержание письма
                $mail->isHTML(false);
                $mail->Subject = 'Refus de votre compte';
                $mail->Body = "Cher " . $user['prenom'] . ",\n\nNous regrettons de vous informer que votre demande de création de compte a été refusée.\n\nCordialement,\nL'équipe Le Petit Stage";

                $mail->send();

                // Insert log entry
                $logQuery = "INSERT INTO Logs (user_id, type, description, date) VALUES (:user_id, 'ACTION', :description, NOW())";
                $stmtLog = $db->getConnection()->prepare($logQuery);
                $stmtLog->bindParam(':user_id', $_SESSION['user_id']);
                $description = "Rejected user account: ID {$userId}";
                $stmtLog->bindParam(':description', $description);
                $stmtLog->execute();

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