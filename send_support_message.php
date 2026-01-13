<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php'; // Make sure you installed PHPMailer via Composer

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $message = trim($_POST['message']);

    if (!$email || empty($message)) {
        die('Invalid input.');
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'royzxcasd@gmail.com';
        $mail->Password   = 'wgdfjpgdphkdziab';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('royzxcasd@gmail.com', 'System Support'); // Use your system email
        $mail->addReplyTo($email, 'User'); // User email for reply
        $mail->addAddress('royzxcasd@gmail.com', 'System Support');

        $mail->isHTML(true);
        $mail->Subject = 'New Support Message';
        $mail->Body    = "<strong>Email:</strong> {$email}<br><strong>Message:</strong><br>{$message}";

        $mail->send();
        echo "<script>alert('Message sent successfully.'); window.location.href='request_otp.php';</script>";
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Invalid request.";
}
