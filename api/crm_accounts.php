<?php
// api/accounts.php
include '../connection.php';
include '../session.php';
requireRole('admin');

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// Helper function for admin activity logging
function logActivity($conn, $module, $activity, $status) {
    $stmt = $conn->prepare("INSERT INTO admin_activity (module, activity, status) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $module, $activity, $status);
    $stmt->execute();
}

// Get user ID if present
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

// -------------------- GET --------------------
if ($method === 'GET') {
    if ($id) {
        $stmt = $conn->prepare("SELECT id, username, email, role, profile_image, created_at FROM accounts WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        if ($user) {
            echo json_encode(['success'=>true, 'data'=>$user]);
        } else {
            http_response_code(404);
            echo json_encode(['success'=>false, 'message'=>'User not found']);
        }
    } else {
        $res = $conn->query("SELECT id, username, email, role, profile_image, created_at FROM accounts ORDER BY id DESC");
        $users = [];
        while ($row = $res->fetch_assoc()) $users[] = $row;
        echo json_encode(['success'=>true, 'data'=>$users]);
    }
    exit;
}

// -------------------- POST (Add User) --------------------
if ($method === 'POST') {
    if (!$input || !isset($input['username'], $input['email'], $input['role'])) {
        http_response_code(400);
        echo json_encode(['success'=>false, 'message'=>'Missing required fields']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO accounts (username,email,role) VALUES (?,?,?)");
    $stmt->bind_param("sss", $input['username'], $input['email'], $input['role']);

    if ($stmt->execute()) {
        logActivity($conn, 'CRM', "Added new user: ".$input['username'], 'Success');
        echo json_encode(['success'=>true, 'message'=>'User added', 'id'=>$conn->insert_id]);
    } else {
        logActivity($conn, 'CRM', "Add user failed: ".$input['username'], 'Failed');
        http_response_code(500);
        echo json_encode(['success'=>false, 'message'=>$conn->error]);
    }
    exit;
}

// -------------------- PUT (Update User) --------------------
if ($method === 'PUT') {
    if (!$id || !$input || !isset($input['username'], $input['email'], $input['role'])) {
        http_response_code(400);
        echo json_encode(['success'=>false,'message'=>'ID and required fields required']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE accounts SET username=?, email=?, role=? WHERE id=?");
    $stmt->bind_param("sssi", $input['username'], $input['email'], $input['role'], $id);

    if ($stmt->execute()) {
        logActivity($conn, 'CRM', "Updated user: ".$input['username'], 'Success');
        echo json_encode(['success'=>true, 'message'=>'User updated']);
    } else {
        logActivity($conn, 'CRM', "Update failed for ID $id", 'Failed');
        http_response_code(500);
        echo json_encode(['success'=>false, 'message'=>$conn->error]);
    }
    exit;
}

// -------------------- DELETE --------------------
if ($method === 'DELETE') {
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success'=>false, 'message'=>'ID required']);
        exit;
    }

    $res = $conn->query("SELECT username FROM accounts WHERE id=$id");
    $row = $res->fetch_assoc();
    $username = $row ? $row['username'] : '';

    if ($conn->query("DELETE FROM accounts WHERE id=$id") === TRUE) {
        logActivity($conn, 'CRM', "Deleted user: $username", 'Success');
        echo json_encode(['success'=>true, 'message'=>'User deleted']);
    } else {
        logActivity($conn, 'CRM', "Delete failed for ID $id", 'Failed');
        http_response_code(500);
        echo json_encode(['success'=>false, 'message'=>$conn->error]);
    }
    exit;
}

// -------------------- METHOD NOT ALLOWED --------------------
http_response_code(405);
echo json_encode(['success'=>false, 'message'=>'Method not allowed']);
