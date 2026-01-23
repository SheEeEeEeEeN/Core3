<?php
// get_contract_logic.php
include("connection.php");
include('session.php');

header('Content-Type: application/json');

$origin = $_POST['origin_island'] ?? '';
$dest = $_POST['dest_island'] ?? '';
$username = $_SESSION['email'];

// 1. HANAPIN ANG USER ID
$uQ = mysqli_query($conn, "SELECT id FROM accounts WHERE email='$username'");
$user = mysqli_fetch_assoc($uQ);
$user_id = $user['id'];

// 2. HANAPIN ANG ACTIVE CONTRACT NG USER
$conQ = mysqli_query($conn, "SELECT * FROM contracts WHERE user_id='$user_id' AND status='Active' LIMIT 1");
$contract = mysqli_fetch_assoc($conQ);

// --- UNIQUE ID GENERATOR (Para sa walang contract) ---
// Format: CN-YYYY-RANDOM (e.g., CN-2026-A1B2C)
$unique_id = "CN-" . date("Y") . "-" . strtoupper(substr(md5(uniqid(rand(), true)), 0, 5));

// Default Values
$response = [
    'contract_number' => $unique_id, // Default unique ID
    'is_contracted' => false,
    'sla_days' => 7, 
    'target_date' => date('Y-m-d', strtotime('+7 days')),
    'display_text' => 'Standard Rate (No Contract)'
];

if ($contract) {
    // KUNG MAY ACTIVE CONTRACT, OVERWRITE ANG DEFAULT
    $response['contract_number'] = $contract['contract_number'];
    $response['is_contracted'] = true;
    $contract_id = $contract['id'];

    // 3. HANAPIN ANG SLA RULE
    $ruleQ = mysqli_query($conn, "SELECT max_days FROM sla_policies 
                                  WHERE contract_id='$contract_id' 
                                  AND origin_group='$origin' 
                                  AND destination_group='$dest'");

    if (mysqli_num_rows($ruleQ) == 0) {
        $ruleQ = mysqli_query($conn, "SELECT max_days FROM sla_policies 
                                      WHERE contract_id='0' 
                                      AND origin_group='$origin' 
                                      AND destination_group='$dest'");
    }

    if (mysqli_num_rows($ruleQ) > 0) {
        $rule = mysqli_fetch_assoc($ruleQ);
        $days = $rule['max_days'];
        
        $response['sla_days'] = $days;
        $response['target_date'] = date('Y-m-d', strtotime("+$days days"));
        $response['display_text'] = "Guaranteed Delivery within $days Days";
    }
}

echo json_encode($response);
?>