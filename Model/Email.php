<?php
// Mail managment
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// init .env variables
if (file_exists(__DIR__ . '/../.env')) {
    require __DIR__ . '/../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

class Email
{
    private PHPMailer $mail;

    /**
     * Get the mail
     * @return PHPMailer
     */
    public function getMail(): PHPMailer
    {
        return $this->mail;
    }

    public function __construct()
    {
        $smtp_host = $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST');
        $smtp_port = $_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT');
        $user = $_ENV['MAIL_USERNAME'] ?? getenv('MAIL_USERNAME');
        $password = $_ENV['MAIL_PASSWORD'] ?? getenv('MAIL_PASSWORD');
        $smtp_secure = $_ENV['SMTP_SECURE'] ?? getenv('SMTP_SECURE');


        // Create an instance of PHPMailer
        $this->mail = new PHPMailer(true);

        // Server settings
        $this->mail->isSMTP();
        $this->mail->Host = $smtp_host;
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $user; // Email address
        $this->mail->Password = $password;
        $this->mail->SMTPSecure = $smtp_secure; // Encryption
        $this->mail->Port = $smtp_port; // SMTP port
    }


    /**
     * Send a new mail
     * @param $to
     * @param $toName
     * @param $subject
     * @param $body
     * @param $isHtml
     * @return bool
     */
    public function sendEmail($to, $toName, $subject, $body, $isHtml = false): bool
    {
        try {
            // Recipients
            $this->mail->setFrom('no-reply@seciut.com', 'Le Petit Stage Team'); // Sender's email
            $this->mail->addAddress($to, $toName); // Add recipient

            // Content
            $this->mail->isHTML($isHtml); // Set email format to HTML or plain text
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;

            // Send the email
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            // Log the error in case of failure
            error_log("Email could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }
}