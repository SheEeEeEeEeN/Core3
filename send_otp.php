<?php
session_start();
include 'connection.php';
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

if (!isset($_POST['send_otp'])) {
    header("Location: request_otp.php");
    exit;
}

$email = $_POST['email'] ?? '';
$username = $_SESSION['username'] ?? '';

if (!$email || !$username) {
    die("Session expired. Please login again.");
}

// Generate OTP
$otp = rand(100000, 999999);
$expires_at = date("Y-m-d H:i:s", time() + 300);
$hashedOtp = password_hash($otp, PASSWORD_DEFAULT);

// Save OTP
$stmt = $conn->prepare("UPDATE accounts SET otp_hash=?, otp_expires_at=? WHERE username=?");
$stmt->bind_param("sss", $hashedOtp, $expires_at, $username);
$stmt->execute();
$stmt->close();

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'royzxcasd@gmail.com';
    $mail->Password   = 'wgdfjpgdphkdziab';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('royzxcasd@gmail.com', 'SLATE System');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = "Your Verification Code";

    // DARK MODE SAFE UI
    $mail->Body = '
    <div style="margin:0;padding:0;background:#f2f3f5;color:#000000;font-family:Arial,sans-serif;">
        
        <table align="center" width="100%" style="max-width:500px;margin:auto;border-radius:12px; padding:0;">
            <tr>
                <td style="
                    background:#ffffff;
                    border-radius:12px;
                    padding:30px;
                    text-align:center;
                    color:#202124;
                ">
                
                    <!-- Title -->
                    <h2 style="margin:0;color:#202124;font-size:22px;">
                        Verification Code
                    </h2>

                    <p style="color:#5f6368;font-size:15px;margin-bottom:25px;">
                        Use the code below to continue.
                    </p>

                    <!-- OTP Code -->
                    <div style="
                        font-size:60px;
                        font-weight:bold;
                        letter-spacing:12px;
                        margin:20px 0;
                        color:#1a73e8;
                    ">
                        ' . $otp . '
                    </div>

                    <!-- Expiry -->
                    <p style="font-size:13px;color:#5f6368;margin-top:20px;">
                        This code expires in <b>5 minutes</b>.
                    </p>

                    <!-- Footer -->
                    <p style="font-size:12px;color:#7e7e7e;margin-top:20px;">
                        If you didn’t request this code, you can ignore this message.
                    </p>

                </td>
            </tr>
        </table>
    </div>

    <!-- DARK MODE SUPPORT -->
    <style>
        @media (prefers-color-scheme: dark) {
            div {
                background: #0d1117 !important;
            }
            table td {
                background: #161b22 !important;
                color: #e6edf3 !important;
            }
            h2, p {
                color: #e6edf3 !important;
            }
        }
    </style>
    ';

    $mail->send();

    $_SESSION['otp_sent'] = true;
    header("Location: verify_otp.php");
    exit;

} catch (Exception $e) {
    echo "OTP could not be sent. Mailer Error: " . $mail->ErrorInfo;
}
