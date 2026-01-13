<?php
session_start();
include 'connection.php';

$email = $_SESSION['email'] ?? '';
$username = $_SESSION['username'] ?? '';

if (!$email || !$username) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Request OTP</title>
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
            <i class="bi bi-shield-lock otp-icon"></i>
        </div>
        <h3 class="mb-3 fw-bold">OTP Verification</h3>
        <p class="small-text">For your account security, we will send a one-time password (OTP) to:</p>
        <p class="fw-semibold text-primary"><?= htmlentities($email) ?></p>

        <form method="POST" action="send_otp.php">
            <input type="hidden" name="email" value="<?= htmlentities($email) ?>">
            <button type="submit" name="send_otp" class="btn btn-primary w-100 py-2 mt-3">
                <i class="bi bi-send-check me-2"></i> Send OTP
            </button>
        </form>

        <p class="small-text mt-4">
            Didn’t receive the email? <br>
            <span class="text-muted">Please check your spam/junk folder.</span>
        </p>
    </div>

</body>

</html>