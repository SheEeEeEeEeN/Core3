<?php
// FILE: update_shipment_api.php
// PURPOSE: Handle Status Updates (Cancel, Receive, Rate, SYNC CORE 1)

include("connection.php"); 
session_start();

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $action = $_POST['action'] ?? '';

        // ======================================================
        // 1. SYNC STATUS FROM CORE 1 (BASED ON CONTRACT NUMBER)
        // ======================================================
        if ($action === 'sync_core1') {
            
            // This 'tracking_code' comes from your Admin Table (It is the Contract Number)
            if (empty($_POST['tracking_code'])) {
                throw new Exception("Missing Contract/Tracking Number");
            }

            $localContractNumber = trim($_POST['tracking_code']); 
            
            // CORE 1 API URL
            $core1_url = "http://192.168.100.130/core1/api/shipments_api.php";

            // A. Fetch Data
            $ctx = stream_context_create(array('http' => array('timeout' => 5))); 
            $json_response = @file_get_contents($core1_url, false, $ctx);

            if (!$json_response) {
                throw new Exception("Failed to connect to Core 1 API");
            }

            $apiData = json_decode($json_response, true);
            $foundStatus = null;
            $matchedKey = "None";

            // B. Find Match
            if (isset($apiData['success']) && $apiData['success'] == true) {
                foreach ($apiData['data'] as $item) {
                    
                    // 1. Get identifiers from Core 1 JSON
                    // Based on your JSON, 'contract_number' is the key we want.
                    $c1_contract = $item['contract_number'] ?? ''; 
                    
                    // Backup keys (Just in case)
                    $c1_external = $item['external_tracking_no'] ?? ''; 
                    $c1_ship     = $item['shipment_code'] ?? '';
                    $c1_track    = $item['tracking_no'] ?? '';

                    // 2. Strict Comparison
                    // Does the Core 1 contract number match our Local contract number?
                    if ($c1_contract === $localContractNumber || 
                        $c1_external === $localContractNumber ||
                        $c1_ship === $localContractNumber || 
                        $c1_track === $localContractNumber) {
                        
                        $foundStatus = $item['status'];
                        $matchedKey = $localContractNumber;
                        break; // Stop loop once found
                    }
                }
            }

            // C. Update Local Database
            if ($foundStatus) {
                $cleanStatus = mysqli_real_escape_string($conn, strtoupper($foundStatus));

                // UPDATE using contract_number as the Unique ID
                $sql = "UPDATE shipments SET status = '$cleanStatus', updated_at = NOW() WHERE contract_number = '$localContractNumber'";
                
                if (mysqli_query($conn, $sql)) {
                    echo json_encode([
                        'success' => true, 
                        'message' => "Synced! Contract [$matchedKey] found. Status updated to: $cleanStatus", 
                        'new_status' => $cleanStatus
                    ]);
                } else {
                    throw new Exception("DB Update Failed: " . mysqli_error($conn));
                }
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => "No match found for Contract [$localContractNumber] in Core 1.",
                    'debug_tip' => "Check if the 'contract_number' column in Core 1 matches exactly."
                ]);
            }
            exit; // Stop script execution
        }

        // ======================================================
        // 2. OTHER ACTIONS (Require ID)
        // ======================================================

        $id = intval($_POST['id'] ?? $_POST['shipment_id'] ?? 0);

        // 🛑 CANCELLATION
        if ($action === 'update_status' && $_POST['status'] === 'Cancelled') {
            if ($id === 0) throw new Exception("Missing ID");
            $reason = mysqli_real_escape_string($conn, $_POST['reason']);
            $sql = "UPDATE shipments SET status = 'Cancelled', cancel_reason = '$reason', updated_at = NOW() WHERE id = '$id'";
            if (mysqli_query($conn, $sql)) echo json_encode(['success' => true, 'message' => 'Shipment cancelled.']);
            else throw new Exception("DB Error: " . mysqli_error($conn));
            exit;
        }

        // ✅ DELIVERED
        if ($action === 'update_status' && $_POST['status'] === 'Delivered') {
            if ($id === 0) throw new Exception("Missing ID");
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
            if (mysqli_query($conn, $sql)) echo json_encode(['success' => true, 'message' => 'Marked as Received!']);
            else throw new Exception("DB Error: " . mysqli_error($conn));
            exit;
        }

        // ⭐ RATING
        if ($action === 'submit_rating') {
            if ($id === 0) throw new Exception("Missing ID");
            $rating = intval($_POST['rating']);
            $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
            $sql = "UPDATE shipments SET rating = '$rating', feedback_text = '$feedback' WHERE id = '$id'";
            if (mysqli_query($conn, $sql)) echo json_encode(['success' => true]);
            else throw new Exception("DB Error: " . mysqli_error($conn));
            exit;
        }
        
        echo json_encode(['success' => false, 'message' => 'Invalid Action']);

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid Request Method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>