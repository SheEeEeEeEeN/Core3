<?php
include("connection.php");
include('session.php');
requireRole('admin');

// Validate request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedback_id = intval($_POST['feedback_id']);
    $reply_message = trim($_POST['reply_message']);
    $admin_id = $_SESSION['account_id']; // assuming session stores admin ID

    if (!empty($feedback_id) && !empty($reply_message)) {
        // Insert reply
        $stmt = $conn->prepare("INSERT INTO replies (feedback_id, admin_id, reply_message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $feedback_id, $admin_id, $reply_message);

        if ($stmt->execute()) {
            // Mark feedback as read once replied
            $update = $conn->prepare("UPDATE feedback SET status='read' WHERE id=?");
            $update->bind_param("i", $feedback_id);
            $update->execute();

            // Redirect back to notifications page
            header("Location: CRM.php?reply=success");
            exit();
        } else {
            die("Error saving reply: " . $conn->error);
        }
    } else {
        die("Invalid input.");
    }
} else {
    die("Invalid request.");
}
