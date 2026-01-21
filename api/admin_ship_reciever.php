<?php
// api/admin_ship_reciever.php
// VERSION: INTEGRATED AUTOMATION (Update + OR Gen + Email Receipt) 🚀

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

    $db = null;
    if (isset($conn) && $conn) { $db = $conn; }
    elseif (isset($con) && $con) { $db = $con; }
    elseif (isset($mysqli) && $mysqli) { $db = $mysqli; }

    if (!$db) { throw new Exception("Database connected, but variable is missing."); }

    // 2. INCLUDE MAILER FUNCTION
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
        // (Same logic as before...)
        $colCheck = mysqli_query($db, "SHOW COLUMNS FROM shipments LIKE 'updated_at'");
        $hasUpdatedAt = (mysqli_num_rows($colCheck) > 0);
        $orderBy = $hasUpdatedAt ? "updated_at" : "id"; 

        $colSelect = "id, id AS tracking_number, sender_name, payment_method, price, status, payment_status, created_at";
        if ($hasUpdatedAt) { $colSelect .= ", updated_at"; }

        $query = "SELECT $colSelect FROM shipments WHERE payment_status = 'Paid' ORDER BY $orderBy DESC"; 
        $result = mysqli_query($db, $query);
        
        $transactions = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $row['updated_at'] = isset($row['updated_at']) ? $row['updated_at'] : $row['created_at'];
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
        $data = [];
        while ($r = mysqli_fetch_assoc($result)) {
            $r['tracking_number'] = "SHIP-" . str_pad($r['id'], 5, "0", STR_PAD_LEFT);
            $data[] = $r;
        }
        echo json_encode(["status" => "success", "data" => $data]);
        exit;
    }

    // ==========================================================
    // 3. THE MAIN EVENT: UPDATE STATUS + OR LOGIC + EMAIL
    // ==========================================================
    if ($action === 'update') {
        $id = $_POST['tracking_id']; // Ito ay Shipment ID
        $status = $_POST['status'];
        
        // A. Basic Update
        $paySql = ($status === 'Delivered') ? ", payment_status = 'Paid'" : "";
        $sql = "UPDATE shipments SET status='$status' $paySql WHERE id='$id'";
        
        if (mysqli_query($db, $sql)) {
            
            $email_msg = "Status Updated. Email skipped.";

            // B. KUNIN ANG USER DETAILS (Para sa Email at Payments)
            // Mas matalino na 'to: Tinitignan muna kung may user_id, bago maghanap ng pangalan
            $shipQuery = "SELECT * FROM shipments WHERE id='$id'";
            $shipResult = mysqli_query($db, $shipQuery);
            $shipData = mysqli_fetch_assoc($shipResult);

            if ($shipData) {
                $user_id = $shipData['user_id'];
                $amount  = $shipData['price'];
                $method  = !empty($shipData['payment_method']) ? $shipData['payment_method'] : 'COD';
                $trackingDisplay = !empty($shipData['contract_number']) ? $shipData['contract_number'] : "SHIP-" . str_pad($id, 5, "0", STR_PAD_LEFT);
                
                // Hanapin ang Email ng User
                $userData = null;
                if (!empty($user_id) && $user_id != 0) {
                     // Priority: Hanapin via User ID
                     $uRes = mysqli_query($db, "SELECT email, username FROM accounts WHERE id='$user_id'");
                     $userData = mysqli_fetch_assoc($uRes);
                } 
                
                if (!$userData) {
                    // Fallback: Hanapin via Sender Name (Old logic mo)
                    $senderName = $db->real_escape_string($shipData['sender_name']);
                    $uRes = mysqli_query($db, "SELECT email, username FROM accounts WHERE username='$senderName' OR email='$senderName'");
                    $userData = mysqli_fetch_assoc($uRes);
                }

                // C. SPECIAL LOGIC: "DELIVERED" SCENARIO (Generate OR & Send Receipt)
                if ($status === 'Delivered' && $userData) {
                    
                    // 1. Generate OR Number
                    $checkPay = mysqli_query($db, "SELECT id, invoice_number FROM payments WHERE shipment_id='$id'");
                    $payRow = mysqli_fetch_assoc($checkPay);
                    
                    $or_number = isset($payRow['invoice_number']) ? $payRow['invoice_number'] : '';
                    
                    if (empty($or_number)) {
                        $or_number = "OR-" . date('Y') . "-" . str_pad($id, 5, "0", STR_PAD_LEFT);
                        
                        if ($payRow) {
                            // Update existing payment
                            $pid = $payRow['id'];
                            mysqli_query($db, "UPDATE payments SET status='Paid', invoice_number='$or_number', payment_date=NOW() WHERE id='$pid'");
                        } else {
                            // Insert new payment (Para sa COD)
                            $clean_uid = isset($userData['id']) ? $userData['id'] : $user_id; // Fallback
                            // Kung wala talagang ID, gamitin ang 0
                            if(empty($clean_uid)) $clean_uid = 0;

                            mysqli_query($db, "INSERT INTO payments (user_id, shipment_id, amount, method, status, invoice_number, payment_date) 
                                               VALUES ('$clean_uid', '$id', '$amount', '$method', 'Paid', '$or_number', NOW())");
                        }
                    }

                    // 2. SEND OFFICIAL RECEIPT EMAIL
                    if ($mailer_found && function_exists('sendReceiptEmail')) {
                        if (sendReceiptEmail($userData['email'], $userData['username'], $or_number, $amount, $trackingDisplay, $method)) {
                            $email_msg = "✅ Official Receipt Sent to " . $userData['email'];
                        } else {
                            $email_msg = "⚠️ OR Generated but Email Failed.";
                        }
                    }

                } 
                // D. NORMAL LOGIC: OTHER STATUS (In Transit, Cancelled)
                else {
                    if ($userData && $mailer_found && function_exists('sendStatusEmail')) {
                        if (sendStatusEmail($userData['email'], $userData['username'], $trackingDisplay, $status)) {
                            $email_msg = "ℹ️ Status Update Sent to " . $userData['email'];
                        }
                    }
                }
            }

            echo json_encode(["status" => "success", "message" => $email_msg]);

        } else {
            throw new Exception("Update SQL Error: " . mysqli_error($db));
        }
        exit;
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>