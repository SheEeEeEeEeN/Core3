<?php
// update_status_pod.php

// 1. Settings & Includes
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

include("connection.php");

// Check kung existing ang mailer function
$mailerActive = false;
if (file_exists("mailer_function.php")) {
    include("mailer_function.php");
    $mailerActive = true;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid Request Method");
    }

    $id = intval($_POST['id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $imagePath = NULL;

    // 2. KUNIN MUNA ANG EMAIL AT USERNAME (Important para sa Email)
    // Kailangan natin to bago mag update para alam natin kung kanino isesend
    $query = "SELECT s.*, a.email, a.username as acc_sender_name 
              FROM shipments s 
              LEFT JOIN accounts a ON s.user_id = a.id 
              WHERE s.id='$id'";
    $q = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($q);
    
    if (!$row) throw new Exception("Shipment ID not found");

    // 3. PROCESS IMAGE UPLOAD (Kung meron)
    if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] == 0) {
        $targetDir = "uploads/";
        if (!file_exists($targetDir)) { mkdir($targetDir, 0777, true); }

        $fileName = "POD_" . $id . "_" . time() . ".jpg"; 
        $targetFilePath = $targetDir . $fileName;
        
        $check = getimagesize($_FILES["proof_image"]["tmp_name"]);
        if($check === false) throw new Exception("File is not a valid image.");

        if (move_uploaded_file($_FILES["proof_image"]["tmp_name"], $targetFilePath)) {
            $imagePath = $fileName;
        } else {
            throw new Exception("Failed to save image.");
        }
    }

    // 4. SLA Logic
    $slaSql = "";
    if ($status === 'Delivered') {
        $targetVal = $row['target_delivery_date'];
        if (!empty($targetVal) && $targetVal != '0000-00-00 00:00:00') {
            $actual = date('Y-m-d');
            $target = date('Y-m-d', strtotime($targetVal));
            $newSla = ($actual <= $target) ? 'Met' : 'Breached';
            $slaSql = ", sla_status = '$newSla'";
        }
    }

    // 5. UPDATE DATABASE
    // Note: Tinanggal ko ang 'updated_at' kasi wala sa table mo base sa screenshot mo kanina
    if ($imagePath) {
        $sql = "UPDATE shipments SET status='$status', proof_image='$imagePath' $slaSql WHERE id='$id'";
    } else {
        $sql = "UPDATE shipments SET status='$status' $slaSql WHERE id='$id'";
    }

    if (mysqli_query($conn, $sql)) {
        
        // 6. SEND EMAIL (Ito yung nawala kanina, binalik na natin!)
        $emailStatus = "Not Sent";
        
        if ($mailerActive && !empty($row['email'])) {
            // Name Logic: Sender Name -> Username -> Default
            $clientName = !empty($row['sender_name']) ? $row['sender_name'] : ($row['username'] ?? 'Valued Client');
            $trackingNo = "TRK-" . str_pad($id, 6, "0", STR_PAD_LEFT);
            
            if (function_exists('sendStatusEmail')) {
                // Suppress errors with @
                $sent = @sendStatusEmail($row['email'], $clientName, $trackingNo, $status);
                $emailStatus = $sent ? "Sent" : "Failed";
            }
        }

        echo json_encode([
            'success' => true, 
            'message' => 'Updated Successfully!',
            'email_status' => $emailStatus // Para makita mo sa alert kung nag send
        ]);

    } else {
        throw new Exception("Database Error: " . mysqli_error($conn));
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>