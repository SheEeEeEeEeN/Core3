<?php
// save_contract.php
header("Content-Type: application/json");

$uploadDir = "contracts/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (isset($_FILES["pdf"])) {
    $fileName = time() . "_" . basename($_FILES["pdf"]["name"]);
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES["pdf"]["tmp_name"], $targetPath)) {
        // Save metadata into DB (example)
        include("connection.php");
        $customerName = $_POST["customerName"];
        $shipmentDetails = $_POST["shipmentDetails"];

        $stmt = $conn->prepare("INSERT INTO contracts (customer_name, shipment_details, file_path, status) VALUES (?, ?, ?, 'Pending')");
        $stmt->bind_param("sss", $customerName, $shipmentDetails, $targetPath);
        $stmt->execute();
        $stmt->close();

        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "File upload failed"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "No file uploaded"]);
}
?>
