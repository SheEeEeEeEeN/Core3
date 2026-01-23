<?php
// FILE: update_shipments_api.php
// PURPOSE: Handle Status Updates (Cancel, Receive, Rate)

include("connection.php");
session_start();

header('Content-Type: application/json');

// Error Handling para hindi mag return ng HTML kung may error
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $action = $_POST['action'] ?? '';
        
        // Check if ID is present
        if (!isset($_POST['id']) && !isset($_POST['shipment_id'])) {
            throw new Exception("Missing Shipment ID");
        }

        $id = intval($_POST['id'] ?? $_POST['shipment_id']);

        // ======================================================
        // 🛑 CANCELLATION LOGIC
        // ======================================================
        if ($action === 'update_status' && $_POST['status'] === 'Cancelled') {
            
            $reason = mysqli_real_escape_string($conn, $_POST['reason']);
            
            // Siguraduhing may 'cancel_reason' column ka na sa DB!
            $sql = "UPDATE shipments 
                    SET status = 'Cancelled', 
                        cancel_reason = '$reason', 
                        updated_at = NOW() 
                    WHERE id = '$id'";

            if (mysqli_query($conn, $sql)) {
                echo json_encode(['success' => true, 'message' => 'Shipment cancelled.']);
            } else {
                throw new Exception("DB Error: " . mysqli_error($conn));
            }
            exit;
        }

        // ======================================================
        // ✅ RECEIVED / DELIVERED LOGIC
        // ======================================================
        if ($action === 'update_status' && $_POST['status'] === 'Delivered') {
            
            // Handle Image Upload
            $proofPath = NULL;
            if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] == 0) {
                $targetDir = "uploads/proofs/";
                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                
                $fileName = time() . "_" . basename($_FILES["proof_image"]["name"]);
                $targetFilePath = $targetDir . $fileName;
                
                if (move_uploaded_file($_FILES["proof_image"]["tmp_name"], $targetFilePath)) {
                    $proofPath = $targetFilePath;
                }
            }

            $proofSql = $proofPath ? ", proof_image = '$proofPath'" : "";

            $sql = "UPDATE shipments SET status = 'Delivered', payment_status = 'Paid' $proofSql, updated_at = NOW() WHERE id = '$id'";
            
            if (mysqli_query($conn, $sql)) {
                echo json_encode(['success' => true, 'message' => 'Marked as Received!']);
            } else {
                throw new Exception("DB Error: " . mysqli_error($conn));
            }
            exit;
        }

        // ======================================================
        // ⭐ RATING LOGIC
        // ======================================================
        if ($action === 'submit_rating') {
            $rating = intval($_POST['rating']);
            $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);

            $sql = "UPDATE shipments SET rating = '$rating', feedback_text = '$feedback' WHERE id = '$id'";
            
            if (mysqli_query($conn, $sql)) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception("DB Error: " . mysqli_error($conn));
            }
            exit;
        }
        
        // Fallback
        echo json_encode(['success' => false, 'message' => 'Invalid Action']);

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid Request Method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>