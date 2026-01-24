<?php
// admin_shipments_api.php

// 1. Silent Mode (Para walang text errors na sisira sa JSON)
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    if (!file_exists("connection.php")) throw new Exception("connection.php missing");
    include("connection.php");
    session_start();

    // 2. Check Mailer
    $mailerActive = false;
    if (file_exists("mailer_function.php")) {
        include("mailer_function.php");
        $mailerActive = true;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'update_status') {
            $id = intval($_POST['id']);
            $newStatus = mysqli_real_escape_string($conn, $_POST['status']);
            
            // --- QUERY FIX: User info na lang ang kunin (no fullname) ---
            $query = "SELECT s.*, a.email, a.username 
                      FROM shipments s 
                      LEFT JOIN accounts a ON s.user_id = a.id 
                      WHERE s.id='$id'";
            
            $q = mysqli_query($conn, $query);
            if (!$q) throw new Exception("DB Error: " . mysqli_error($conn));
            
            $row = mysqli_fetch_assoc($q);
            if (!$row) throw new Exception("Shipment ID not found");

            // SLA Logic
            $slaStatusUpdate = "";
            if ($newStatus === 'Delivered') {
                $targetVal = $row['target_delivery_date'];
                if (!empty($targetVal) && $targetVal != '0000-00-00 00:00:00') {
                    $actualDate = date('Y-m-d');
                    $targetDate = date('Y-m-d', strtotime($targetVal));
                    $slaResult = ($actualDate <= $targetDate) ? 'Met' : 'Breached';
                    $slaStatusUpdate = ", sla_status = '$slaResult'";
                }
            }

            // --- SQL FIX: TINANGGAL KO NA ANG 'updated_at = NOW()' ---
            $sql = "UPDATE shipments SET status = '$newStatus' $slaStatusUpdate WHERE id = '$id'";
            
            if (mysqli_query($conn, $sql)) {
                
                // SEND EMAIL
                $emailStatus = "Not Sent";
                if ($mailerActive && !empty($row['email'])) {
                    
                    // Name Logic: Sender Name -> Username -> Default
                    $clientName = !empty($row['sender_name']) ? $row['sender_name'] : ($row['username'] ?? 'Valued Client');
                    
                    $trackingNo = "TRK-" . str_pad($id, 6, "0", STR_PAD_LEFT);
                    
                    if (function_exists('sendStatusEmail')) {
                        // Suppress errors with @
                        $sent = @sendStatusEmail($row['email'], $clientName, $trackingNo, $newStatus);
                        $emailStatus = $sent ? "Sent" : "Failed";
                    }
                }

                echo json_encode([
                    'success' => true, 
                    'message' => 'Updated Successfully', 
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