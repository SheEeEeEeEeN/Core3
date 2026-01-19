<?php
// api/admin_ship_reciever.php
// VERSION: STABLE CONNECTION + FINANCIALS + EMAIL RESTORED ✅

ini_set('display_errors', 0); 
error_reporting(E_ALL);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); 

try {
    // 1. CONNECTION SETUP (Auto-Detect)
    $current_dir = __DIR__;
    $path_attempt_1 = dirname($current_dir) . "/connection.php"; 
    $path_attempt_2 = $_SERVER['DOCUMENT_ROOT'] . "/last/connection.php"; 

    if (file_exists($path_attempt_1)) {
        require_once($path_attempt_1);
    } elseif (file_exists($path_attempt_2)) {
        require_once($path_attempt_2);
    } else {
        throw new Exception("CRITICAL: connection.php NOT FOUND.");
    }

    // Auto-detect DB variable ($conn, $con, or $mysqli)
    $db = null;
    if (isset($conn) && $conn) { $db = $conn; }
    elseif (isset($con) && $con) { $db = $con; }
    elseif (isset($mysqli) && $mysqli) { $db = $mysqli; }

    if (!$db) {
        throw new Exception("Database connected, but variable is missing.");
    }

    // 2. INCLUDE MAILER FUNCTION (Importante to para sa email!)
    $mailer_found = false;
    $mailer_paths = [
        dirname($current_dir) . "/mailer_function.php",
        $_SERVER['DOCUMENT_ROOT'] . "/last/mailer_function.php"
    ];
    
    foreach($mailer_paths as $mp) {
        if(file_exists($mp)) {
            include($mp);
            $mailer_found = true;
            break;
        }
    }

    // ==========================================================
    // LOGIC START
    // ==========================================================
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid Request Method.");
    }

    $action = $_POST['action'] ?? 'update'; 
    $secret = $_POST['secret_key'] ?? '';

    if ($secret !== "SLATE_SECRET_123") {
        throw new Exception("Unauthorized Access.");
    }

    // --- FETCH FINANCIALS (Finance Dept) ---
    if ($action === 'fetch_financials') {
        $colCheck = mysqli_query($db, "SHOW COLUMNS FROM shipments LIKE 'updated_at'");
        $hasUpdatedAt = (mysqli_num_rows($colCheck) > 0);
        $orderBy = $hasUpdatedAt ? "updated_at" : "id"; 

        $colSelect = "id, id AS tracking_number, sender_name, payment_method, price, status, payment_status, created_at";
        if ($hasUpdatedAt) { $colSelect .= ", updated_at"; }

        $query = "SELECT $colSelect FROM shipments WHERE payment_status = 'Paid' ORDER BY $orderBy DESC"; 
        $result = mysqli_query($db, $query);
        
        if (!$result) throw new Exception("SQL Error: " . mysqli_error($db));

        $transactions = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $row['updated_at'] = isset($row['updated_at']) ? $row['updated_at'] : $row['created_at'];
            // Tracking format for display
            $row['tracking_number'] = "SHIP-" . str_pad($row['id'], 5, "0", STR_PAD_LEFT); 
            $transactions[] = $row;
        }
        
        echo json_encode(["status" => "success", "data" => $transactions]);
        exit;
    }

    // --- FETCH ALL (Logistics List) ---
    if ($action === 'fetch_all') {
        $query = "SELECT *, id AS tracking_number FROM shipments ORDER BY id DESC";
        $result = mysqli_query($db, $query);
        if (!$result) throw new Exception("SQL Error: " . mysqli_error($db));
        
        $data = [];
        while ($r = mysqli_fetch_assoc($result)) {
            $r['tracking_number'] = "SHIP-" . str_pad($r['id'], 5, "0", STR_PAD_LEFT);
            $data[] = $r;
        }
        echo json_encode(["status" => "success", "data" => $data]);
        exit;
    }

    // --- UPDATE STATUS + EMAIL NOTIFICATION ---
    if ($action === 'update') {
        $id = $_POST['tracking_id'];
        $status = $_POST['status'];
        
        // Auto-Paid Logic
        $paySql = ($status === 'Delivered') ? ", payment_status = 'Paid'" : "";
        
        $sql = "UPDATE shipments SET status='$status' $paySql WHERE id='$id'";
        
        if (mysqli_query($db, $sql)) {
            
            $email_status = "Skipped (Mailer not found)";
            
            // ✨ EMAIL LOGIC RESTORED HERE ✨
            if ($mailer_found && function_exists('sendStatusEmail')) {
                
                // 1. Hanapin ang Sender Name sa shipments table
                $shipQuery = "SELECT sender_name FROM shipments WHERE id='$id'";
                $shipResult = mysqli_query($db, $shipQuery);
                
                if ($shipResult && mysqli_num_rows($shipResult) > 0) {
                    $shipData = mysqli_fetch_assoc($shipResult);
                    $senderName = $db->real_escape_string($shipData['sender_name']);

                    // 2. Hanapin ang Email sa accounts table gamit ang Sender Name
                    // (Matches either username or email field)
                    $userQuery = "SELECT email, username FROM accounts WHERE username='$senderName' OR email='$senderName'";
                    $userResult = mysqli_query($db, $userQuery);

                    if ($userResult && mysqli_num_rows($userResult) > 0) {
                        $userData = mysqli_fetch_assoc($userResult);
                        $userEmail = $userData['email'];
                        $userName = $userData['username'];
                        
                        // Formatting Tracking ID for the email body
                        $trackingDisplay = "SHIP-" . str_pad($id, 5, "0", STR_PAD_LEFT);

                        // 3. Send the Email
                        if (sendStatusEmail($userEmail, $userName, $trackingDisplay, $status)) {
                            $email_status = "Email Sent to $userEmail";
                        } else {
                            $email_status = "Email Failed to Send";
                        }
                    } else {
                        $email_status = "User Account Not Found";
                    }
                }
            }

            echo json_encode(["status" => "success", "message" => "Updated Status. $email_status"]);
        } else {
            throw new Exception("Update SQL Error: " . mysqli_error($db));
        }
        exit;
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>