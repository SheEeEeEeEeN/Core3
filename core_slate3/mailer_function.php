<?php
// mailer_function.php

// 1. TIGAS NA PATH (Dahil sabi mo nasa vendor folder)
$autoloadPath = __DIR__ . '/vendor/autoload.php';

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    // Pag wala, stop na agad para di mag 500 Error
    error_log("CRITICAL: Vendor folder not found at: " . $autoloadPath);
    return;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!function_exists('sendStatusEmail')) {
    function sendStatusEmail($toEmail, $clientName, $trackingNo, $newStatus) {
        
        // Safety Check: Kung hindi nag-load ang library
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'royzxcasd@gmail.com'; 
            $mail->Password   = 'wgdfjpgdphkdziab'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            // TIMEOUT (Importante para iwas Error 500 pag mabagal net)
            $mail->Timeout    = 10; 
            $mail->SMTPDebug  = 0; // Off debug output

            // Recipients
            $mail->setFrom('royzxcasd@gmail.com', 'SLATE Logistics');
            $mail->addAddress($toEmail, $clientName);

            // --- 1. PREPARE DYNAMIC COLORS & CONTENT ---
            $statusColor = '#4e73df'; // Default Blue (In Transit/Processed)
            $statusMessage = 'Your shipment is moving to the next stage.';
            $icon = '📦';

            if ($newStatus == 'Delivered') {
                $statusColor = '#1cc88a'; // Green
                $statusMessage = 'Great news! Your package has been successfully delivered.';
                $icon = '✅';
            } elseif ($newStatus == 'Cancelled') {
                $statusColor = '#e74a3b'; // Red
                $statusMessage = 'We regret to inform you that this shipment has been cancelled.';
                $icon = '❌';
            } elseif ($newStatus == 'Pending') {
                $statusColor = '#f6c23e'; // Yellow
                $statusMessage = 'We have received your booking request.';
                $icon = '📝';
            }

            // Get Current Date/Time
            date_default_timezone_set('Asia/Manila');
            $updateTime = date("F j, Y | g:i A");

            // --- 2. BUILD THE HTML EMAIL (Professional Layout) ---
            $mail->isHTML(true);
            $mail->Subject = "Update: Shipment #$trackingNo is $newStatus $icon";
            
            $bodyContent = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
                    .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
                    .header { background-color: #2c3e50; padding: 20px; text-align: center; color: #ffffff; }
                    .header h1 { margin: 0; font-size: 24px; letter-spacing: 1px; }
                    .content { padding: 30px; color: #333333; line-height: 1.6; }
                    .status-badge { 
                        background-color: $statusColor; 
                        color: #ffffff; 
                        padding: 10px 20px; 
                        border-radius: 50px; 
                        font-weight: bold; 
                        display: inline-block; 
                        font-size: 18px;
                        margin: 10px 0;
                    }
                    .details-table { width: 100%; border-collapse: collapse; margin-top: 20px; background-color: #f9f9f9; border-radius: 5px; }
                    .details-table td { padding: 12px; border-bottom: 1px solid #eeeeee; }
                    .details-table td:first-child { font-weight: bold; color: #555; width: 40%; }
                    .btn-track {
                        display: block;
                        width: 200px;
                        margin: 30px auto;
                        padding: 12px;
                        background-color: #4e73df;
                        color: #ffffff !important;
                        text-align: center;
                        text-decoration: none;
                        border-radius: 5px;
                        font-weight: bold;
                    }
                    .footer { background-color: #eeeeee; padding: 20px; text-align: center; font-size: 12px; color: #888888; }
                </style>
            </head>
            <body>
                <div class='email-container'>
                    <div class='header'>
                        <h1>SLATE CORE3</h1>
                    </div>

                    <div class='content'>
                        <p>Hi <strong>$clientName</strong>,</p>
                        <p>$statusMessage</p>
                        
                        <div style='text-align: center;'>
                            <span class='status-badge'>$newStatus</span>
                        </div>

                        <table class='details-table'>
                            <tr>
                                <td>Tracking Number:</td>
                                <td style='font-family: monospace; font-size: 16px; color: #333;'>$trackingNo</td>
                            </tr>
                            <tr>
                                <td>Date Updated:</td>
                                <td>$updateTime</td>
                            </tr>
                            <tr>
                                <td>Service Type:</td>
                                <td>Freight Standard</td>
                            </tr>
                        </table>

                        <a href='http://localhost/last/user.php' class='btn-track'>Track Shipment</a>
                        
                        <p style='font-size: 13px; color: #666;'>If you have questions, reply to this email or contact support.</p>
                    </div>

                    <div class='footer'>
                        &copy; " . date("Y") . " Slate Freight Logistics. All rights reserved.<br>
                        This is an automated system message.
                    </div>
                </div>
            </body>
            </html>";

            $mail->Body = $bodyContent;
            
            // Plain Text Version (Fallback)
            $mail->AltBody = "Shipment Update for Tracking #$trackingNo. Status: $newStatus. Check your dashboard for details.";

            $mail->send();
            return true;

        } catch (Exception $e) {
            // Log error sa file lang
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }
}
?>