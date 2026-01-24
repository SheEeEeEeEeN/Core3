<?php
session_start();
include 'connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

// 🔐 Function: Get Client IP
function get_client_ip()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
    else return $_SERVER['REMOTE_ADDR'];
}

// ------------------
// Exponential lock settings
// ------------------
$baseLock  = 30;
$maxLock   = 3600;
$resetTime = 3600;

// ------------------
// 🛡️ SESSION SELF-REPAIR (Fixes all Array/Int errors)
// ------------------
$sessionVars = ['login_attempts', 'last_attempt_time', 'lockout_count'];
foreach ($sessionVars as $var) {
    // If variable doesn't exist OR is corrupted (is an array), reset it to 0
    if (!isset($_SESSION[$var]) || is_array($_SESSION[$var])) {
        $_SESSION[$var] = 0;
    }
}

// Check if lockout time has passed
if ($_SESSION['last_attempt_time'] > 0) {
    if ((time() - $_SESSION['last_attempt_time']) > $resetTime) {
        $_SESSION['lockout_count'] = 0;
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = 0;
    }
}

$isLocked = false;
$remaining = 0;
if ($_SESSION['lockout_count'] > 0 && $_SESSION['last_attempt_time'] > 0) {
    $lockDuration = min($baseLock * pow(2, $_SESSION['lockout_count'] - 1), $maxLock);
    $elapsed = time() - $_SESSION['last_attempt_time'];
    if ($elapsed < $lockDuration) {
        $remaining = (int)($lockDuration - $elapsed);
        $isLocked = true;
    }
}

// ------------------
// AJAX HANDLER
// ------------------
if (isset($_GET['check'])) {
    $field = $_GET['check'];
    $value = trim($_GET['value']);
    $response = '';

    if ($field === 'username') {
        $stmt = $conn->prepare("SELECT id FROM accounts WHERE username=?");
        $stmt->bind_param("s", $value);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $response = 'Username already taken!';
        $stmt->close();
    }

    if ($field === 'email') {
        $stmt = $conn->prepare("SELECT id FROM accounts WHERE email=?");
        $stmt->bind_param("s", $value);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $response = 'Email already registered!';
        $stmt->close();
    }

    echo $response;
    exit();
}

