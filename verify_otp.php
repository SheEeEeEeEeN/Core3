<?php
session_start();
include 'connection.php';

$username = $_SESSION['username'] ?? '';
if (!$username) {
    header("Location: login.php");
    exit;
}

$error = '';

if (isset($_POST['verify_otp'])) {
    $inputOtp = trim($_POST['otp']);

    $stmt = $conn->prepare("SELECT otp_hash, otp_expires_at FROM accounts WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($otp_hash, $expires_at);
    $stmt->fetch();
    $stmt->close();

    if (!$otp_hash) {
        $error = "No OTP found. Request again.";
    } elseif (password_verify($inputOtp, $otp_hash)) {
        $_SESSION['is_verified'] = true;

        // Kunin yung buong details ng account
        $stmt = $conn->prepare("SELECT id, role, email FROM accounts WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($id, $role, $email);
        $stmt->fetch();
        $stmt->close();

        // Save sa session
        $_SESSION['user_id'] = $id;
        $_SESSION['role']    = $role;
        $_SESSION['email']   = $email;

        // Role-based redirect
        if ($role === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: user.php");
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .otp-card {
            max-width: 420px;
            margin: auto;
            padding: 2rem;
            border-radius: 1.2rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            background: #fff;
            color: #333;
        }

        .otp-icon {
            font-size: 3rem;
            color: #0d6efd;
        }

        .btn-primary {
            border-radius: 30px;
            font-weight: 600;
        }

        .small-text {
            font-size: 0.9rem;
            color: #555;
        }
    </style>
</head>

<body class="d-flex justify-content-center align-items-center vh-100">

    <div class="otp-card text-center">
        <div class="mb-3">
            <i class="bi bi-key-fill otp-icon"></i>
        </div>
        <h3 class="mb-3 fw-bold">Enter OTP</h3>
        <p class="small-text">Please enter the 6-digit OTP we sent to your registered email.</p>

        <form method="POST" action="verify_otp.php" class="mt-3">
            <input type="text" name="otp" maxlength="6" placeholder="6-digit OTP"
                class="form-control text-center mb-3" required>
            <button type="submit" name="verify_otp" class="btn btn-primary w-100 py-2">
                <i class="bi bi-check-circle me-2"></i> Verify OTP
            </button>
        </form>

        <?php if ($error): ?>
            <p class="text-danger mt-3"><?= htmlentities($error) ?></p>
        <?php endif; ?>

        <p class="small-text mt-4">Didn’t get the OTP? <br>
            <a href="request_otp.php" class="text-decoration-none">Request a new code</a>
        </p>
    </div>

</body>

</html>