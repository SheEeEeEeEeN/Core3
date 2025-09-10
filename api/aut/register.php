<?php
// api/auth/register.php
require_once __DIR__ . '/../../connection.php';
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['username']) || empty($input['email']) || empty($input['password'])) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'username, email and password required']);
    exit;
}

$username = trim($input['username']);
$email = trim($input['email']);
$password = $input['password'];

// Basic validation (you can expand)
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Invalid email']);
    exit;
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

// Check unique
$stmt = $conn->prepare("SELECT id FROM accounts WHERE username=? OR email=? LIMIT 1");
$stmt->bind_param('ss',$username,$email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['success'=>false,'message'=>'username or email already exists']);
    exit;
}
$stmt->close();

// Insert
$stmt = $conn->prepare("INSERT INTO accounts (username, email, password, role, created_at) VALUES (?, ?, ?, 'user', NOW())");
$stmt->bind_param('sss', $username, $email, $hashed);
if ($stmt->execute()) {
    echo json_encode(['success'=>true,'message'=>'Account created']);
} else {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Insert error: '.$conn->error]);
}