// ------------------
// LOGIN LOGIC
// ------------------
if (isset($_POST['login']) && !$isLocked) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $login_stmt = $conn->prepare("SELECT id, username, password, role, email FROM accounts WHERE BINARY username=?");
    $login_stmt->bind_param("s", $username);
    $login_stmt->execute();
    $login_stmt->bind_result($id, $db_username, $db_password, $role, $email);
    $login_stmt->fetch();
    $login_stmt->close();

    if ($id && password_verify($password, $db_password)) {
        // Successful Login
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = 0;
        $_SESSION['lockout_count'] = 0;
        session_regenerate_id(true);

        $_SESSION['user_id']     = $id;
        $_SESSION['account_id'] = $id;
        $_SESSION['username']   = $db_username;
        $_SESSION['role']       = $role;
        $_SESSION['email']      = $email ?? '';

        $ip = get_client_ip();
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $login_time = date('Y-m-d H:i:s');
        $log_stmt = $conn->prepare("INSERT INTO activity_log (user_id, username, ip_address, user_agent, login_time) VALUES (?, ?, ?, ?, ?)");
        $log_stmt->bind_param("issss", $id, $db_username, $ip, $user_agent, $login_time);
        $log_stmt->execute();
        $log_stmt->close();

        header("Location: request_otp.php");
        exit;
    } else {
        // Failed Login
        
        // Safety check: ensure login_attempts is an integer before incrementing
        if (is_array($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
        
        $_SESSION['login_attempts']++;
        
        if ($_SESSION['login_attempts'] >= 3) {
            
            // Safety check for lockout_count
            if (is_array($_SESSION['lockout_count'])) $_SESSION['lockout_count'] = 0;

            $_SESSION['lockout_count']++;
            $_SESSION['last_attempt_time'] = time();
            $_SESSION['login_attempts'] = 0;
            
            $lockDuration = min($baseLock * pow(2, $_SESSION['lockout_count'] - 1), $maxLock);
            $remaining = (int)$lockDuration;
            $error = "Too many failed attempts. Try again in {$remaining} seconds.";
            $isLocked = true;
        } else {
            $error = $id ? "Invalid password!" : "No account found with that username.";
        }
    }
}

// ------------------
// FORGOT PASSWORD
// ------------------
if (isset($_POST['forgot_pass'])) {
    $f_email = trim($_POST['forgot_email']);
    
    $stmt = $conn->prepare("SELECT id, username FROM accounts WHERE email = ?");
    $stmt->bind_param("s", $f_email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($f_id, $f_username);
    $stmt->fetch();
    
    if ($stmt->num_rows > 0) {
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+30 minutes"));
        
        $ins = $conn->prepare("INSERT INTO password_reset (email, token, expiry) VALUES (?, ?, ?)");
        $ins->bind_param("sss", $f_email, $token, $expiry);
        
        if ($ins->execute()) {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $resetLink = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/login.php?reset_token=" . $token;

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'royzxcasd@gmail.com'; // CHECK CREDENTIALS
                $mail->Password   = 'wgdfjpgdphkdziab';    // CHECK CREDENTIALS
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('no-reply@slate-freight.com', 'SLATE System');
                $mail->addAddress($f_email, $f_username); 

                $mail->isHTML(true);                                  
                $mail->Subject = 'Reset Your Password - SLATE Freight Management';
                $mail->Body    = "
                    <h3>Password Reset Request</h3>
                    <p>Hi <b>$f_username</b>,</p>
                    <p>We received a request to reset your password. Click the link below:</p>
                    <p><a href='$resetLink' style='background-color: #0072ff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Reset Password</a></p>
                    <p>Or copy this link: <br> $resetLink</p>
                    <p><small>This link will expire in 30 minutes.</small></p>
                ";
                $mail->send();
                $alertTitle = "Email Sent!";
                $alertMessage = "If this email is registered, we have sent a password reset link.";
                
            } catch (Exception $e) {
                $forgot_error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $forgot_error = "Database error. Please try again.";
        }
        $ins->close();
    } else {
        $forgot_error = "Email not found in our records.";
    }
    $stmt->close();
}

// ------------------
// RESET PROCESS
// ------------------
$showResetForm = false;
$token_error = "";
$token_email = "";

if (isset($_GET['reset_token'])) {
    $token = $_GET['reset_token'];
    $now = date("Y-m-d H:i:s");
    
    $stmt = $conn->prepare("SELECT email FROM password_reset WHERE token = ? AND expiry > ?");
    $stmt->bind_param("ss", $token, $now);
    $stmt->execute();
    $stmt->bind_result($token_email);
    $stmt->fetch();
    $stmt->close();
    
    if ($token_email) {
        $showResetForm = true;
    } else {
        $alertTitle = "Invalid Link";
        $alertMessage = "This password reset link is invalid or has expired.";
        $alertRedirect = "login.php";
    }
}

if (isset($_POST['reset_password_submit'])) {
    $new_pass = $_POST['new_password'];
    $conf_pass = $_POST['confirm_new_password'];
    $r_token = $_POST['token'];
    $r_email = $_POST['email']; 

    if ($new_pass !== $conf_pass) {
        $reset_error = "Passwords do not match.";
        $showResetForm = true; 
    } else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $upd = $conn->prepare("UPDATE accounts SET password = ? WHERE email = ?");
        $upd->bind_param("ss", $hashed, $r_email);
        
        if ($upd->execute()) {
            $del = $conn->prepare("DELETE FROM password_reset WHERE email = ?");
            $del->bind_param("s", $r_email);
            $del->execute();
            $register_success = "Password successfully reset! You can now login."; 
        } else {
            $reset_error = "Failed to update password.";
        }
        $upd->close();
    }
}

// ------------------
// REGISTRATION
// ------------------
$showRegister = false;

if (isset($_POST['register'])) {
    $showRegister = true;
    $reg_username = trim($_POST['reg_username']);
    $reg_password = $_POST['reg_password'];
    $reg_confirm_password = $_POST['reg_confirm_password'];
    $reg_email = trim($_POST['reg_email']);
    $reg_phone = trim($_POST['reg_phone']);
    $reg_gender = trim($_POST['reg_gender']);
    $captcha_answer = trim($_POST['captcha_answer']);
    $captcha_code = $_SESSION['captcha_code'] ?? '';

    if ($captcha_answer != $captcha_code) {
        $register_error = "Captcha is incorrect.";
    } elseif ($reg_password !== $reg_confirm_password) {
        $register_error = "Passwords do not match.";
    } elseif (!preg_match('/^(?=.*[A-Z])[A-Za-z0-9]+$/', $reg_username)) {
        $register_error = "Username must contain at least one capital letter and only alphanumeric characters.";
    } else {
        $check_stmt = $conn->prepare("SELECT id FROM accounts WHERE username = ? OR email = ?");
        $check_stmt->bind_param("ss", $reg_username, $reg_email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $register_error = "Username or Email already exists.";
        } else {
            $hashed_password = password_hash($reg_password, PASSWORD_DEFAULT);
            $role = "user";
            $insert_stmt = $conn->prepare("INSERT INTO accounts (username, password, role, email, phone_number, gender) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("ssssss", $reg_username, $hashed_password, $role, $reg_email, $reg_phone, $reg_gender);

            if ($insert_stmt->execute()) {
                $register_success = "Registration successful. You can now login.";
                unset($_SESSION['captcha_sum']);
            } else {
                $register_error = "Error in registration: " . $conn->error;
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}

// ------------------
// CAPTCHA
// ------------------
$num1 = rand(1, 9);
$num2 = rand(1, 9);
$_SESSION['captcha_sum'] = $num1 + $num2;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>SLATE System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; min-height: 100vh; display: flex; flex-direction: column; background: linear-gradient(135deg, #0f2027, #203a43, #2c5364); color: white; line-height: 1.6; }
        .main-container { flex: 1; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        .login-container { width: 100%; max-width: 75rem; display: flex; background: rgba(31, 42, 56, 0.8); border-radius: .75rem; overflow: hidden; box-shadow: 0 .625rem 1.875rem rgba(0, 0, 0, .3); }
        .welcome-panel { flex: 1; display: flex; align-items: center; justify-content: center; padding: 2.5rem; background: linear-gradient(135deg, rgba(0, 114, 255, .2), rgba(0, 198, 255, .2)); }
        .welcome-panel h1 { font-size: 2.25rem; font-weight: 700; color: #fff; text-shadow: .125rem .125rem .5rem rgba(0, 0, 0, .6); text-align: center; }
        .form-panel { width: 25rem; padding: 3.75rem 2.5rem; background: rgba(22, 33, 49, .95); }
        .form-box { width: 100%; margin-bottom: 1rem; text-align: center; }
        img { width: 150px; height: auto; }
        .form-box h2 { margin-bottom: 1.5625rem; color: #fff; font-size: 1.75rem; }
        .login-form input, .register-form input { width: 100%; padding: .75rem; margin-bottom: .5rem; border: 1px solid rgba(255, 255, 255, .1); border-radius: .375rem; background: rgba(255, 255, 255, .1); color: #fff; font-size: 1rem; transition: .3s; }
        .login-form input:focus, .register-form input:focus { outline: none; border-color: #00c6ff; box-shadow: 0 0 0 .125rem rgba(0, 198, 255, .2); }
        .login-form button, .register-form button { width: 100%; padding: .75rem; background: linear-gradient(to right, #0072ff, #00c6ff); border: none; border-radius: .375rem; font-weight: 600; font-size: 1rem; color: #fff; cursor: pointer; transition: .3s; margin-top: .5rem; }
        .login-form button:hover, .register-form button:hover { background: linear-gradient(to right, #0052cc, #009ee3); transform: translateY(-.125rem); box-shadow: 0 .3125rem .9375rem rgba(0, 0, 0, .2); }
        .login-form button:disabled { cursor: not-allowed !important; opacity: 0.7; }
        .inline-message { font-size: .9rem; margin-top: -.25rem; margin-bottom: .5rem; }
        .inline-message.error { color: #ff6b6b; }
        .inline-message.success { color: #4caf50; }
        .switch-link { margin-top: 1rem; font-size: .9rem; color: #f1f1f1; }
        .switch-link a { color: #007bff !important; cursor: pointer; text-decoration: none; font-weight: 600; transition: color 0.3s ease; }
        .switch-link a:hover { text-decoration: underline; color: #0056b3 !important; }
        footer { text-align: center; padding: 1.25rem; background: rgba(0, 0, 0, .2); color: rgba(255, 255, 255, .7); font-size: .875rem; }
        .forgot-link { display: block; text-align: right; font-size: 0.85rem; margin-bottom: 10px; margin-top: -5px; }
        .forgot-link a { color: #bbb; text-decoration: none; }
        .forgot-link a:hover { color: #fff; text-decoration: underline; }
        @media (max-width:48rem) { .login-container { flex-direction: column; } .welcome-panel, .form-panel { width: 100%; } }
        @media (max-width:30rem) { .main-container { padding: 1rem; } }
    </style>
</head>

<body>
    <div class="main-container">
        <div class="login-container">
            <div class="welcome-panel">
                <h1>FREIGHT MANAGEMENT SYSTEM</h1>
            </div>
            <div class="form-panel">
                <div class="form-box">
                    <img src="Remorig.png" alt="SLATE Logo">
                    <h2 id="formTitle">SLATE Login</h2>

                    <form id="loginForm" class="login-form" method="POST" action="login.php">
                        <input type="text" name="username" placeholder="Username" required <?php if ($isLocked) echo 'disabled'; ?>>
                        <input type="password" name="password" placeholder="Password" required <?php if ($isLocked) echo 'disabled'; ?>>
                        <div class="forgot-link">
                            <a href="#" onclick="showForgot()">Forgot Password?</a>
                        </div>
                        <button type="submit" name="login" id="loginBtn" <?php if ($isLocked) echo 'disabled'; ?>>Log In</button>
                        <?php if ($isLocked || (!empty($error) && isset($_POST['login']))): ?>
                            <?php $lockText = !empty($error) ? $error : 'Too many failed attempts.'; ?>
                            <div class="inline-message error" id="lockoutMessage"><?= htmlentities($lockText) ?></div>
                            <?php if ($isLocked || (strpos($error, 'Too many failed attempts') !== false)): ?>
                                <script>
                                    let countdown = <?= (int)$remaining ?>;
                                    const lockoutMsg = document.getElementById("lockoutMessage");
                                    const loginBtn = document.getElementById("loginBtn");
                                    const inputs = document.querySelectorAll("#loginForm input");
                                    loginBtn.disabled = true;
                                    inputs.forEach(i => i.disabled = true);
                                    function updateCountdown() {
                                        if (countdown > 0) {
                                            lockoutMsg.textContent = "Too many failed attempts. Wait " + countdown + "s.";
                                            countdown--;
                                            setTimeout(updateCountdown, 1000);
                                        } else {
                                            lockoutMsg.textContent = "";
                                            loginBtn.disabled = false;
                                            inputs.forEach(i => i.disabled = false);
                                        }
                                    }
                                    updateCountdown();
                                </script>
                            <?php endif; ?>
                        <?php endif; ?>
                    </form>

                    <form id="registerForm" class="register-form" method="POST" action="login.php" style="display:none;">
                        <input type="text" name="reg_username" placeholder="Username" required oninput="validateUsername(this.value)">
                        <div id="usernameMessage" class="inline-message error"></div>
                        <input type="email" name="reg_email" placeholder="Email" required oninput="checkAvailability('email', this.value)">
                        <div id="emailMessage" class="inline-message error"></div>
                        <input type="text" name="reg_phone" placeholder="Phone Number" required pattern="[0-9]{10,15}">
                        <select name="reg_gender" required style="width:100%; padding:.75rem; margin-bottom:.5rem; border:1px solid rgba(255,255,255,.1); border-radius:.375rem; background:rgba(239, 237, 237, 0.1); color:gray;">
                            <option value="">Select Gender</option>
                            <option value="Male" style="color: black;">Male</option>
                            <option value="Female" style="color: black;">Female</option>
                            <option value="Other" style="color: black;">Other</option>
                        </select>
                        <input type="password" name="reg_password" placeholder="Password" required>
                        <input type="password" name="reg_confirm_password" placeholder="Confirm Password" required>
                        <div id="matchMessage" class="inline-message"></div>
                        <img src="captcha_image.php" alt="captcha"><br>
                        <input type="text" name="captcha_answer" placeholder="Enter Correct Code" required>
                        <div class="form-check d-flex align-items-center my-2">
                            <input class="form-check-input me-2" type="checkbox" id="agreeLogin" required style="width:14px; height:14px; margin-top:0;">
                            <label class="form-check-label" for="agreeLogin" style="font-size:14px; color:#fff; margin:0;">
                                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal" style="color:#00c6ff;">Terms & Conditions</a>
                            </label>
                        </div>
                        <button type="submit" name="register">Register</button>
                        <?php if (!empty($register_error) && isset($_POST['register'])): ?>
                            <div class="inline-message error"><?= htmlentities($register_error) ?></div>
                        <?php endif; ?>
                    </form>

                    <form id="forgotForm" class="login-form" method="POST" action="login.php" style="display:none;">
                        <p style="font-size: 0.9rem; color: #ddd; margin-bottom: 15px;">Enter your email to reset your password.</p>
                        <input type="email" name="forgot_email" placeholder="Enter your email" required>
                        <button type="submit" name="forgot_pass">Send Reset Link</button>
                        <div class="switch-link"><a onclick="showLogin()">Back to Login</a></div>
                        <?php if (!empty($forgot_error) && isset($_POST['forgot_pass'])): ?>
                            <div class="inline-message error"><?= htmlentities($forgot_error) ?></div>
                        <?php endif; ?>
                    </form>

                    <form id="resetForm" class="login-form" method="POST" action="login.php" style="display:none;">
                        <p style="font-size: 0.9rem; color: #ddd; margin-bottom: 15px;">Enter your new password.</p>
                        <input type="hidden" name="token" value="<?= isset($_GET['reset_token']) ? htmlentities($_GET['reset_token']) : '' ?>">
                        <input type="hidden" name="email" value="<?= isset($token_email) ? htmlentities($token_email) : '' ?>">
                        <input type="password" name="new_password" placeholder="New Password" required>
                        <input type="password" name="confirm_new_password" placeholder="Confirm New Password" required>
                        <button type="submit" name="reset_password_submit">Change Password</button>
                        <?php if (!empty($reset_error) && isset($_POST['reset_password_submit'])): ?>
                            <div class="inline-message error"><?= htmlentities($reset_error) ?></div>
                        <?php endif; ?>
                    </form>

                    <div class="switch-link" id="switchToRegister">Don’t have an account? <a onclick="showRegister()">Register</a></div>
                    <div class="switch-link" id="switchToLogin" style="display:none;">Already have an account? <a onclick="showLogin()">Login</a></div>
                    <div class="switch-link" id="backToLoginFromForgot" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content text-white rounded-3" style="background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);border-radius:10px;">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Terms & Conditions</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="max-height:400px; overflow-y:auto;">
                    <p>Terms content here...</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <footer>&copy; <span id="currentYear"></span> SLATE Freight Management System. All rights reserved.</footer>

    <script>
        document.getElementById('currentYear').textContent = new Date().getFullYear();

        function showRegister() {
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('registerForm').style.display = 'block';
            document.getElementById('forgotForm').style.display = 'none';
            document.getElementById('resetForm').style.display = 'none';
            document.getElementById('formTitle').textContent = 'SLATE Register';
            document.getElementById('switchToRegister').style.display = 'none';
            document.getElementById('switchToLogin').style.display = 'block';
        }

        function showLogin() {
            document.getElementById('loginForm').style.display = 'block';
            document.getElementById('registerForm').style.display = 'none';
            document.getElementById('forgotForm').style.display = 'none';
            document.getElementById('resetForm').style.display = 'none';
            document.getElementById('formTitle').textContent = 'SLATE Login';
            document.getElementById('switchToRegister').style.display = 'block';
            document.getElementById('switchToLogin').style.display = 'none';
        }

        function showForgot() {
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('registerForm').style.display = 'none';
            document.getElementById('forgotForm').style.display = 'block';
            document.getElementById('resetForm').style.display = 'none';
            document.getElementById('formTitle').textContent = 'Reset Password';
            document.getElementById('switchToRegister').style.display = 'none';
            document.getElementById('switchToLogin').style.display = 'none';
        }

        function showReset() {
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('registerForm').style.display = 'none';
            document.getElementById('forgotForm').style.display = 'none';
            document.getElementById('resetForm').style.display = 'block';
            document.getElementById('formTitle').textContent = 'New Password';
            document.getElementById('switchToRegister').style.display = 'none';
            document.getElementById('switchToLogin').style.display = 'none';
        }

        <?php if ($showRegister): ?> showRegister(); <?php endif; ?>
        <?php if (isset($_POST['forgot_pass'])): ?> showForgot(); <?php endif; ?>
        <?php if ($showResetForm): ?> showReset(); <?php endif; ?>

        const passwordInput = document.querySelector('#registerForm input[name="reg_password"]');
        const confirmInput = document.querySelector('#registerForm input[name="reg_confirm_password"]');
        if (confirmInput) {
            confirmInput.addEventListener('input', () => {
                let messageBox = document.getElementById('matchMessage');
                if (confirmInput.value === "") { messageBox.textContent = ""; return; }
                if (passwordInput.value !== confirmInput.value) {
                    messageBox.textContent = "Passwords do not match!"; messageBox.style.color = "#ff6b6b";
                } else {
                    messageBox.textContent = "Passwords match!"; messageBox.style.color = "#4caf50";
                }
            });
        }
        
        // --- ADDED MISSING FUNCTIONS HERE ---
        function validateUsername(value) {
            const msg = document.getElementById('usernameMessage');
            if (value.length > 0) {
                checkAvailability('username', value);
            } else {
                msg.textContent = "";
            }
        }

        function checkAvailability(field, value) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'login.php?check=' + field + '&value=' + encodeURIComponent(value), true);
            xhr.onload = function() {
                if (this.status === 200) {
                    const response = this.responseText;
                    const msgElement = document.getElementById(field + 'Message');
                    msgElement.textContent = response;
                    msgElement.className = response ? "inline-message error" : "inline-message";
                }
            };
            xhr.send();
        }
        // --- END ADDED FUNCTIONS ---
    </script>

    <?php if (!empty($alertMessage)): ?>
        <script>
            Swal.fire({
                icon: '<?= (isset($forgot_success) || isset($register_success)) ? "success" : "info" ?>',
                title: '<?= addslashes($alertTitle) ?>',
                text: '<?= addslashes($alertMessage) ?>',
                <?php if(isset($alertRedirect)): ?>
                willClose: () => { window.location.href = "<?= $alertRedirect ?>"; }
                <?php endif; ?>
            });
        </script>
    <?php endif; ?>
    
    <?php if ((!empty($register_error) && isset($_POST['register'])) || (!empty($error) && isset($_POST['login']))): ?>
        <script>
            Swal.fire({ icon: 'error', title: 'Action Failed', text: '<?= addslashes($register_error ?? $error) ?>' });
        </script>
    <?php endif; ?>

</body>
</html>