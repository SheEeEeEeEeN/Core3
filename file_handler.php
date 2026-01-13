<?php
// file_handler.php
// CENTRALIZED UPLOAD FUNCTION
// Ito ang "Gatekeeper". Lahat ng upload dadaan dito.

function uploadDocument($conn, $tracking_num, $doc_type, $fileArray, $uploaderName) {
    
    // 1. Setup Folder
    $targetDir = "uploads/"; 
    
    // Auto-create folder kung wala pa
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $allowed = array('jpg', 'jpeg', 'png', 'pdf', 'docx');
    
    // 2. Get File Details
    $fileName = $fileArray['name'];
    $fileTmp = $fileArray['tmp_name'];
    $fileSize = $fileArray['size'];
    $fileError = $fileArray['error'];
    
    $fileExt = explode('.', $fileName);
    $fileActualExt = strtolower(end($fileExt));
    
    // 3. Validation
    if (!in_array($fileActualExt, $allowed)) {
        return ["status" => "warning", "message" => "Invalid file type! (JPG, PNG, PDF, DOCX only)"];
    }
    
    if ($fileError !== 0) {
        return ["status" => "danger", "message" => "File upload error occurred."];
    }
    
    if ($fileSize > 5000000) { // 5MB Limit
        return ["status" => "warning", "message" => "File is too big! Max 5MB."];
    }

    // 4. Central Validation: Check if Tracking # Exists in YOUR Shipments Table
    // Ito ang assurance na connected sa system niyo
    $check = $conn->query("SELECT id FROM shipments WHERE id = '$tracking_num'");
    if ($check->num_rows == 0) {
        return ["status" => "danger", "message" => "Error: Tracking #$tracking_num does not exist in the Core System!"];
    }

    // 5. Rename & Move File
    // Format: TRACKING_DOCTYPE_TIMESTAMP.ext
    $cleanDocType = str_replace(' ', '', $doc_type); // Remove spaces
    $fileNameNew = $tracking_num . "_" . $cleanDocType . "_" . time() . "." . $fileActualExt;
    $destination = $targetDir . $fileNameNew;

    if (move_uploaded_file($fileTmp, $destination)) {
        // 6. Insert to Central Database
        $sql = "INSERT INTO shipment_documents (tracking_number, doc_type, file_name, file_path, uploaded_by) 
                VALUES ('$tracking_num', '$doc_type', '$fileNameNew', '$destination', '$uploaderName')";
        
        if ($conn->query($sql)) {
            return ["status" => "success", "message" => "Document uploaded successfully to Central Repository!"];
        } else {
            return ["status" => "danger", "message" => "Database Error: " . $conn->error];
        }
    } else {
        return ["status" => "danger", "message" => "Failed to move file to server folder."];
    }
}
?>