<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include __DIR__ . '/../connection.php';
include __DIR__ . '/../session.php';
requireRole('admin');

// Get request data
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Optional filters: title, type, status
        $title  = isset($_GET['title']) ? $conn->real_escape_string($_GET['title']) : '';
        $type   = isset($_GET['doc_type']) ? $conn->real_escape_string($_GET['doc_type']) : '';
        $status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';

        $sql = "SELECT * FROM e_doc WHERE 1";
        if ($title) $sql .= " AND title LIKE '%$title%'";
        if ($type) $sql .= " AND doc_type='$type'";
        if ($status) $sql .= " AND status='$status'";
        $sql .= " ORDER BY uploaded_on DESC";

        $result = $conn->query($sql);
        $docs = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $docs[] = $row;
            }
        }

        echo json_encode(['success' => true, 'documents' => $docs]);
        break;

    case 'POST':
        if (!isset($_FILES['uploadfile'])) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded']);
            exit;
        }

        $title = $conn->real_escape_string($_POST['docstitle'] ?? '');
        $doc_type = $conn->real_escape_string($_POST['doc_type'] ?? '');

        $targetDir = __DIR__ . "/uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = basename($_FILES["uploadfile"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["uploadfile"]["tmp_name"], $targetFilePath)) {
            $conn->query("INSERT INTO e_doc (title, doc_type, filename, status) 
                          VALUES ('$title', '$doc_type', '$fileName', 'Pending Review')");

            // Record activity
            $conn->query("INSERT INTO admin_activity (`module`,`activity`,`status`,`date`) 
                          VALUES ('E-Documentation','Uploaded document: $title','Pending Review',NOW())");

            echo json_encode(['success' => true, 'message' => 'Document uploaded successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload document']);
        }
        break;

    case 'PUT':
        parse_str(file_get_contents("php://input"), $put_vars);
        $id = intval($put_vars['id'] ?? 0);
        $title = $conn->real_escape_string($put_vars['docstitle'] ?? '');
        $doc_type = $conn->real_escape_string($put_vars['doc_type'] ?? '');
        $status = $conn->real_escape_string($put_vars['status'] ?? '');

        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE e_doc SET title=?, doc_type=?, status=? WHERE id=?");
            $stmt->bind_param("sssi", $title, $doc_type, $status, $id);

            if ($stmt->execute()) {
                $conn->query("INSERT INTO admin_activity (`module`,`activity`,`status`,`date`) 
                              VALUES ('E-Documentation','Edited document: $title','$status',NOW())");
                echo json_encode(['success' => true, 'message' => 'Document updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update document']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid document ID']);
        }
        break;

    case 'DELETE':
        parse_str(file_get_contents("php://input"), $del_vars);
        $id = intval($del_vars['id'] ?? 0);

        if ($id > 0) {
            $res = $conn->query("SELECT filename, title FROM e_doc WHERE id=$id");
            if ($res && $res->num_rows > 0) {
                $row = $res->fetch_assoc();
                $filePath = __DIR__ . "/uploads/" . $row['filename'];
                if (file_exists($filePath)) unlink($filePath);

                $conn->query("DELETE FROM e_doc WHERE id=$id");
                $conn->query("INSERT INTO admin_activity (`module`,`activity`,`status`,`date`) 
                              VALUES ('E-Documentation','Deleted document: ".$row['title']."','Deleted',NOW())");

                echo json_encode(['success' => true, 'message' => 'Document deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Document not found']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid document ID']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
