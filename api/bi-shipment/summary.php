<?php
include("../../connection.php");
header("Content-Type: application/json");

// Example: last 7 days shipments
$sql = "
  SELECT DATE(created_at) as day, COUNT(*) as total
  FROM shipments
  WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
  GROUP BY DATE(created_at)
  ORDER BY day ASC
";

$result = $conn->query($sql);
$data = [];

while($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
