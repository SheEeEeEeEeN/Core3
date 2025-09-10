<?php
// api/shipment/history.php
require_once __DIR__ . '/../../connection.php';
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Not authenticated']);
    exit;
}
$username = $_SESSION['username'];

// shipments table assumed to have field 'username' to link to user
$stmt = $conn->prepare("SELECT tracking_no, origin, destination, weight, status, booked_date FROM shipments WHERE username = ? ORDER BY booked_date DESC");
if ($stmt) {
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();
    $out = [];
    while ($r = $res->fetch_assoc()) $out[] = $r;
    echo json_encode(['success'=>true,'data'=>$out]);
} else {
    echo json_encode(['success'=>false,'message'=>'shipments table not found on server']);
}
