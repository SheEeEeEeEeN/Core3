<?php
// api_receiver.php - (Main System)
// NGAYON: Kaya na niyang mag-receive ng update, at kaya na rin niyang magbigay ng listahan.

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); // Payagan ang ibang folder na kumuha ng data

if (!file_exists('../connection.php')) {
    echo json_encode(["status" => "error", "message" => "Missing connection.php"]);
    exit;
}

include("../connection.php"); 
include("../mailer_function.php"); // Para sa email function

// TANGGAPIN ANG REQUEST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Check natin kung anong ACTION ang gusto: 'update' ba o 'fetch_all'?
    $action = $_POST['action'] ?? 'update'; // Default to update kung walang sinabi
    $secret = $_POST['secret_key'] ?? '';

    if($secret !== "SLATE_SECRET_123") {
        echo json_encode(["status" => "error", "message" => "Unauthorized"]);
        exit;
    }

    // --- CASE 1: GUSTO MAKITA ANG LISTAHAN (FETCH) ---
    if ($action === 'fetch_all') {
        $query = "SELECT * FROM shipments ORDER BY id DESC"; // Kunin lahat sa Main DB
        $result = mysqli_query($conn, $query);
        
        $shipments = [];
        while($row = mysqli_fetch_assoc($result)) {
            $shipments[] = $row;
        }
        
        echo json_encode([
            "status" => "success", 
            "data" => $shipments
        ]);
        exit;
    }

    // --- CASE 2: GUSTO MAG-UPDATE (UPDATE) ---
    if ($action === 'update') {
        $tracking_id = $_POST['tracking_id'];
        $status      = $_POST['status'];

        $update = mysqli_query($conn, "UPDATE shipments SET status='$status' WHERE id='$tracking_id'");

        if ($update) {
            // Email Logic (Same as before)
            $email_msg = "No Email Sent";
            $shipQ = mysqli_query($conn, "SELECT sender_name FROM shipments WHERE id='$tracking_id'");
            if(mysqli_num_rows($shipQ) > 0){
                $row = mysqli_fetch_assoc($shipQ);
                $senderName = $row['sender_name'];
                $userQ = mysqli_query($conn, "SELECT email, username FROM accounts WHERE username = '$senderName' OR email = '$senderName'");
                if(mysqli_num_rows($userQ) > 0) {
                    $user = mysqli_fetch_assoc($userQ);
                    $trackDisplay = "SHIP-" . str_pad($tracking_id, 8, "0", STR_PAD_LEFT);
                    if(sendStatusEmail($user['email'], $user['username'], $trackDisplay, $status)){
                        $email_msg = "Email Sent";
                    }
                }
            }
            echo json_encode(["status" => "success", "message" => "Main Updated ($email_msg)"]);
        } else {
            echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
        }
    }
}
?>