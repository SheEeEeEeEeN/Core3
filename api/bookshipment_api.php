<?php
// VERSION: FINAL INTEGRATED WITH MAP COORDINATES (Shipment + PDF Invoice + Payment Record + Core 1 Sync)

include('../connection.php'); 
include('../session.php');
header('Content-Type: application/json');

// 1. LOAD PDF GENERATOR
require_once('generate_invoice_fpdf.php');

// CONFIG FOR CORE 1
$CORE1_DB_NAME = "coretransac"; 
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";

try {
    // 2. CHECK SESSION
    if (!isset($_SESSION['email'])) { throw new Exception("Unauthorized access."); }
    $email = $_SESSION['email'];
    
    $uQuery = mysqli_query($conn, "SELECT id FROM accounts WHERE email='$email'");
    $uData = mysqli_fetch_assoc($uQuery);
    if (!$uData) { throw new Exception("User account not found."); }
    $userId = $uData['id'];

    // 3. RECEIVE DATA
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) { throw new Exception("No data received."); }

    // Sanitize Variables (Existing)
    $contract    = mysqli_real_escape_string($conn, $input['contract_number'] ?? '');
    $sender      = mysqli_real_escape_string($conn, $input['sender_name']);
    $s_contact   = mysqli_real_escape_string($conn, $input['sender_contact']);
    $receiver    = mysqli_real_escape_string($conn, $input['receiver_name']);
    $r_contact   = mysqli_real_escape_string($conn, $input['receiver_contact']);
    $origin      = mysqli_real_escape_string($conn, $input['origin_address']);
    $dest        = mysqli_real_escape_string($conn, $input['destination_address']);
    $origin_island = mysqli_real_escape_string($conn, $input['origin_island'] ?? 'Luzon');
    $dest_island   = mysqli_real_escape_string($conn, $input['destination_island'] ?? 'Luzon');
    $address     = mysqli_real_escape_string($conn, $input['address']);
    $weight      = floatval($input['weight']);
    $type        = mysqli_real_escape_string($conn, $input['package_type']);
    $desc        = mysqli_real_escape_string($conn, $input['package']);
    $method      = mysqli_real_escape_string($conn, $input['payment_method']);
    $bank        = mysqli_real_escape_string($conn, $input['bank_name']);
    $km          = floatval($input['distance_km']);
    $price       = floatval($input['price_php']);
    $ai_time     = mysqli_real_escape_string($conn, $input['ai_estimated_time'] ?? 'Calculating...');
    $target_date = !empty($input['target_date']) ? $input['target_date'] : date('Y-m-d', strtotime('+3 days'));
    $targetDateSql = "'$target_date'";

    // --- NEW: Sanitize Coordinates ---
    $origin_lat = isset($input['origin_lat']) ? mysqli_real_escape_string($conn, $input['origin_lat']) : null;
    $origin_lng = isset($input['origin_lng']) ? mysqli_real_escape_string($conn, $input['origin_lng']) : null;
    $dest_lat   = isset($input['dest_lat']) ? mysqli_real_escape_string($conn, $input['dest_lat']) : null;
    $dest_lng   = isset($input['dest_lng']) ? mysqli_real_escape_string($conn, $input['dest_lng']) : null;

    // ---------------------------------------------------------
    // STEP 4: SAVE SHIPMENT (CORE 3) WITH COORDINATES
    // ---------------------------------------------------------
    $sql_core3 = "INSERT INTO shipments (
        user_id, contract_number, sender_name, sender_contact, receiver_name, receiver_contact, 
        origin_address, origin_island, destination_address, destination_island, specific_address, 
        weight, package_type, package_description, distance_km, price, payment_method, bank_name, 
        status, sla_status, ai_estimated_time, target_delivery_date, 
        origin_lat, origin_lng, dest_lat, dest_lng,  -- <--- NEW COLUMNS
        created_at
    ) VALUES (
        '$userId', '$contract', '$sender', '$s_contact', '$receiver', '$r_contact', 
        '$origin', '$origin_island', '$dest', '$dest_island', '$address', 
        '$weight', '$type', '$desc', '$km', '$price', '$method', '$bank', 
        'Pending', 'Pending', '$ai_time', $targetDateSql, 
        '$origin_lat', '$origin_lng', '$dest_lat', '$dest_lng', -- <--- NEW VALUES
        NOW()
    )";

    if (!mysqli_query($conn, $sql_core3)) {
        throw new Exception("Core 3 Save Failed: " . mysqli_error($conn));
    }
    
    $shipmentId = mysqli_insert_id($conn);
    $trackingCode = "PO-C3-" . str_pad($shipmentId, 5, "0", STR_PAD_LEFT);

    // ---------------------------------------------------------
    // STEP 5: GENERATE PDF INVOICE (Automatic)
    // ---------------------------------------------------------
    $invoiceData = [
        'invoice_number' => $trackingCode,
        'sender_name' => $sender,
        'sender_contact' => $s_contact,
        'origin_address' => $origin,
        'receiver_name' => $receiver,
        'receiver_contact' => $r_contact,
        'destination_address' => $dest,
        'package_type' => $type,
        'weight' => $weight,
        'distance_km' => $km,
        'price' => $price,
        'method' => $method,
        'status' => ($method == 'online') ? 'PAID' : 'PENDING'
    ];

    $pdfFilename = generateInvoicePDF($invoiceData, $shipmentId);

    // ---------------------------------------------------------
    // STEP 6: INSERT PAYMENT RECORD
    // ---------------------------------------------------------
    $payment_status = ($method == 'online') ? 'Paid' : 'Pending'; 
    
    $sql_payment = "INSERT INTO payments (
        user_id, shipment_id, invoice_number, amount, payment_date, status, method, reference_no, invoice_image
    ) VALUES (
        '$userId', '$shipmentId', '$trackingCode', '$price', NOW(), '$payment_status', '$method', '$bank', '$pdfFilename'
    )";

    if (!mysqli_query($conn, $sql_payment)) {
        // error_log("Payment Insert Error: " . mysqli_error($conn));
    }

    // ---------------------------------------------------------
    // STEP 7: SYNC TO CORE 1 (Updated with Real Coordinates)
    // ---------------------------------------------------------
    $conn_core1 = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $CORE1_DB_NAME);
    $msg = "Booking Successful! Invoice Generated.";

    if (!$conn_core1->connect_error) {
        $c1_uid = 1; $c1_mode = 'LAND'; $c1_status = 'PENDING';
        
        // Use real coordinates if available, otherwise default to 0.0
        $c1_origin_lat = $origin_lat ? floatval($origin_lat) : 0.0;
        $c1_origin_lng = $origin_lng ? floatval($origin_lng) : 0.0;
        $c1_dest_lat   = $dest_lat ? floatval($dest_lat) : 0.0;
        $c1_dest_lng   = $dest_lng ? floatval($dest_lng) : 0.0;
        
        $stmt = $conn_core1->prepare("INSERT INTO purchase_orders (
            user_id, contract_number, sender_name, sender_contact, receiver_name, receiver_contact, 
            origin_address, destination_address, origin_lat, origin_lng, destination_lat, destination_lng, 
            transport_mode, weight, package_type, package_description, payment_method, bank_name, 
            distance_km, price, sla_agreement, ai_estimated_time, target_delivery_date, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        if ($stmt) {
            $stmt->bind_param("isssssssddddssdssdsdssss", 
                $c1_uid, $trackingCode, $sender, $s_contact, $receiver, $r_contact, 
                $origin, $dest, 
                $c1_origin_lat, $c1_origin_lng, $c1_dest_lat, $c1_dest_lng, // Use real coords
                $c1_mode, $weight, $type, $desc, $method, $bank, 
                $km, $price, $contract, $ai_time, $target_date, $c1_status
            );
            $stmt->execute();
            $stmt->close();
        }
        $conn_core1->close();
    }

    // ---------------------------------------------------------
    // STEP 8: RETURN SUCCESS
    // ---------------------------------------------------------
    echo json_encode([
        'success' => true, 
        'message' => $msg, 
        'shipment_id' => $shipmentId,
        'invoice_file' => $pdfFilename 
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>