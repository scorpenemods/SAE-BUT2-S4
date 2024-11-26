<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email
{
    private PHPMailer $mail;

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
        $this->mail->Host = 'smtp.gmail.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'Secretariat.lps.official@gmail.com'; // Email address
        $this->mail->Password = 'xtdu vchi sldx qmyi'; // Replace with environment variable in production
        $this->mail->SMTPSecure = 'tls'; // Encryption
        $this->mail->Port = 587; // SMTP port
    }

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
