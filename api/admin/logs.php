<?php
// api/admin/logs.php
require_once __DIR__ . '/../../connection.php';
session_start();
header('Content-Type: application/json; charset=utf-8');

function require_admin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success'=>false,'message'=>'Admin only']);
        exit;
    }
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
    $stmt = $conn->prepare("SELECT id, date, module, activity, status FROM admin_activity ORDER BY date DESC LIMIT ?");
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $res = $stmt->get_result();
    $out = [];
    while($r = $res->fetch_assoc()) $out[] = $r;
    echo json_encode(['success'=>true,'data'=>$out]);
    exit;
}

// Allow internal POST logging (admin only)
if ($method === 'POST') {
    require_admin();
    $input = json_decode(file_get_contents('php://input'), true);
    $module = $input['module'] ?? 'API';
    $activity = $input['activity'] ?? '';
    $status = $input['status'] ?? 'Info';
    if (!$activity) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'activity required']); exit; }
    $stmt = $conn->prepare("INSERT INTO admin_activity (module, activity, status, date) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param('sss', $module, $activity, $status);
    if ($stmt->execute()) echo json_encode(['success'=>true,'id'=>$stmt->insert_id]);
    else { http_response_code(500); echo json_encode(['success'=>false,'message'=>$conn->error]); }
    exit;
}

http_response_code(405);
echo json_encode(['success'=>false,'message'=>'Method not allowed']);
