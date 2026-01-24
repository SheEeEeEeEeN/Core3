<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // allow other domains (like Core 1) to pull data
header("Access-Control-Allow-Methods: GET");

// include Core 3 DB connection
include '../connection.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

// Fetch shipment bookings (customer-related data)
$sql = "
    SELECT 
        id, 
        user_id, 
        sender_name, 
        receiver_name, 
        address, 
        weight, 
        package_description, 
        status, 
        created_at 
    FROM shipments
    ORDER BY id DESC
";
$result = $conn->query($sql);

$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Return JSON
echo json_encode($data);
exit;
