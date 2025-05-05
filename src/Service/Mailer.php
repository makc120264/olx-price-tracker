<?php

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    /**
     * @param string $to
     * @param string $subject
     * @param string $body
     * @return bool
     */
    public function send(string $to, string $subject, string $body): bool
    {
        $headers = 'From: ' . $_ENV['MAIL_FROM'] . "\r\n" .
            'Content-Type: text/plain; charset=utf-8';

        return mail($to, $subject, $body, $headers);
    }

    /**
     * @param string $email
     * @param string $confirmUrl
     * @return bool
     */
    public function sendConfirmationEmail(string $email, string $confirmUrl): bool
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USER'];
            $mail->Password = $_ENV['SMTP_PASS'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['SMTP_PORT'];

            $mail->setFrom($_ENV['MAIL_FROM'], 'Price Tracker');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Confirm your subscription';
            $mail->Body = "Click the link to confirm your subscription: <a href='{$confirmUrl}'>Confirm</a>";
            $mail->AltBody = "Confirm your subscription: $confirmUrl";

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Log or just return false
            return false;
        }
    }
}
