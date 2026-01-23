<?php
// FILE: core3/api/sync_status_api.php
header('Content-Type: application/json');
include('../connection.php'); 

// --- DEBUG LOGGER ---
function logSync($msg) {
    file_put_contents("sync_debug.txt", date('H:i:s') . " - " . $msg . "\n", FILE_APPEND);
}

logSync("--- NEW REQUEST RECEIVED ---");

$secret_key = "SLATE_SECRET_123";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logSync("Error: Invalid Method");
    echo json_encode(['success' => false, 'message' => 'Invalid Request Method']);
    exit;
}

$input_key = $_POST['secret_key'] ?? '';
$tracking_code = $_POST['tracking_code'] ?? '';
$new_status = $_POST['status'] ?? '';

// Log natin kung ano ang natanggap
logSync("Tracking Code: " . $tracking_code);
logSync("New Status: " . $new_status);

if ($input_key !== $secret_key) {
    logSync("Error: Wrong Secret Key");
    echo json_encode(['success' => false, 'message' => 'Unauthorized Access']);
    exit;
}

// UPDATE CORE 3
// Note: 'contract_number' ang ginagamit natin kasi yun ang PO-C3-XXX
$stmt = $conn->prepare("UPDATE shipments SET status = ? WHERE contract_number = ?");
$stmt->bind_param("ss", $new_status, $tracking_code);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        logSync("SUCCESS: Database Updated!");
        echo json_encode(['success' => true, 'message' => 'Updated']);
    } else {
        logSync("WARNING: No rows affected. Baka mali ang tracking code o same status na.");
        // Try natin i-check kung nag-eexist ba yung tracking code
        $chk = $conn->query("SELECT id FROM shipments WHERE contract_number = '$tracking_code'");
        if($chk->num_rows == 0) {
            logSync("ERROR: Tracking code '$tracking_code' NOT FOUND in shipments table.");
        }
        echo json_encode(['success' => false, 'message' => 'No changes made']);
    }
} else {
    logSync("DB ERROR: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Database Error']);
}
?>