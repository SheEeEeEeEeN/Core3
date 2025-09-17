<?php
// api/csm/contracts.php
require_once __DIR__ . '/../../connection.php';
session_start();
header('Content-Type: application/json; charset=utf-8');

function require_admin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success'=>false,'message'=>'Admin only']);
        exit;
    }
}

$method = $_SERVER['REQUEST_METHOD'];

// GET: list or single
if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM csm WHERE id=? LIMIT 1");
        $stmt->bind_param('i',$id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        if ($row) echo json_encode(['success'=>true,'data'=>$row]);
        else { http_response_code(404); echo json_encode(['success'=>false,'message'=>'Not found']); }
        exit;
    }

    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
    $stmt = $conn->prepare("SELECT * FROM csm ORDER BY start_date DESC LIMIT ?");
    $stmt->bind_param('i',$limit);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = [];
    while($r = $res->fetch_assoc()) $data[] = $r;
    echo json_encode(['success'=>true,'data'=>$data]);
    exit;
}

// POST: add (admin)
if ($method === 'POST') {
    require_admin();
    $input = json_decode(file_get_contents('php://input'), true);
    $contract_id = $input['contract_id'] ?? '';
    $client_name = $input['client_name'] ?? '';
    $start_date = $input['start_date'] ?? '';
    $end_date = $input['end_date'] ?? '';
    $status = $input['status'] ?? 'Pending';
    $sla = $input['sla_compliance'] ?? 'Non-Compliant';

    if (!$contract_id || !$client_name || !$start_date || !$end_date) {
        http_response_code(400);
        echo json_encode(['success'=>false,'message'=>'Missing required fields']);
        exit;
    }

    // enforce contract limit (same logic as your UI)
    $limit = 100;
    $res = $conn->query("SELECT COUNT(*) AS total FROM csm");
    $row = $res->fetch_assoc();
    $total = (int)($row['total'] ?? 0);
    if ($total >= $limit) {
        // delete oldest
        $conn->query("DELETE FROM csm ORDER BY start_date ASC LIMIT 1");
        $activity = "Deleted oldest contract due to limit $limit";
        $conn->query($conn->prepare("INSERT INTO admin_activity (module, activity, status) VALUES ('CSM', ?, 'Success')")->bind_param('s',$activity));
    }

    $stmt = $conn->prepare("INSERT INTO csm (contract_id, client_name, start_date, end_date, status, sla_compliance) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssss',$contract_id,$client_name,$start_date,$end_date,$status,$sla);
    if ($stmt->execute()) {
        $id = $stmt->insert_id;
        $act = "Added new contract: $contract_id - $client_name";
        $conn->query("INSERT INTO admin_activity (module, activity, status) VALUES ('CSM', '". $conn->real_escape_string($act) ."', 'Success')");
        echo json_encode(['success'=>true,'id'=>$id]);
    } else {
        http_response_code(500);
        echo json_encode(['success'=>false,'message'=>$conn->error]);
    }
    exit;
}

// PUT: update (admin)
if ($method === 'PUT') {
    require_admin();
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    if (!$id) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'id required']); exit; }

    $fields = [];
    $params = [];
    $types = '';
    if (isset($input['contract_id'])) { $fields[]='contract_id=?'; $params[]=$input['contract_id']; $types.='s'; }
    if (isset($input['client_name'])) { $fields[]='client_name=?'; $params[]=$input['client_name']; $types.='s'; }
    if (isset($input['start_date'])) { $fields[]='start_date=?'; $params[]=$input['start_date']; $types.='s'; }
    if (isset($input['end_date'])) { $fields[]='end_date=?'; $params[]=$input['end_date']; $types.='s'; }
    if (isset($input['status'])) { $fields[]='status=?'; $params[]=$input['status']; $types.='s'; }
    if (isset($input['sla_compliance'])) { $fields[]='sla_compliance=?'; $params[]=$input['sla_compliance']; $types.='s'; }

    if (empty($fields)) { echo json_encode(['success'=>false,'message'=>'No fields']); exit; }

    $sql = "UPDATE csm SET ".implode(',',$fields)." WHERE id=?";
    $types .= 'i'; $params[] = $id;
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    if ($stmt->execute()) {
        $act = "Updated contract ID $id";
        $conn->query("INSERT INTO admin_activity (module, activity, status) VALUES ('CSM', '". $conn->real_escape_string($act) ."', 'Success')");
        echo json_encode(['success'=>true]);
    } else {
        http_response_code(500);
        echo json_encode(['success'=>false,'message'=>$conn->error]);
    }
    exit;
}

// DELETE (admin)
if ($method === 'DELETE') {
    require_admin();
    parse_str(file_get_contents('php://input'), $vars);
    $id = (int)($vars['id'] ?? 0);
    if (!$id) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'id required']); exit; }
    $res = $conn->query("SELECT contract_id, client_name FROM csm WHERE id=$id");
    $row = $res->fetch_assoc();
    $name = $row ? $row['contract_id'].' - '.$row['client_name'] : "ID $id";
    if ($conn->query("DELETE FROM csm WHERE id=$id")) {
        $act = "Deleted contract: $name";
        $conn->query("INSERT INTO admin_activity (module, activity, status) VALUES ('CSM', '". $conn->real_escape_string($act) ."', 'Success')");
        echo json_encode(['success'=>true]);
    } else {
        http_response_code(500);
        echo json_encode(['success'=>false,'message'=>$conn->error]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success'=>false,'message'=>'Method not allowed']);
