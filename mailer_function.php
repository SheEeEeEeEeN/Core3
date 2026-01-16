<?php
// mailer_function.php
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) { require_once $autoloadPath; } 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!function_exists('sendStatusEmail')) {
    function sendStatusEmail($toEmail, $clientName, $trackingNo, $newStatus) {
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) { return false; }

        $mail = new PHPMailer(true);
        try {
            // SMTP SETTINGS (Palitan mo ng credentials mo)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'royzxcasd@gmail.com'; 
            $mail->Password   = 'wgdfjpgdphkdziab'; 
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
            
        // --- PROFESSIONAL HTML EMAIL TEMPLATE (PH TIME) ---
            
            // 1. Set Timezone to Philippines
            date_default_timezone_set('Asia/Manila');
            
            // 2. Get Current Date & Time (Format: January 16, 2026, 2:30 PM)
            $dateToday = date('F j, Y, g:i A');

            // 3. Set Header Color (Slate Dark Blue)
            $headerColor = '#2c3e50'; 

            $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 0; }
                    .email-wrapper { padding: 40px 10px; }
                    .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e9ecef; }
                    
                    /* HEADER */
                    .header { background-color: $headerColor; padding: 30px; text-align: center; }
                    .brand-name { color: #ffffff; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: 2px; font-style: italic; }
                    
                    /* CONTENT */
                    .content { padding: 40px 30px; color: #444444; line-height: 1.6; }
                    .greeting { font-size: 18px; font-weight: bold; margin-bottom: 10px; color: #333; }
                    
                    /* STATUS CARD */
                    .status-box { background-color: #f8f9fa; border-left: 6px solid $color; padding: 20px; margin: 25px 0; border-radius: 4px; }
                    .status-label { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #888; font-weight: bold; }
                    .status-value { font-size: 22px; font-weight: bold; color: $color; margin-top: 5px; }
                    
                    /* TABLE DETAILS */
                    .details-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    .details-table td { padding: 12px 0; border-bottom: 1px solid #eeeeee; }
                    .details-label { font-size: 12px; color: #999; text-transform: uppercase; }
                    .details-data { font-size: 14px; font-weight: bold; color: #333; text-align: right; }
                    
                    /* BUTTON */
                    .btn-track { display: inline-block; background-color: $color; color: #ffffff !important; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: bold; font-size: 14px; margin-top: 30px; }
                    
                    /* FOOTER */
                    .footer { background-color: #f1f3f5; padding: 20px; text-align: center; font-size: 12px; color: #999; }
                </style>
            </head>
            <body>
                <div class='email-wrapper'>
                    <div class='email-container'>
                        
                        <div class='header'>
                            <div class='brand-name'>SLATE LOGISTICS</div>
                        </div>

                        <div class='content'>
                            <div class='greeting'>Hi $clientName,</div>
                            <p>This is an automated notification regarding the status of your shipment.</p>

                            <div class='status-box'>
                                <div class='status-label'>Current Status</div>
                                <div class='status-value'>$newStatus</div>
                            </div>

                            <table class='details-table'>
                                <tr>
                                    <td class='details-label'>Tracking Number</td>
                                    <td class='details-data'>$trackingNo</td>
                                </tr>
                                <tr>
                                    <td class='details-label'>Update Time</td>
                                    <td class='details-data'>$dateToday</td>
                                </tr>
                                <tr>
                                    <td class='details-label'>Service Type</td>
                                    <td class='details-data'>Express Freight</td>
                                </tr>
                            </table>

                            <div style='text-align: center;'>
                                <a href='#' class='btn-track'>Track Package</a>
                            </div>
                        </div>

                        <div class='footer'>
                            <p>&copy; " . date('Y') . " Slate Logistics. All rights reserved.</p>
                            <p>Please do not reply to this automated message.</p>
                        </div>

                    </div>
                </div>
            </body>
            </html>";

            $mail->send();
            return true;
        } catch (Exception $e) { return false; }
    }
}
?>