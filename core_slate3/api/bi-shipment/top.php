<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include("../../connection.php");

$sql = "SELECT CONCAT(origin, '-', destination) as route, COUNT(*) as shipments 
        FROM shipments 
        GROUP BY route 
        ORDER BY shipments DESC 
        LIMIT 5";

$result = $conn->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        "route" => $row["route"],
        "shipments" => intval($row["shipments"])
    ];
}

echo json_encode($data);
?>
