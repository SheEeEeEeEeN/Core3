<?php
// update_shipment_api.php

// 1. Pigilan ang PHP na mag-print ng text errors sa screen (nakakasira ito ng JSON)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// 2. Linisin ang output buffer
ob_start();

include("connection.php");

// 3. Siguraduhin na JSON ang header
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$response = ['success' => false, 'message' => 'Unknown error'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = "Invalid Request Method";
    echo_json($response);
}

$action = $_POST['action'] ?? '';

// === ACTION: UPDATE STATUS ===
if ($action === 'update_status') {
    $id = intval($_POST['id']);
    $status = $_POST['status'];
    $reason = $_POST['reason'] ?? '';

    // A. KUNG DELIVERED (May Picture Upload)
    if ($status === 'Delivered') {
        
        // Check kung may file upload
        if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] == 0) {
            
            $targetDir = "uploads/proofs/";
            
            // Gumawa ng folder kung wala pa
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $extension = pathinfo($_FILES["proof_image"]["name"], PATHINFO_EXTENSION);
            $fileName = "proof_" . $id . "_" . time() . "." . $extension;
            $targetFilePath = $targetDir . $fileName;

            if (move_uploaded_file($_FILES["proof_image"]["tmp_name"], $targetFilePath)) {
                
                $stmt = $conn->prepare("UPDATE shipments SET status = ?, proof_image = ? WHERE id = ?");
                $stmt->bind_param("ssi", $status, $targetFilePath, $id);
                
                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Status updated and proof uploaded'];
                } else {
                    $response['message'] = 'Database Error: ' . $stmt->error;
                }
                $stmt->close();

            } else {
                $response['message'] = 'Failed to move uploaded file. Check folder permissions.';
            }
        } else {
            $response['message'] = 'No valid image file received.';
        }
    }

    // B. KUNG CANCELLED
    elseif ($status === 'Cancelled') {
        $stmt = $conn->prepare("UPDATE shipments SET status = ?, feedback_text = ? WHERE id = ?");
        $stmt->bind_param("ssi", $status, $reason, $id);
        if ($stmt->execute()) {
            $response = ['success' => true];
        } else {
            $response['message'] = $stmt->error;
        }
    }

    // C. IBA PANG STATUS (Receive without pic, etc.)
    else {
        $stmt = $conn->prepare("UPDATE shipments SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        if ($stmt->execute()) {
            $response = ['success' => true];
        } else {
            $response['message'] = $stmt->error;
        }
    }
}

// === ACTION: SUBMIT RATING ===
elseif ($action === 'submit_rating') {
    $id = intval($_POST['shipment_id']);
    $rating = intval($_POST['rating']);
    $feedback = $_POST['feedback'];
    
    $stmt = $conn->prepare("UPDATE shipments SET rating = ?, feedback_text = ? WHERE id = ?");
    $stmt->bind_param("isi", $rating, $feedback, $id);
    
    if ($stmt->execute()) {
        $response = ['success' => true];
    } else {
        $response['message'] = $stmt->error;
    }
}

else {
    $response['message'] = "No valid action provided";
}

// Function para mag-send ng JSON at patayin ang script
function echo_json($data) {
    ob_end_clean(); // Burahin ang anumang "Connected" text o errors
    echo json_encode($data);
    exit;
}

echo_json($response);
?>