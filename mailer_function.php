<?php
// mailer_function.php
// VERSION: COMPLETE (Status Update + Official Receipt)

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) { require_once $autoloadPath; } 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ==================================================================
// 1. FUNCTION: SEND STATUS UPDATE (Existing mo 'to)
// ==================================================================
if (!function_exists('sendStatusEmail')) {
    function sendStatusEmail($toEmail, $clientName, $trackingNo, $newStatus) {
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) { return false; }

        $mail = new PHPMailer(true);
        try {
            // SMTP SETTINGS
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'royzxcasd@gmail.com'; 
            $mail->Password   = 'YOUR_APP_PASSWORD'; // <--- ILAGAY ANG PASSWORD MO DITO
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->Timeout    = 10;
            $mail->SMTPDebug  = 0;

            $mail->setFrom('royzxcasd@gmail.com', 'SLATE Logistics');
            $mail->addAddress($toEmail, $clientName);

            // COLORS
            $color = '#4e73df';
            if ($newStatus == 'Delivered') $color = '#1cc88a';
            if ($newStatus == 'Cancelled') $color = '#e74a3b';

            $mail->isHTML(true);
            $mail->Subject = "Update: Shipment #$trackingNo is $newStatus";
            
            // --- TIME SETTINGS ---
            date_default_timezone_set('Asia/Manila');
            $dateToday = date('F j, Y, g:i A');
            $headerColor = '#2c3e50'; 

            $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 0; }
                    .email-wrapper { padding: 40px 10px; }
                    .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e9ecef; }
                    .header { background-color: $headerColor; padding: 30px; text-align: center; }
                    .brand-name { color: #ffffff; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: 2px; font-style: italic; }
                    .content { padding: 40px 30px; color: #444444; line-height: 1.6; }
                    .status-box { background-color: #f8f9fa; border-left: 6px solid $color; padding: 20px; margin: 25px 0; border-radius: 4px; }
                    .status-value { font-size: 22px; font-weight: bold; color: $color; margin-top: 5px; }
                    .details-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    .details-table td { padding: 12px 0; border-bottom: 1px solid #eeeeee; }
                    .footer { background-color: #f1f3f5; padding: 20px; text-align: center; font-size: 12px; color: #999; }
                </style>
            </head>
            <body>
                <div class='email-wrapper'>
                    <div class='email-container'>
                        <div class='header'><div class='brand-name'>SLATE LOGISTICS</div></div>
                        <div class='content'>
                            <p>Hi <b>$clientName</b>,</p>
                            <p>This is an automated notification regarding your shipment.</p>
                            <div class='status-box'>
                                <div style='font-size:11px; font-weight:bold; color:#888;'>CURRENT STATUS</div>
                                <div class='status-value'>$newStatus</div>
                            </div>
                            <table class='details-table'>
                                <tr><td>Tracking Number</td><td style='text-align:right; font-weight:bold;'>$trackingNo</td></tr>
                                <tr><td>Update Time</td><td style='text-align:right; font-weight:bold;'>$dateToday</td></tr>
                            </table>
                        </div>
                        <div class='footer'>&copy; " . date('Y') . " Slate Logistics.</div>
                    </div>
                </div>
            </body>
            </html>";

            $mail->send();
            return true;
        } catch (Exception $e) { return false; }
    }
}

// ==================================================================
// 2. FUNCTION: SEND OFFICIAL RECEIPT (ITO ANG KULANG MO KANINA!)
// ==================================================================
if (!function_exists('sendReceiptEmail')) {
    function sendReceiptEmail($toEmail, $clientName, $orNumber, $amount, $trackingNo, $method) {
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) { return false; }

        $mail = new PHPMailer(true);
        try {
            // SMTP SETTINGS (Same as above)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'royzxcasd@gmail.com'; 
            $mail->Password   = 'YOUR_APP_PASSWORD'; // <--- ILAGAY ANG PASSWORD MO DITO
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->Timeout    = 10;
            $mail->SMTPDebug  = 0;

            $mail->setFrom('royzxcasd@gmail.com', 'SLATE Logistics Finance');
            $mail->addAddress($toEmail, $clientName);

            $mail->isHTML(true);
            $mail->Subject = "Official Receipt: $orNumber - Payment Confirmed";

            // TIME
            date_default_timezone_set('Asia/Manila');
            $datePaid = date('F j, Y, g:i A');

            // RECEIPT EMAIL BODY
            $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
                    .receipt-box { max-width: 500px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-top: 5px solid #2c3e50; }
                    .header { background-color: #f8f9fa; padding: 20px; text-align: center; border-bottom: 1px dashed #ddd; }
                    .brand { font-size: 20px; font-weight: bold; color: #2c3e50; margin: 0; }
                    .title { font-size: 14px; color: #666; margin-top: 5px; text-transform: uppercase; letter-spacing: 1px; }
                    .content { padding: 30px; }
                    .amount-box { text-align: center; margin-bottom: 25px; }
                    .amount-value { font-size: 32px; font-weight: bold; color: #28a745; margin-top: 5px; }
                    .details-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; border-bottom: 1px solid #f1f1f1; padding-bottom: 8px; }
                    .footer { background-color: #2c3e50; color: white; text-align: center; padding: 15px; font-size: 11px; }
                </style>
            </head>
            <body>
                <div class='receipt-box'>
                    <div class='header'>
                        <div class='brand'>SLATE LOGISTICS</div>
                        <div class='title'>Payment Confirmation</div>
                    </div>
                    <div class='content'>
                        <p>Hi <b>$clientName</b>,</p>
                        <p>We have successfully received your payment. Below is your official electronic receipt.</p>
                        <div class='amount-box'>
                            <div style='font-size:12px; color:#888;'>TOTAL AMOUNT PAID</div>
                            <div class='amount-value'>₱" . number_format($amount, 2) . "</div>
                        </div>
                        <div class='details-row'><span>OR Number</span><strong>$orNumber</strong></div>
                        <div class='details-row'><span>Date Paid</span><strong>$datePaid</strong></div>
                        <div class='details-row'><span>Payment Method</span><strong>" . strtoupper($method) . "</strong></div>
                        <div class='details-row'><span>Tracking No.</span><strong>$trackingNo</strong></div>
                    </div>
                    <div class='footer'>Thank you for your business!<br>This is a system-generated receipt.</div>
                </div>
            </body>
            </html>";

            $mail->send();
            return true;
        } catch (Exception $e) { return false; }
    }
}
?>