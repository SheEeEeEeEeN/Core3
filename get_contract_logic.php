<?php
// get_contract_logic.php
session_start();
include("connection.php");
header('Content-Type: application/json');

// 1. Check User
if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$email = $_SESSION['email'];
$originGroup = $_POST['origin_island'] ?? '';
$destGroup = $_POST['dest_island'] ?? '';

// 2. Get User ID
$u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM accounts WHERE email='$email'"));
$userId = $u['id'] ?? 0;

// 3. Find Active Contract
$contractQ = mysqli_query($conn, "SELECT * FROM contracts WHERE user_id='$userId' AND status='Active' AND end_date >= CURDATE() LIMIT 1");
$contract = mysqli_fetch_assoc($contractQ);

if (!$contract) {
    echo json_encode([
        'success' => false, 
        'message' => 'No Active Contract',
        'is_contracted' => false
    ]);
    exit;
}

// 4. Find SLA Rule based on Route
$slaDays = 7; // Default fallback (Standard)
$slaFound = false;

if (!empty($originGroup) && !empty($destGroup)) {
    $slaQ = mysqli_query($conn, "SELECT max_days FROM sla_policies WHERE contract_id='{$contract['id']}' AND origin_group='$originGroup' AND destination_group='$destGroup'");
    if ($row = mysqli_fetch_assoc($slaQ)) {
        $slaDays = $row['max_days'];
        $slaFound = true;
    }
}

// 5. Calculate Target Date
$targetDate = date('Y-m-d', strtotime("+$slaDays days"));
$displayDate = date('M d, Y', strtotime($targetDate));

echo json_encode([
    'success' => true,
    'is_contracted' => true,
    'contract_number' => $contract['contract_number'],
    'sla_days' => $slaDays,
    'target_date' => $targetDate,
    'display_text' => "Guaranteed by Contract: $slaDays Days ($displayDate)"
]);
?>