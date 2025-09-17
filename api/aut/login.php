<?php
// api/auth/login.php
require_once __DIR__ . '/../../connection.php';
session_start();
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['username']) || empty($input['password'])) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'username and password required']);
    exit;
}

$username = $input['username'];
$password = $input['password'];

$stmt = $conn->prepare("SELECT id, username, password, role FROM accounts WHERE username=? OR email=? LIMIT 1");
$stmt->bind_param('ss', $username, $username);
$stmt->execute();
$res = $stmt->get_result();
if (!$row = $res->fetch_assoc()) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'User not found']);
    exit;
}

if (!password_verify($password, $row['password'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Invalid password']);
    exit;
}

// Login success
session_regenerate_id(true);
$_SESSION['user_id'] = $row['id'];
$_SESSION['username'] = $row['username'];
$_SESSION['role'] = $row['role'];

echo json_encode(['success'=>true,'username'=>$row['username'],'role'=>$row['role']]);
