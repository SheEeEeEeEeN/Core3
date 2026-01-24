<?php
// Save as verify_otp_form.php
// It posts to verify_otp.php
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify OTP</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #2c3e50;
            font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
        }

        .card-otp {
            width: 100%;
            max-width: 420px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(16, 24, 40, 0.08);
            padding: 1.2rem;
        }

        .brand {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: inline-grid;
            place-items: center;
            background: linear-gradient(180deg, #6b21a8, #3b82f6);
            color: white;
            font-weight: 700;
            font-size: 20px;
            margin-right: 12px;
        }

        .small-muted {
            font-size: 0.9rem;
            color: #6b7280
        }
    </style>
</head>

<body>
    <main class="card card-otp bg-white">
        <div class="d-flex align-items-center mb-3">
            <div class="brand">OTP</div>
            <div>
                <h5 class="mb-0">Verify OTP</h5>
                <div class="small-muted">Enter the 6-digit code we sent to your email</div>
            </div>
        </div>

        <form action="verify_otp.php" method="post" id="otpVerifyForm" novalidate>
            <div class="mb-3">
                <label for="otp" class="form-label">One-Time Password</label>
                <input type="text" class="form-control form-control-lg text-center" id="otp" name="otp" pattern="\d{6}" placeholder="123456" required>
                <div class="invalid-feedback">Please enter a valid 6-digit OTP.</div>
            </div>

            <div class="d-grid mb-3">
                <button id="verifyBtn" type="submit" class="btn btn-success btn-lg">Verify OTP</button>
            </div>

            <div class="text-center small-muted">
                Didn’t receive the code? <a href="request_otp.php">Resend</a>
            </div>
        </form>

        <hr>
        <div class="text-center small text-muted">Need help? <a href="#">Contact support</a></div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            const form = document.getElementById('otpVerifyForm');
            const verifyBtn = document.getElementById('verifyBtn');

            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                    form.classList.add('was-validated');
                    return;
                }

                verifyBtn.disabled = true;
                verifyBtn.innerHTML = 'Verifying...';
            });
        })();
    </script>
</body>

</html>
