<?php
// api/get_notifications.php
include("../connection.php");
include("../session.php");

header('Content-Type: application/json');

if(!isset($_SESSION['account_id'])) {
    echo json_encode(['count' => 0, 'data' => []]);
    exit;
}

$user_id = $_SESSION['account_id'];

// 1. Mark as Read if requested
if(isset($_POST['action']) && $_POST['action'] == 'read_all') {
    $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id");
    echo json_encode(['success' => true]);
    exit;
}

// 2. Count Unread
$countQ = $conn->query("SELECT COUNT(*) as unread FROM notifications WHERE user_id = $user_id AND is_read = 0");
$count = $countQ->fetch_assoc()['unread'];

// 3. Get Latest 5 Notifications
$dataQ = $conn->query("SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");
$notifications = [];
while($row = $dataQ->fetch_assoc()) {
    $notifications[] = $row;
}

echo json_encode(['count' => $count, 'data' => $notifications]);
?>