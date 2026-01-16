<?php
// bookshipment_api.php - FIXED VERSION (With Origin Island)
include('../connection.php'); // Ang ../ ay "Go up one level"
include('../session.php');
header('Content-Type: application/json');

try {
    // 1. Check User Session
    if (!isset($_SESSION['email'])) {
        throw new Exception("Unauthorized access. Please login.");
    }

    $email = $_SESSION['email'];
    
    // Get User ID
    $uQuery = mysqli_query($conn, "SELECT id FROM accounts WHERE email='$email'");
    $uData = mysqli_fetch_assoc($uQuery);
    $userId = $uData['id'];

    // 2. Receive JSON Data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception("No data received.");
    }

    // 3. Sanitize Inputs
    $contract_number = mysqli_real_escape_string($conn, $input['contract_number'] ?? '');
    
    $sender_name = mysqli_real_escape_string($conn, $input['sender_name']);
    $sender_contact = mysqli_real_escape_string($conn, $input['sender_contact']);
    
    $receiver_name = mysqli_real_escape_string($conn, $input['receiver_name']);
    $receiver_contact = mysqli_real_escape_string($conn, $input['receiver_contact']);
    
    $origin_address = mysqli_real_escape_string($conn, $input['origin_address']);
    $destination_address = mysqli_real_escape_string($conn, $input['destination_address']);
    
    // --- ISLAND DATA (Mahalaga ito para sa SLA) ---
    // Siguraduhin na nakuha natin ang value mula sa form
    $origin_island = mysqli_real_escape_string($conn, $input['origin_island'] ?? 'Luzon');
    $destination_island = mysqli_real_escape_string($conn, $input['destination_island'] ?? 'Luzon');
    // ------------------------

    $specific_address = mysqli_real_escape_string($conn, $input['address']);
    $weight = floatval($input['weight']);
    $package_type = mysqli_real_escape_string($conn, $input['package_type']);
    $package_desc = mysqli_real_escape_string($conn, $input['package']);
    
    $payment_method = mysqli_real_escape_string($conn, $input['payment_method']);
    $bank_name = mysqli_real_escape_string($conn, $input['bank_name']);
    
    $distance_km = floatval($input['distance_km']);
    $price = floatval($input['price_php']);
    
    // AI & SLA Data
    $ai_estimated = mysqli_real_escape_string($conn, $input['ai_estimated_time'] ?? 'Calculating...');
    $target_date = mysqli_real_escape_string($conn, $input['target_date'] ?? NULL);
    
    // Handle Empty Target Date
    $targetDateSql = empty($target_date) ? "NULL" : "'$target_date'";

    // 4. INSERT QUERY (FIXED)
    // Idinagdag natin ang 'origin_island' sa parehong listahan
    $sql = "INSERT INTO shipments (
        user_id, contract_number, 
        sender_name, sender_contact, 
        receiver_name, receiver_contact, 
        origin_address, 
        origin_island, /* <--- ✅ ADDED COLUMN HERE */
        destination_address, 
        destination_island, 
        specific_address, 
        weight, package_type, package_description, 
        distance_km, price, 
        payment_method, bank_name, 
        status, sla_status, 
        ai_estimated_time, target_delivery_date, 
        created_at
    ) VALUES (
        '$userId', '$contract_number',
        '$sender_name', '$sender_contact',
        '$receiver_name', '$receiver_contact',
        '$origin_address', 
        '$origin_island', /* <--- ✅ ADDED VALUE HERE */
        '$destination_address',
        '$destination_island', 
        '$specific_address',
        '$weight', '$package_type', '$package_desc',
        '$distance_km', '$price',
        '$payment_method', '$bank_name',
        'Pending', 'Pending',
        '$ai_estimated', $targetDateSql,
        NOW()
    )";

    if (mysqli_query($conn, $sql)) {
        $shipmentId = mysqli_insert_id($conn);
        echo json_encode(['success' => true, 'message' => 'Booking Successful!', 'shipment_id' => $shipmentId]);
    } else {
        throw new Exception("Database Error: " . mysqli_error($conn));
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>