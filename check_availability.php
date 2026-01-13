<?php
include 'connection.php';

if (isset($_GET['field']) && isset($_GET['value'])) {
    $field = $_GET['field'];
    $value = trim($_GET['value']);

    if ($field === "username") {
        $stmt = $conn->prepare("SELECT id FROM accounts WHERE username = ?");
    } elseif ($field === "email") {
        $stmt = $conn->prepare("SELECT id FROM accounts WHERE email = ?");
    } else {
        echo "";
        exit;
    }

    $stmt->bind_param("s", $value);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo ucfirst($field) . " already exists!";
    } else {
        echo ""; // available
    }

    $stmt->close();
}
