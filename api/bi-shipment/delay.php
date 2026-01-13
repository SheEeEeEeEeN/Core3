<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include("../../connection.php");

$sql = "SELECT delay_reason, COUNT(*) as count 
        FROM shipments 
        WHERE status = 'delayed' 
        GROUP BY delay_reason";

$result = $conn->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        "reason" => $row["delay_reason"],
        "count" => intval($row["count"])
    ];
}

echo json_encode($data);
?>
