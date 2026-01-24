<?php
include(__DIR__ . "/../connection.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit();
}

// Read JSON input
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(["error" => "Invalid JSON input"]);
    exit();
}

// Validate required fields
// ADDED: origin_address and destination_address to validation
$required = ["sender_name", "receiver_name", "origin_address", "destination_address", "address", "weight", "package", "payment_method"];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(["error" => "Missing field: $field"]);
        exit();
    }
}

$user_id = $_SESSION['user_id'] ?? ($data['user_id'] ?? 0);
$sender_name = $conn->real_escape_string(trim($data['sender_name']));
$receiver_name = $conn->real_escape_string(trim($data['receiver_name']));

// --- NEW FIELDS: Capture Origin & Destination ---
$origin_address = $conn->real_escape_string(trim($data['origin_address']));
$destination_address = $conn->real_escape_string(trim($data['destination_address']));
// ------------------------------------------------

$address = $conn->real_escape_string(trim($data['address']));
$weight = floatval($data['weight']);
$package_description = $conn->real_escape_string(trim($data['package']));
$payment_method = $conn->real_escape_string(trim($data['payment_method']));

// --- Distance & Price Calculation ---
$distance_km = floatval($data['distance_km'] ?? 0);
$price_per_km = 24.0;
$base_fee = 0;

// You can rely on backend calc OR trust frontend 'price_php'. 
// Using backend calc is safer, but ensure logic matches frontend.
$price = ($distance_km * $price_per_km) + $base_fee; 

// --- Insert Shipment ---
// UPDATED SQL: Added origin_address and destination_address columns and values
$sql = "INSERT INTO shipments 
    (user_id, sender_name, receiver_name, origin_address, destination_address, address, weight, package_description, distance_km, price, payment_method, status, created_at)
    VALUES
    ('$user_id', '$sender_name', '$receiver_name', '$origin_address', '$destination_address', '$address', '$weight', '$package_description',
     '$distance_km', '$price', '$payment_method', 'Pending', NOW())";

if ($conn->query($sql) === TRUE) {
    $shipment_id = $conn->insert_id;

    // --- Insert into Payments table ---
    $payment_sql = "INSERT INTO payments (user_id, amount, method, status, reference_no, payment_date)
                    VALUES ('$user_id', '$price', '$payment_method', 'Pending', NULL, NOW())";

    if ($conn->query($payment_sql) === TRUE) {
        echo json_encode([
            "success" => true,
            "message" => "Shipment booked and payment record created successfully",
            "shipment_id" => $shipment_id,
            "price" => $price,
            "payment_method" => $payment_method
        ]);
    } else {
        echo json_encode([
            "success" => true,
            "warning" => "Shipment saved but failed to create payment record",
            "error" => $conn->error
        ]);
    }
} else {
    echo json_encode(["error" => $conn->error, "sql" => $sql]);
}
?>