<?php
// get_contract_logic.php
include("connection.php");
include("session.php");

header('Content-Type: application/json');

$origin = $_POST['origin_island'] ?? '';
$dest = $_POST['dest_island'] ?? '';
$user_email = $_SESSION['email'];

$response = [
    'success' => false,
    'is_contracted' => false,
    'contract_number' => 'STANDARD-RATE',
    'sla_days' => 7, // Default standard
    'target_date' => date('Y-m-d', strtotime('+7 days')),
    'display_text' => 'Standard Shipping (5-7 Days)'
];

if (!$origin || !$dest) {
    echo json_encode($response);
    exit;
}

// 1. HANAPIN ANG USER ID
$uQ = mysqli_query($conn, "SELECT id FROM accounts WHERE email='$user_email'");
$uRow = mysqli_fetch_assoc($uQ);
$user_id = $uRow['id'];

// 2. HANAPIN ANG CONTRACT NG USER
$cQ = mysqli_query($conn, "SELECT id, contract_number FROM contracts WHERE user_id='$user_id' AND status='Active' LIMIT 1");

if (mysqli_num_rows($cQ) > 0) {
    $contract = mysqli_fetch_assoc($cQ);
    $contract_id = $contract['id'];
    
    // UPDATE RESPONSE: May contract na siya!
    $response['is_contracted'] = true;
    $response['contract_number'] = $contract['contract_number']; // E.g., CNT-2026-0001
    
    // 3. HANAPIN ANG SLA RULE PARA SA ROUTE NA ITO
    // (Example: Origin=Luzon, Dest=Visayas)
    $ruleQ = mysqli_query($conn, "SELECT max_days FROM sla_policies 
                                  WHERE contract_id='$contract_id' 
                                  AND origin_group='$origin' 
                                  AND destination_group='$dest'");
                                  
    if (mysqli_num_rows($ruleQ) > 0) {
        // MERONG SPECIAL RULE (e.g., 3 Days)
        $rule = mysqli_fetch_assoc($ruleQ);
        $days = $rule['max_days'];
        
        $response['success'] = true;
        $response['sla_days'] = $days;
        $response['target_date'] = date('Y-m-d', strtotime("+$days days"));
        $response['display_text'] = "Priority SLA ($days Days Guaranteed)";
    } else {
        // WALANG SPECIAL RULE, PERO MAY CONTRACT PA RIN
        // Gamitin ang Standard days pero ilabas pa rin ang Contract Number
        $response['success'] = true;
        $response['display_text'] = "Standard Contract Terms (5-7 Days)";
        // Note: Hindi natin babaguhin ang contract_number, mananatili siyang CNT-XXXX
    }
} else {
    // WALANG CONTRACT (Fallback)
    $response['success'] = true; // Success query, pero walang contract logic
}

echo json_encode($response);
?>