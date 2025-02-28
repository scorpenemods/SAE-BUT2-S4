<?php
// Mail managment
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// init .env variables
/*
require __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
*/
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
        // Create an instance of PHPMailer
        $this->mail = new PHPMailer(true);

        // Server settings
        $this->mail->isSMTP();
        $this->mail->Host = $_ENV['SMTP_HOST'];
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $_ENV['MAIL_USERNAME']; // Email address
        $this->mail->Password = $_ENV['MAIL_PASSWORD'];
        $this->mail->SMTPSecure = $_ENV['SMTP_SECURE']; // Encryption
        $this->mail->Port = $_ENV['SMTP_PORT']; // SMTP port
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
