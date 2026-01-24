<?php
// admin_shipments_api.php
// FINAL VERSION: With 'Paid' Auto-Update + Email Fix

// 1. Error Reporting (Para makita sa network tab kung may crash)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Keep 0 to output JSON only

header('Content-Type: application/json');

try {
    if (!file_exists("connection.php")) throw new Exception("connection.php missing");
    include("connection.php");
    session_start();

    // 2. Load Mailer (Without suppressing errors)
    $mailerActive = false;
    if (file_exists("mailer_function.php")) {
        include("mailer_function.php");
        $mailerActive = true;
    } else {
        error_log("⚠️ Warning: mailer_function.php not found.");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'update_status') {
            $id = intval($_POST['id']);
            $newStatus = mysqli_real_escape_string($conn, $_POST['status']);
            
            // Get Info
            $query = "SELECT s.*, a.email, a.username 
                      FROM shipments s 
                      LEFT JOIN accounts a ON s.user_id = a.id 
                      WHERE s.id='$id'";
            
            $q = mysqli_query($conn, $query);
            if (!$q) throw new Exception("DB Error: " . mysqli_error($conn));
            
            $row = mysqli_fetch_assoc($q);
            if (!$row) throw new Exception("Shipment ID not found");

            // --- ✨ THE FIX: PAYMENT & SLA LOGIC ✨ ---
            
            $slaStatusUpdate = "";
            $paymentUpdate = ""; // Variable para sa Payment Status

            if ($newStatus === 'Delivered') {
                
                // 1. GAWING 'PAID' ANG PAYMENT (Ito ang kulang mo kanina)
                $paymentUpdate = ", payment_status = 'Paid'";

                // 2. SLA Check
                $targetVal = $row['target_delivery_date'];
                if (!empty($targetVal) && $targetVal != '0000-00-00 00:00:00') {
                    $actualDate = date('Y-m-d');
                    $targetDate = date('Y-m-d', strtotime($targetVal));
                    $slaResult = ($actualDate <= $targetDate) ? 'Met' : 'Breached';
                    $slaStatusUpdate = ", sla_status = '$slaResult'";
                }
            }

            // --- SQL UPDATE COMMAND ---
            // Idinagdag natin ang variable na $paymentUpdate dito
            $sql = "UPDATE shipments 
                    SET status = '$newStatus' $slaStatusUpdate $paymentUpdate 
                    WHERE id = '$id'";
            
            if (mysqli_query($conn, $sql)) {
                
                // --- EMAIL LOGIC (FIXED) ---
                $emailStatus = "Not Sent (No Email or Mailer Off)";
                
                if ($mailerActive && !empty($row['email'])) {
                    $clientName = !empty($row['sender_name']) ? $row['sender_name'] : ($row['username'] ?? 'Valued Client');
                    $trackingNo = "TRK-" . str_pad($id, 6, "0", STR_PAD_LEFT);
                    
                    if (function_exists('sendStatusEmail')) {
                        // Tinanggal ko ang '@' para mahuli kung may error ang mailer
                        try {
                            $sent = sendStatusEmail($row['email'], $clientName, $trackingNo, $newStatus);
                            $emailStatus = $sent ? "Sent Successfully" : "Mailer Failed";
                        } catch (Exception $mailEx) {
                            $emailStatus = "Mailer Error: " . $mailEx->getMessage();
                        }
                    } else {
                        $emailStatus = "Function sendStatusEmail not found";
                    }
                }

                echo json_encode([
                    'success' => true, 
                    'message' => 'Status Updated to ' . $newStatus . ($newStatus == 'Delivered' ? ' (Marked as Paid)' : ''), 
                    'sla' => $slaResult ?? 'Pending',
                    'email_status' => $emailStatus
                ]);
            } else {
                throw new Exception("SQL Error: " . mysqli_error($conn));
            }
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid Request']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>