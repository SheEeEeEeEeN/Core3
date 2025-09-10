<?php
// api/shipment/track.php
require_once __DIR__ . '/../../connection.php';
session_start();
header('Content-Type: application/json; charset=utf-8');

// Accept GET ?tracking=ID or POST {tracking}
$tracking = $_GET['tracking'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $tracking = $input['tracking'] ?? $tracking;
}
if (!$tracking) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'tracking required']);
    exit;
}

// NOTE: shipments table is not in your dump; this code assumes a table 'shipments' exists.
// If not, return example data.
$res = $conn->prepare("SELECT tracking_no, origin, destination, weight, status, booked_date FROM shipments WHERE tracking_no = ? LIMIT 1");
if ($res) {
    $res->bind_param('s', $tracking);
    $res->execute();
    $r = $res->get_result();
    if ($row = $r->fetch_assoc()) {
        echo json_encode(['success'=>true,'data'=>$row]);
    } else {
        http_response_code(404);
        echo json_encode(['success'=>false,'message'=>'Not found']);
    }
} else {
    // fallback: return example if shipments table missing
    echo json_encode(['success'=>false,'message'=>'shipments table not found on server — create table or adjust this endpoint. Example response:','example'=>[
        'tracking_no'=>'FRT12345','origin'=>'Manila','destination'=>'Cebu','weight'=>'25','status'=>'In Transit','booked_date'=>'2025-09-01'
    ]]);
}
