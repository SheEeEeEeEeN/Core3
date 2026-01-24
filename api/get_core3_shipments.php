<?php
// FILE: core3/api/get_core3_shipments.php
// SOURCE: Client Database (core_slate1)

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// 1. CONNECTION (Direct connection tayo para surebol)
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "core_slate1"; // <--- DATABASE NG CORE 3

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection Failed"]);
    exit();
}

try {
    // 2. QUERY
    // Sa Core 3, 'id' at 'contract_number' ang gamit
    $sql = "SELECT id, contract_number, status FROM shipments ORDER BY id DESC";
    $result = $conn->query($sql);

    $data = [];

    if ($result) {
        while($row = $result->fetch_assoc()) {
            // 3. MAPPING (Gawing kamukha ng Core 1 format)
            $data[] = [
                'shipment_id'   => $row['id'],                 // I-map ang id -> shipment_id
                'shipment_code' => $row['contract_number'],    // I-map ang contract -> shipment_code
                'status'        => $row['status']
            ];
        }
    }

    // 4. OUTPUT
    echo json_encode([
        "status" => "success",
        "source" => "Core 3 (Client)",
        "count" => count($data),
        "data" => $data
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>