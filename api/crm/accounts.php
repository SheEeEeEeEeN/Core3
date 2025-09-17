<?php
include '../../connection.php';
include '../../session.php';
requireRole('admin');

header('Content-Type: application/json; charset=utf-8');

$result = $conn->query("SELECT id, username FROM accounts ORDER BY username ASC");

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = [
        'id' => $row['id'],
        'username' => $row['username']
    ];
}

echo json_encode([
    'success' => true,
    'data' => $users
]);
exit;