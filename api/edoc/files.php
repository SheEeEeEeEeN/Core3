<?php
// api/edoc/files.php
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

// GET: list (with optional search/status)
if ($method === 'GET') {
    $q = isset($_GET['q']) ? "%{$_GET['q']}%" : '%';
    $status = $_GET['status'] ?? null;

    if ($status) {
        $stmt = $conn->prepare("SELECT * FROM e_doc WHERE (title LIKE ? OR doc_type LIKE ?) AND status=? ORDER BY uploaded_on DESC");
        $stmt->bind_param('sss', $q, $q, $status);
    } else {
        $stmt = $conn->prepare("SELECT * FROM e_doc WHERE (title LIKE ? OR doc_type LIKE ?) ORDER BY uploaded_on DESC");
        $stmt->bind_param('ss', $q, $q);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $out = [];
    while ($r = $res->fetch_assoc()) $out[] = $r;
    echo json_encode(['success'=>true,'data'=>$out]);
    exit;
}

// POST: upload file (multipart/form-data) or insert metadata
if ($method === 'POST') {
    require_admin();
    // If a file was uploaded
    if (!empty($_FILES['uploadfile']) && $_FILES['uploadfile']['error'] === UPLOAD_ERR_OK) {
        $title = $_POST['docstitle'] ?? '';
        $doc_type = $_POST['doc_type'] ?? '';
        $file = $_FILES['uploadfile'];
        $allowed = ['pdf','jpg','jpeg','png','doc','docx'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            http_response_code(400);
            echo json_encode(['success'=>false,'message'=>'Invalid file type']);
            exit;
        }

        $targetDir = __DIR__ . '/../../uploads/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $safeName = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/','',basename($file['name']));
        $target = $targetDir . $safeName;

        if (!move_uploaded_file($file['tmp_name'],$target)) {
            http_response_code(500);
            echo json_encode(['success'=>false,'message'=>'Upload failed']);
            exit;
        }

        $rel = 'uploads/' . $safeName;
        $stmt = $conn->prepare("INSERT INTO e_doc (title, doc_type, filename, status, uploaded_on) VALUES (?,?,?,?, NOW())");
        $status = 'Pending Review';
        $stmt->bind_param('ssss', $title, $doc_type, $rel, $status);
        if ($stmt->execute()) {
            $conn->query("INSERT INTO admin_activity (module, activity, status) VALUES ('E-Documentation', 'Uploaded document: ". $conn->real_escape_string($title) ."', 'Pending Review')");
            echo json_encode(['success'=>true,'message'=>'Uploaded','id'=>$stmt->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(['success'=>false,'message'=>$conn->error]);
        }
        exit;
    }

    // else metadata-only (not typical)
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input) {
        require_admin();
        $title = $input['title'] ?? '';
        $doc_type = $input['doc_type'] ?? '';
        $filename = $input['filename'] ?? '';
        $status = $input['status'] ?? 'Pending Review';
        if (!$title || !$doc_type || !$filename) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Missing']); exit; }
        $stmt = $conn->prepare("INSERT INTO e_doc (title, doc_type, filename, status, uploaded_on) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param('ssss', $title, $doc_type, $filename, $status);
        if ($stmt->execute()) { echo json_encode(['success'=>true,'id'=>$stmt->insert_id]); } else { http_response_code(500); echo json_encode(['success'=>false,'message'=>$conn->error]); }
        exit;
    }

    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'No file or data provided']);
    exit;
}

// PUT: update metadata (admin)
if ($method === 'PUT') {
    require_admin();
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    if (!$id) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'id required']); exit; }

    $fields=[]; $params=[]; $types='';
    if (isset($input['title'])) { $fields[]='title=?'; $params[]=$input['title']; $types.='s'; }
    if (isset($input['doc_type'])) { $fields[]='doc_type=?'; $params[]=$input['doc_type']; $types.='s'; }
    if (isset($input['status'])) { $fields[]='status=?'; $params[]=$input['status']; $types.='s'; }

    if (!$fields) { echo json_encode(['success'=>false,'message'=>'No fields']); exit; }

    $sql = "UPDATE e_doc SET ".implode(',',$fields)." WHERE id=?";
    $types.='i'; $params[]=$id;
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    if ($stmt->execute()) {
        $conn->query("INSERT INTO admin_activity (module, activity, status) VALUES ('E-Documentation', 'Edited document ID $id', 'Success')");
        echo json_encode(['success'=>true]);
    } else {
        http_response_code(500);
        echo json_encode(['success'=>false,'message'=>$conn->error]);
    }
    exit;
}

// DELETE: remove doc file (admin)
if ($method === 'DELETE') {
    require_admin();
    parse_str(file_get_contents('php://input'), $d);
    $id = (int)($d['id'] ?? 0);
    if (!$id) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'id required']); exit; }

    $res = $conn->query("SELECT filename, title FROM e_doc WHERE id=$id");
    $row = $res->fetch_assoc();
    if ($row) {
        $file = __DIR__ . '/../../' . $row['filename'];
        if (file_exists($file)) @unlink($file);
        $conn->query("DELETE FROM e_doc WHERE id=$id");
        $conn->query("INSERT INTO admin_activity (module, activity, status) VALUES ('E-Documentation', 'Deleted document: ". $conn->real_escape_string($row['title']) ."', 'Deleted')");
        echo json_encode(['success'=>true]);
    } else {
        http_response_code(404);
        echo json_encode(['success'=>false,'message'=>'Not found']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success'=>false,'message'=>'Method not allowed']);
