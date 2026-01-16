<?php
// admin_contracts_api.php - FIXED FOR YOUR TABLE STRUCTURE
include('../connection.php'); // Ang ../ ay "Go up one level"
include('../session.php');

header('Content-Type: application/json');

// Patayin ang error display para hindi masira ang JSON response
error_reporting(0); 
ini_set('display_errors', 0);

$response = ['success' => false, 'message' => '', 'error' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $action = $_POST['action'] ?? '';

    // ======================================================
    // ACTION 1: ADD RULE (Global or Specific)
    // ======================================================
    if ($action === 'add_rule') {
        $contract_id = $_POST['contract_id'];
        $origin_group = mysqli_real_escape_string($conn, $_POST['origin_group']);
        $destination_group = mysqli_real_escape_string($conn, $_POST['destination_group']);
        $max_days = (int)$_POST['max_days'];

        $sql = "INSERT INTO sla_policies (contract_id, origin_group, destination_group, max_days) 
                VALUES ('$contract_id', '$origin_group', '$destination_group', '$max_days')";

        if (mysqli_query($conn, $sql)) {
            $response['success'] = true;
            $response['message'] = "Rule saved successfully!";
        } else {
            $response['error'] = "DB Error: " . mysqli_error($conn);
        }
    } 

    // ======================================================
    // ACTION 2: SYNC ALL USERS (FIXED: Uses 'username')
    // ======================================================
    elseif ($action === 'sync_all_users') {
        
        // 1. KUNIN ANG USERNAME AT EMAIL (Ito ang tama sa table mo)
        $users = mysqli_query($conn, "SELECT id, username, email FROM accounts WHERE role='user'");
        
        if (!$users) {
            $response['error'] = "Query Failed: " . mysqli_error($conn);
            echo json_encode($response);
            exit;
        }

        $count = 0;

        while($u = mysqli_fetch_assoc($users)) {
            $uid = $u['id'];
            
            // Gamitin ang USERNAME bilang Client Name
            // Kung walang username, gamitin ang Email Prefix
            $clientName = !empty($u['username']) ? $u['username'] : explode('@', $u['email'])[0];
            $clientName = mysqli_real_escape_string($conn, strtoupper($clientName));

            // 2. Check kung may contract na
            $check = mysqli_query($conn, "SELECT id FROM contracts WHERE user_id='$uid'");
            
            // KUNG WALA PANG CONTRACT, GAWAN NATIN
            if(mysqli_num_rows($check) == 0) {
                
                $contract_num = "CNT-" . date('Y') . "-" . str_pad($uid, 4, '0', STR_PAD_LEFT);
                $start = date('Y-m-d');
                $end = date('Y-m-d', strtotime('+5 years')); 
                
                // Insert Contract
                $sql_ins = "INSERT INTO contracts (contract_number, user_id, client_name, start_date, end_date, status) 
                            VALUES ('$contract_num', '$uid', '$clientName', '$start', '$end', 'Active')";
                
                if(mysqli_query($conn, $sql_ins)) {
                    $new_contract_id = mysqli_insert_id($conn);
                    
                    // 3. KOPYAHIN ANG RULES GALING SA MASTER (ID 0)
                    $masterRules = mysqli_query($conn, "SELECT * FROM sla_policies WHERE contract_id = 0");
                    
                    if ($masterRules) {
                        while($rule = mysqli_fetch_assoc($masterRules)) {
                            $o = mysqli_real_escape_string($conn, $rule['origin_group']);
                            $d = mysqli_real_escape_string($conn, $rule['destination_group']);
                            $m = $rule['max_days'];
                            
                            mysqli_query($conn, "INSERT INTO sla_policies (contract_id, origin_group, destination_group, max_days) 
                                                 VALUES ('$new_contract_id', '$o', '$d', '$m')");
                        }
                    }
                    $count++;
                }
            }
        }
        
        $response['success'] = true;
        $response['message'] = "Sync Success! Generated contracts for $count users.";
    }

    // ======================================================
    // ACTION 3: DELETE RULE
    // ======================================================
    elseif ($action === 'delete_rule') {
        $rule_id = $_POST['rule_id'];
        mysqli_query($conn, "DELETE FROM sla_policies WHERE id='$rule_id'");
        $response['success'] = true;
    }
    
    else {
        $response['error'] = "Invalid Action";
    }
}

echo json_encode($response);
?>