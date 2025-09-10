<?php
// api/accounts/profile.php
require_once __DIR__ . '/../../connection.php';
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Not authenticated']);
    exit;
}

$username = $_SESSION['username'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $conn->prepare("SELECT id, username, email, role, created_at, profile_image FROM accounts WHERE username=? LIMIT 1");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    if ($row) {
        $row['profile_image'] = (!empty($row['profile_image']) && file_exists(__DIR__ . '/../../'.$row['profile_image'])) ? $row['profile_image'] : 'default-avatar.png';
        echo json_encode(['success'=>true,'data'=>$row]);
    } else {
        http_response_code(404);
        echo json_encode(['success'=>false,'message'=>'User not found']);
    }
    exit;
}

// Update profile image via POST (multipart/form-data)
if ($method === 'POST') {
    if (!isset($_FILES['profile_image'])) {
        http_response_code(400);
        echo json_encode(['success'=>false,'message'=>'File missing (profile_image)']);
        exit;
    }
    $file = $_FILES['profile_image'];
    $allowed = ['jpg','jpeg','png','gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        http_response_code(400);
        echo json_encode(['success'=>false,'message'=>'Invalid file type']);
        exit;
    }

    $targetDir = __DIR__ . '/../../uploads/';
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/','', basename($file['name']));
    $target = $targetDir . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        http_response_code(500);
        echo json_encode(['success'=>false,'message'=>'Error saving file']);
        exit;
    }

    // Save relative path in DB
    $relPath = 'uploads/' . $safeName;
    $stmt = $conn->prepare("UPDATE accounts SET profile_image=? WHERE username=?");
    $stmt->bind_param('ss',$relPath, $username);
    if ($stmt->execute()) {
        echo json_encode(['success'=>true,'message'=>'Profile updated','profile_image'=>$relPath]);
    } else {
        http_response_code(500);
        echo json_encode(['success'=>false,'message'=>'DB update failed']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success'=>false,'message'=>'Method not allowed']);
