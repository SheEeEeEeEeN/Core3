<?php
// FILE: api/bookshipment_api.php

// 1. SET HEADERS (Open Access)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// 2. ERROR HANDLING
error_reporting(E_ALL);
ini_set('display_errors', 0);

// 3. DATABASE CONNECTION
if (file_exists('../connection.php'))
    include('../connection.php');

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

// ==========================================================================
// SCENARIO A: GET DATA (FETCH SHIPMENTS)
// ==========================================================================
if ($method === 'GET') {
    
    // Case 1: Fetch SINGLE shipment by Tracking Code
    if (isset($_GET['tracking_code'])) {
        $tracking = mysqli_real_escape_string($conn, $_GET['tracking_code']);
        
        // CHANGED: Select * to get ALL columns
        $sql = "SELECT * FROM shipments WHERE contract_number = '$tracking' OR id = '$tracking' LIMIT 1";
        
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $data = $result->fetch_assoc();
            echo json_encode([
                "status" => "success",
                "data" => $data
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Shipment not found"]);
        }
    } 
    // Case 2: Fetch ALL shipments (List)
    else {
        // CHANGED: Select * to get ALL columns
        $sql = "SELECT * FROM shipments ORDER BY created_at DESC LIMIT 20";
        
        $result = $conn->query($sql);
        $rows = [];
        
        if ($result) {
            while($r = $result->fetch_assoc()) {
                $rows[] = $r;
            }
        }
        
        echo json_encode(["status" => "success", "count" => count($rows), "data" => $rows]);
    }
}

// ==========================================================================
// SCENARIO B: CREATE BOOKING (POST)
// ==========================================================================
elseif ($method === 'POST') {
    
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
        exit();
    }

    try {
        $userId      = 1; 
        
        $contract    = mysqli_real_escape_string($conn, $input['contract_number'] ?? 'Ext-' . time());
        $sender      = mysqli_real_escape_string($conn, $input['sender_name'] ?? 'Core 1 Sender');
        $s_contact   = mysqli_real_escape_string($conn, $input['sender_contact'] ?? '');
        $receiver    = mysqli_real_escape_string($conn, $input['receiver_name'] ?? '');
        $r_contact   = mysqli_real_escape_string($conn, $input['receiver_contact'] ?? '');
        $origin      = mysqli_real_escape_string($conn, $input['origin_address'] ?? '');
        $dest        = mysqli_real_escape_string($conn, $input['destination_address'] ?? '');
        $address     = mysqli_real_escape_string($conn, $input['specific_address'] ?? $dest);
        $weight      = floatval($input['weight'] ?? 0);
        $type        = mysqli_real_escape_string($conn, $input['package_type'] ?? 'box');
        $desc        = mysqli_real_escape_string($conn, $input['description'] ?? 'Imported Shipment');
        $km          = floatval($input['distance_km'] ?? 0);
        $price       = floatval($input['price'] ?? 0);
        $method      = mysqli_real_escape_string($conn, $input['payment_method'] ?? 'System Transfer');
        $status      = 'Pending';

        $sql = "INSERT INTO shipments (
            user_id, contract_number, sender_name, sender_contact, receiver_name, receiver_contact, 
            origin_address, destination_address, specific_address, 
            weight, package_type, package_description, distance_km, price, payment_method, 
            status, created_at
        ) VALUES (
            '$userId', '$contract', '$sender', '$s_contact', '$receiver', '$r_contact', 
            '$origin', '$dest', '$address', 
            '$weight', '$type', '$desc', '$km', '$price', '$method', 
            '$status', NOW()
        )";

        if ($conn->query($sql)) {
            $shipmentId = $conn->insert_id;
            $trackingCode = "PO-C3-" . str_pad($shipmentId, 5, "0", STR_PAD_LEFT);
            $conn->query("UPDATE shipments SET contract_number = '$trackingCode' WHERE id = '$shipmentId'");

            echo json_encode([
                "status" => "success", 
                "message" => "Booking Received", 
                "tracking_code" => $trackingCode,
                "shipment_id" => $shipmentId
            ]);
        } else {
            throw new Exception($conn->error);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
}

$conn->close();
?>