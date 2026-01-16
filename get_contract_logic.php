<?php
// get_contract_logic.php
// Ito ang tinatawag ng bookshipment.php via AJAX
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

// Default Values (Kapag walang contract o walang rule)
$response = [
    'contract_number' => 'STANDARD-RATE',
    'is_contracted' => false,
    'sla_days' => 7, // Default 7 days pag wala sa DB
    'target_date' => date('Y-m-d', strtotime('+7 days')),
    'display_text' => 'Standard Terms (5-7 Days)' // Ito yung nakikita mo ngayon
];

if ($contract) {
    // KUNG MAY ACTIVE CONTRACT SI CLIENT
    $response['contract_number'] = $contract['contract_number'];
    $response['is_contracted'] = true;
    $contract_id = $contract['id'];

    // 3. HANAPIN ANG SLA RULE (Specific to Route)
    // Priority: Specific Contract Rule -> Master Rule (ID 0)
    
    // Check muna sa sariling contract
    $ruleQ = mysqli_query($conn, "SELECT max_days FROM sla_policies 
                                  WHERE contract_id='$contract_id' 
                                  AND origin_group='$origin' 
                                  AND destination_group='$dest'");

    // Kung wala sa sarili, check sa Master (ID 0)
    if (mysqli_num_rows($ruleQ) == 0) {
        $ruleQ = mysqli_query($conn, "SELECT max_days FROM sla_policies 
                                      WHERE contract_id='0' 
                                      AND origin_group='$origin' 
                                      AND destination_group='$dest'");
    }

    if (mysqli_num_rows($ruleQ) > 0) {
        // MERONG RULE! (Ito ang gusto mong lumabas)
        $rule = mysqli_fetch_assoc($ruleQ);
        $days = $rule['max_days'];
        
        $response['sla_days'] = $days;
        $response['target_date'] = date('Y-m-d', strtotime("+$days days"));
        
        // Dito natin papalitan yung text para maging specific
        $response['display_text'] = "Guaranteed Delivery within $days Days";
    } else {
        // WALANG RULE SA DB (Kaya fallback ang lumalabas)
        // Pwede nating baguhin ang text para alam mong wala sa DB
        $response['display_text'] = "Standard Route (No specific SLA set)"; 
    }
}

echo json_encode($response);
?>