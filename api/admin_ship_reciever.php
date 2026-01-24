<?php
// FILE: last/api/admin_ship_reciever.php (Core 3)

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// TANDAAN: Gamitin ang tamang path sa connection file mo (yung inayos mo kanina)
include_once "../connection.php"; 

$action = $_POST['action'] ?? '';
$secret_key = $_POST['secret_key'] ?? '';

// Security Check
if ($secret_key !== 'SLATE_SECRET_123') {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Unauthorized Access"]);
    exit();
}

// ================= ACTION: CREATE SHIPMENT =================
if ($action == 'create_shipment') {
    $code   = $_POST['shipment_code'];
    $origin = $_POST['origin'];
    $dest   = $_POST['destination'];

    // Check kung existing na para iwas duplicate error
    $check = $conn->query("SELECT shipment_code FROM shipments WHERE shipment_code = '$code'");
    if ($check->num_rows > 0) {
        echo json_encode(["status" => "warning", "message" => "Shipment already exists"]);
        exit();
    }

    // Insert sa Core 3 Database (Status default: BOOKED)
    // Siguraduhin na ang columns na ito ay existing sa table mo sa Core 3
    $stmt = $conn->prepare("INSERT INTO shipments (shipment_code, origin_address, destination_address, status, created_at) VALUES (?, ?, ?, 'BOOKED', NOW())");
    $stmt->bind_param("sss", $code, $origin, $dest);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Shipment created in Core 3"]);
    } else {
        echo json_encode(["status" => "error", "message" => "DB Error: " . $stmt->error]);
    }
    $stmt->close();
} 

// ================= ACTION: UPDATE STATUS =================
elseif ($action == 'auto_sync_status') {
    $shipment_code = $_POST['shipment_code'];
    $new_status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE shipments SET status = ?, updated_at = NOW() WHERE shipment_code = ?");
    $stmt->bind_param("ss", $new_status, $shipment_code);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["status" => "success", "message" => "Status updated in Core 3"]);
        } else {
            echo json_encode(["status" => "warning", "message" => "No changes made or Shipment Code not found"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $stmt->error]);
    }
    $stmt->close();
} 

else {
    echo json_encode(["status" => "error", "message" => "Invalid Action"]);
}

$conn->close();
?>