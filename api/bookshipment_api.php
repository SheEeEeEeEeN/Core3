<?php
// core3/api/bookshipment_api.php
// VERSION: FINAL INTEGRATION (Core 3 -> Core 1 Purchase Order) 💉

include('../connection.php'); // Connection sa Core 3
include('../session.php');
header('Content-Type: application/json');

// =========================================================
// ⚠️ CONFIGURATION: ALAMIN ANG DB NAME NG CORE 1 MO!
// =========================================================
$CORE1_DB_NAME = "coretransac"; // <--- PALITAN MO ITO NG EXACT DB NAME NG CORE 1
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";

try {
    // 1. Check User Session
    if (!isset($_SESSION['email'])) { throw new Exception("Unauthorized access."); }
    $email = $_SESSION['email'];
    
    // Get User ID (Core 3)
    $uQuery = mysqli_query($conn, "SELECT id FROM accounts WHERE email='$email'");
    $uData = mysqli_fetch_assoc($uQuery);
    $userId = $uData['id'];

    // 2. Receive Data
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) { throw new Exception("No data received."); }

    // 3. Sanitize Variables
    $contract = mysqli_real_escape_string($conn, $input['contract_number'] ?? '');
    $sender = mysqli_real_escape_string($conn, $input['sender_name']);
    $s_contact = mysqli_real_escape_string($conn, $input['sender_contact']);
    $receiver = mysqli_real_escape_string($conn, $input['receiver_name']);
    $r_contact = mysqli_real_escape_string($conn, $input['receiver_contact']);
    
    $origin = mysqli_real_escape_string($conn, $input['origin_address']);
    $dest = mysqli_real_escape_string($conn, $input['destination_address']);
    
    $origin_island = mysqli_real_escape_string($conn, $input['origin_island'] ?? 'Luzon');
    $dest_island = mysqli_real_escape_string($conn, $input['destination_island'] ?? 'Luzon');
    
    $address = mysqli_real_escape_string($conn, $input['address']);
    $weight = floatval($input['weight']);
    $type = mysqli_real_escape_string($conn, $input['package_type']);
    $desc = mysqli_real_escape_string($conn, $input['package']);
    $method = mysqli_real_escape_string($conn, $input['payment_method']);
    $bank = mysqli_real_escape_string($conn, $input['bank_name']);
    $km = floatval($input['distance_km']);
    $price = floatval($input['price_php']);
    $ai_time = mysqli_real_escape_string($conn, $input['ai_estimated_time'] ?? 'Calculating...');
    $target_date = mysqli_real_escape_string($conn, $input['target_date'] ?? NULL);
    $targetDateSql = empty($target_date) ? "NULL" : "'$target_date'";

    // ---------------------------------------------------------
    // STEP 4: SAVE TO CORE 3 DATABASE (Yung sarili mong DB)
    // ---------------------------------------------------------
    $sql_core3 = "INSERT INTO shipments (
        user_id, contract_number, sender_name, sender_contact, receiver_name, receiver_contact, 
        origin_address, origin_island, destination_address, destination_island, specific_address, 
        weight, package_type, package_description, distance_km, price, payment_method, bank_name, 
        status, sla_status, ai_estimated_time, target_delivery_date, created_at
    ) VALUES (
        '$userId', '$contract', '$sender', '$s_contact', '$receiver', '$r_contact', 
        '$origin', '$origin_island', '$dest', '$dest_island', '$address', 
        '$weight', '$type', '$desc', '$km', '$price', '$method', '$bank', 
        'Pending', 'Pending', '$ai_time', $targetDateSql, NOW()
    )";

    if (!mysqli_query($conn, $sql_core3)) {
        throw new Exception("Core 3 Save Failed: " . mysqli_error($conn));
    }
    
    $shipmentId = mysqli_insert_id($conn);
    $trackingCode = "PO-C3-" . str_pad($shipmentId, 5, "0", STR_PAD_LEFT);

    // ---------------------------------------------------------
    // STEP 5: INJECT TO CORE 1 PURCHASE ORDERS 💉
    // ---------------------------------------------------------
    
    // Manual Connection to Core 1
    $conn_core1 = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $CORE1_DB_NAME);

    if ($conn_core1->connect_error) {
        $msg = "Booking Saved in Core 3! (Core 1 DB Unreachable)";
    } else {
        
        // Default values for Core 1 (since C3 doesn't provide them)
        $c1_uid = 1; // Default to Admin ID
        $c1_mode = 'LAND'; // Default Transport Mode
        $zero = 0.0; // Default Lat/Lng

        // Prepare SQL for Core 1 (purchase_orders Table)
        // Ensure column count matches the bind_param types exactly!
        $stmt = $conn_core1->prepare("
            INSERT INTO purchase_orders 
            (
                user_id, contract_number, 
                sender_name, sender_contact,
                receiver_name, receiver_contact,
                origin_address, destination_address, 
                origin_lat, origin_lng, 
                destination_lat, destination_lng,
                transport_mode,
                weight, package_type, package_description,
                payment_method, bank_name, 
                distance_km, price,
                sla_agreement, ai_estimated_time, target_delivery_date,
                status, created_at
            ) 
            VALUES 
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDING', NOW())
        ");

        if ($stmt) {
            // Target Date Logic for Bind (needs to be string)
            $bindTargetDate = empty($target_date) ? date('Y-m-d') : $target_date;

            // Bind Parameters (23 items to match values + 'PENDING' hardcoded)
            // Types: i (int), s (string), d (double)
            $stmt->bind_param(
                "isssssssddddssdssdsdsss", 
                $c1_uid,          // user_id (i)
                $trackingCode,    // contract_number (s)
                $sender,          // sender_name (s)
                $s_contact,       // sender_contact (s)
                $receiver,        // receiver_name (s)
                $r_contact,       // receiver_contact (s)
                $origin,          // origin_address (s)
                $dest,            // destination_address (s)
                $zero,            // origin_lat (d)
                $zero,            // origin_lng (d)
                $zero,            // dest_lat (d)
                $zero,            // dest_lng (d)
                $c1_mode,         // transport_mode (s)
                $weight,          // weight (d)
                $type,            // package_type (s)
                $desc,            // package_description (s)
                $method,          // payment_method (s)
                $bank,            // bank_name (s)
                $km,              // distance_km (d)
                $price,           // price (d)
                $contract,        // sla_agreement (s) - using contract/island logic
                $ai_time,         // ai_estimated_time (s)
                $bindTargetDate   // target_delivery_date (s)
            );

            if ($stmt->execute()) {
                $msg = "Booking Successful! Sent to Operations (Core 1 PO) for Approval.";
            } else {
                $msg = "Saved in Core 3, but PO Failed in Core 1: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $msg = "Saved in Core 3, but SQL Prepare Failed in Core 1: " . $conn_core1->error;
        }
        $conn_core1->close();
    }

    echo json_encode(['success' => true, 'message' => $msg, 'shipment_id' => $shipmentId]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>