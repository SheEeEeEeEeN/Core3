<?php
    // htdocs/core_admin/api_receiver.php
    // ITO ANG SALUHAN (Receiver). Dito babagsak ang request galing Sales Dept.

    header('Content-Type: application/json'); // Return JSON format

// 1. IMPORT NIYO YUNG CONNECTIONS AT LOGIC NIYO
include '../connection.php';    // Ito yung connection sa Admin DB
include '../file_handler.php';  // Ito yung engine na nag-uupload (yung may uploadDocument function)

// 2. SECURITY CHECK (API KEY)
// Para hindi kung sino-sino lang ang tumatawag.
$headers = getallheaders();
$api_key = isset($headers['X-API-KEY']) ? $headers['X-API-KEY'] : '';

// Check kung tama ang susi (Dapat match ito sa Sales Dept)
if($api_key !== "SECRET_KEY_12345") {
    echo json_encode(["status" => "error", "message" => "Unauthorized Access! Wrong API Key."]);
    exit();
}

// 3. CHECK REQUEST METHOD
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed."]);
    exit();
}

// 4. RECEIVE DATA FROM SENDER
$tracking_num = $_POST['tracking_number'] ?? '';
$doc_type = $_POST['doc_type'] ?? 'Other';
$uploader = "API User (Sales Dept)"; // Pwede mo hardcode o kunin din sa POST

// 5. VALIDATE INPUT
if(empty($tracking_num) || empty($_FILES['doc_file'])) {
    echo json_encode(["status" => "error", "message" => "Missing parameters (tracking_number or file)."]);
    exit();
}

// ============================================================
// DITO YUNG PART NA TINATANONG MO BES:
// Tatawagin natin yung function para mag-save sa Admin DB
// ============================================================

$result = uploadDocument($conn, $tracking_num, $doc_type, $_FILES['doc_file'], $uploader);

// 6. RETURN RESPONSE (Balik tayo ng message sa Sender)
echo json_encode($result);
?>