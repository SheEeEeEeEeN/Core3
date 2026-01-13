<?php
include '../../connection.php';
include '../../session.php';
requireRole('admin');

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

// Helper to get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Function to get contract stats
function getStats($conn) {
    $getCount = fn($sql) => (int) ($conn->query($sql)->fetch_row()[0] ?? 0);

    return [
        'totalContracts' => $getCount("SELECT COUNT(*) FROM csm"),
        'totalActive' => $getCount("SELECT COUNT(*) FROM csm WHERE status='Active'"),
        'expiringSoon' => $getCount("SELECT COUNT(*) FROM csm WHERE end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)"),
        'totalCompliant' => $getCount("SELECT COUNT(*) FROM csm WHERE sla_compliance='Compliant'")
    ];
}

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    if ($action === 'stats') {
        echo json_encode(['success' => true, 'data' => getStats($conn)]);
        exit;
    } elseif ($action === 'list') {
        $res = $conn->query("SELECT * FROM csm ORDER BY start_date DESC");
        $contracts = [];
        while ($row = $res->fetch_assoc()) $contracts[] = $row;
        echo json_encode(['success' => true, 'data' => $contracts]);
        exit;
    }
} elseif ($method === 'POST') {
    // Add contract
    $contract_limit = 100;
    $contract_id = trim($input['contract_id'] ?? '');
    $client_name = trim($input['client_name'] ?? '');
    $start_date = trim($input['start_date'] ?? '');
    $end_date = trim($input['end_date'] ?? '');
    $status = trim($input['status'] ?? '');
    $sla_compliance = trim($input['sla_compliance'] ?? '');

    $errors = [];
    foreach (['Contract ID' => $contract_id, 'Client Name' => $client_name, 'Start Date' => $start_date, 'End Date' => $end_date, 'Status' => $status, 'SLA Compliance' => $sla_compliance] as $k => $v) {
        if ($v === '') $errors[] = $k;
    }
    if ($errors) {
        echo json_encode(['success' => false, 'message' => 'Missing fields: ' . implode(', ', $errors)]);
        exit;
    }

    // Check contract limit and delete oldest if needed
    $total_contracts = (int) ($conn->query("SELECT COUNT(*) FROM csm")->fetch_row()[0] ?? 0);
    if ($total_contracts >= $contract_limit) {
        $conn->query("DELETE FROM csm ORDER BY start_date ASC LIMIT 1");
    }

    $stmt = $conn->prepare("INSERT INTO csm (contract_id, client_name, start_date, end_date, status, sla_compliance) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssss', $contract_id, $client_name, $start_date, $end_date, $status, $sla_compliance);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Contract added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }
    $stmt->close();
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Unsupported request']);
}
?>
