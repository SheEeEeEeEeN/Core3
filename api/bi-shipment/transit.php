<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include("../../connection.php");

$sql = "SELECT DATE_FORMAT(created_at, '%b') as month, 
               AVG(transit_time) as avg_days
        FROM shipments
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY created_at";

$result = $conn->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        "month" => $row["month"],
        "avg_days" => round($row["avg_days"], 2)
    ];
}

echo json_encode($data);
?>
