<?php
// client_view_contract.php
include("connection.php");
include("session.php"); // Sisiguraduhin na naka-login si user

// 1. Kunin ang User ID ng naka-login
$email = $_SESSION['email'];
$uQ = mysqli_query($conn, "SELECT id FROM accounts WHERE email='$email'");
$user = mysqli_fetch_assoc($uQ);
$user_id = $user['id'];

// 2. Hanapin ang ACTIVE Contract ng user na ito
$cQ = mysqli_query($conn, "SELECT id FROM contracts WHERE user_id='$user_id' AND status='Active' LIMIT 1");

if(mysqli_num_rows($cQ) > 0) {
    // 3. Kung meron, REDIRECT sa Print Page
    $row = mysqli_fetch_assoc($cQ);
    $contract_id = $row['id'];
    
    // Dadalhin siya sa layout na ginawa natin
    header("Location: contract_print.php?id=" . $contract_id);
    exit;
} else {
    // 4. Kung wala pang contract
    echo "<script>
            alert('You do not have an active service contract yet. Please contact Admin.');
            window.location.href = 'bookshipment.php';
          </script>";
}
?>