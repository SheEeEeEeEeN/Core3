<?php
include("../../connection.php"); // adjust path if needed
header("Content-Type: application/json");

// ✅ use the correct table name: costs
$sql = "SELECT category, SUM(amount) AS total 
        FROM bi_costs 
        GROUP BY category";

$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        "category" => $row["category"],
        "total"    => (float)$row["total"]
    ];
}

echo json_encode($data);
?>
