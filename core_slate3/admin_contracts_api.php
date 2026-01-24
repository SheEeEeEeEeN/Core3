<?php
// admin_contracts_api.php - NO UPLOAD VERSION
include("connection.php"); 
header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'error' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $action = $_POST['action'] ?? '';

    // --- CASE 1: CREATE NEW CONTRACT (Pure Data Only) ---
    if ($action === 'create_contract') {
        
        $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
        $client_name = mysqli_real_escape_string($conn, $_POST['client_name']);
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        
        // Generate Contract Number
        $contract_number = "CNT-" . date('Ymd') . "-" . rand(100, 999);
        $status = "Active";

        // Insert to Database (WALA NANG contract_file)
        $sql = "INSERT INTO contracts (contract_number, user_id, client_name, start_date, end_date, status) 
                VALUES ('$contract_number', '$user_id', '$client_name', '$start_date', '$end_date', '$status')";

        if (mysqli_query($conn, $sql)) {
            $response['success'] = true;
            $response['message'] = "Contract $contract_number created successfully!";
        } else {
            $response['error'] = "Database Error: " . mysqli_error($conn);
        }
    }

    // --- CASE 2: ADD SLA RULE ---
    elseif ($action === 'add_rule') {
        $contract_id = $_POST['contract_id'];
        $origin_group = mysqli_real_escape_string($conn, $_POST['origin_group']);
        $destination_group = mysqli_real_escape_string($conn, $_POST['destination_group']);
        $max_days = (int)$_POST['max_days'];

        $sql = "INSERT INTO sla_policies (contract_id, origin_group, destination_group, max_days) 
                VALUES ('$contract_id', '$origin_group', '$destination_group', '$max_days')";

        if (mysqli_query($conn, $sql)) {
            $response['success'] = true;
            $response['message'] = "SLA Rule added successfully!";
        } else {
            $response['error'] = "Database Error: " . mysqli_error($conn);
        }
    } 
    
    else {
        $response['error'] = "Invalid Action";
    }
}

echo json_encode($response);
?>