<?php
session_start();
include("connection.php");

$error = "";
$success = "";
$showRegister = false; // for staying in register form

// Variables for showing the custom modal after HTML loads
$alertTitle = "";
$alertMessage = "";
$alertRedirect = "";

// ✅ Initialize login attempts & lockout tracking
if (!isset($_SESSION['login_attempts'])) {
  $_SESSION['login_attempts'] = 0;
}
if (!isset($_SESSION['lockout_time'])) {
  $_SESSION['lockout_time'] = 0;
}
if (!isset($_SESSION['lockout_count'])) {
  $_SESSION['lockout_count'] = 0; // how many times user got locked
}

// Handle AJAX requests for live check
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

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST['login'])) {
    // Check lockout
    if ($_SESSION['lockout_time'] > time()) {
      $remaining = $_SESSION['lockout_time'] - time();
      $error = "Too many failed attempts. Please wait <span id='countdown'>{$remaining}</span> seconds.";
    } else {
      $username = trim($_POST['username']);
      $password = $_POST['password'];

      $stmt = $conn->prepare("SELECT id, username, password, role FROM accounts WHERE username=?");
      $stmt->bind_param("s", $username);
      $stmt->execute();
      $stmt->store_result();

      if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $db_username, $db_password, $role);
        $stmt->fetch();

        if (password_verify($password, $db_password)) {
          // ✅ Reset everything after successful login
          $_SESSION['login_attempts'] = 0;
          $_SESSION['lockout_time'] = 0;
          $_SESSION['lockout_count'] = 0;

          session_regenerate_id(true);
          $_SESSION['user_id'] = $id;
          $_SESSION['account_id'] = $id;
          $_SESSION['username'] = $db_username;
          $_SESSION['role'] = $role;

          $alertTitle = "Login Successful";
          $alertMessage = "Welcome, $db_username";
          $alertRedirect = ($role === 'admin' ? 'admin.php' : 'user.php');
        } else {
          // ❌ Wrong password
          $_SESSION['login_attempts']++;

          if ($_SESSION['login_attempts'] >= 3) {
            $_SESSION['login_attempts'] = 0; // reset counter after lockout
            $_SESSION['lockout_count']++;   // count how many lockouts

            // Progressive lockout: 30s → 60s → 120s
            $durations = [30, 60, 120];
            $index = min($_SESSION['lockout_count'] - 1, count($durations) - 1);
            $lockTime = $durations[$index];

            $_SESSION['lockout_time'] = time() + $lockTime;
            $error = "Too many failed attempts. Please wait <span id='countdown'>{$lockTime}</span> seconds.";
          } else {
            $error = "Invalid password!"; // ✅ cleaned (removed 1/3)
          }
        }
      } else {
        // ❌ No account found
        $_SESSION['login_attempts']++;
        if ($_SESSION['login_attempts'] >= 3) {
          $_SESSION['login_attempts'] = 0;
          $_SESSION['lockout_count']++;

          $durations = [30, 60, 120];
          $index = min($_SESSION['lockout_count'] - 1, count($durations) - 1);
          $lockTime = $durations[$index];

          $_SESSION['lockout_time'] = time() + $lockTime;
          $error = "Too many failed attempts. Please wait <span id='countdown'>{$lockTime}</span> seconds.";
        } else {
          $error = "No account found."; // ✅ cleaned (removed 1/3)
        }
      }
      $stmt->close();
    }
  } elseif (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Password validation
    if (!preg_match("/^(?=.*[a-z])(?=.*\d).{8,}$/", $password)) {
      $error = "Password must be at least 8 characters, include a lowercase letter and a number.";
    } elseif ($password !== $confirm) {
      $error = "Passwords do not match!";
    } else {
      // Check existing username/email
      $check = $conn->prepare("SELECT id FROM accounts WHERE username=? OR email=?");
      $check->bind_param("ss", $username, $email);
      $check->execute();
      $check->store_result();

      if ($check->num_rows > 0) {
        $error = "Username or Email already exists!";
      } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO accounts (username, email, password, role) VALUES (?, ?, ?, 'user')");
        $stmt->bind_param("sss", $username, $email, $hashedPassword);

        if ($stmt->execute()) {
          $alertTitle = "Registration Successful";
          $alertMessage = "You can now login.";
          $alertRedirect = "login.php";
          $success = "Account created successfully! You can now login.";
        } else {
          $error = "Error: " . $stmt->error;
        }
        $stmt->close();
      }
      $check->close();
    }

    if (!empty($error)) $showRegister = true; // stay on register if error
  }
}

// ✅ safely close connection at the end
if (isset($conn) && $conn instanceof mysqli) {
  $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SLATE System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', system-ui, sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
      color: white;
      line-height: 1.6;
    }

    .main-container {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }

    .login-container {
      width: 100%;
      max-width: 75rem;
      display: flex;
      background: rgba(31, 42, 56, 0.8);
      border-radius: .75rem;
      overflow: hidden;
      box-shadow: 0 .625rem 1.875rem rgba(0, 0, 0, .3);
    }

    .welcome-panel {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2.5rem;
      background: linear-gradient(135deg, rgba(0, 114, 255, .2), rgba(0, 198, 255, .2));
    }

    .welcome-panel h1 {
      font-size: 2.25rem;
      font-weight: 700;
      color: #fff;
      text-shadow: .125rem .125rem .5rem rgba(0, 0, 0, .6);
      text-align: center;
    }

    .form-panel {
      width: 25rem;
      padding: 3.75rem 2.5rem;
      background: rgba(22, 33, 49, .95);
    }

    .form-box {
      text-align: center;
      width: 100%;
    }

    .form-box img {
      width: 6.25rem;
      margin-bottom: 1.25rem;
    }

    .form-box h2 {
      margin-bottom: 1.5625rem;
      color: #fff;
      font-size: 1.75rem;
    }

    .login-form input,
    .register-form input {
      width: 100%;
      padding: .75rem;
      margin-bottom: .5rem;
      border: 1px solid rgba(255, 255, 255, .1);
      border-radius: .375rem;
      background: rgba(255, 255, 255, .1);
      color: #fff;
      font-size: 1rem;
      transition: .3s;
    }

    .login-form input:focus,
    .register-form input:focus {
      outline: none;
      border-color: #00c6ff;
      box-shadow: 0 0 0 .125rem rgba(0, 198, 255, .2);
    }

    .login-form button,
    .register-form button {
      width: 100%;
      padding: .75rem;
      background: linear-gradient(to right, #0072ff, #00c6ff);
      border: none;
      border-radius: .375rem;
      font-weight: 600;
      font-size: 1rem;
      color: #fff;
      cursor: pointer;
      transition: .3s;
      margin-top: .5rem;
    }

    .login-form button:hover,
    .register-form button:hover {
      background: linear-gradient(to right, #0052cc, #009ee3);
      transform: translateY(-.125rem);
      box-shadow: 0 .3125rem .9375rem rgba(0, 0, 0, .2);
    }

    .inline-message {
      font-size: .9rem;
      margin-top: -.25rem;
      margin-bottom: .5rem;
    }

    .inline-message.error {
      color: #ff6b6b;
    }

    .inline-message.success {
      color: #4caf50;
    }

    .switch-link {
      margin-top: 1rem;
      font-size: .9rem;
    }

    .switch-link a {
      color: #00c6ff;
      cursor: pointer;
      text-decoration: none;
    }

    .switch-link a:hover {
      text-decoration: underline;
    }

    footer {
      text-align: center;
      padding: 1.25rem;
      background: rgba(0, 0, 0, .2);
      color: rgba(255, 255, 255, .7);
      font-size: .875rem;
    }

    /* Custom alert box */
    #customAlert {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 9999;
    }

    .alert-content {
      background: #fff;
      padding: 20px 30px;
      border-radius: 10px;
      text-align: center;
      width: 320px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, .3);
      font-family: Arial, sans-serif;
    }

    .alert-content h3 {
      margin-bottom: 10px;
      color: #0072ff;
    }

    .alert-content p {
      margin-bottom: 20px;
      color: #333;
    }

    .alert-content .ok-btn {
      padding: 8px 16px;
      border: none;
      background: linear-gradient(to right, #0072ff, #00c6ff);
      color: white;
      font-weight: bold;
      border-radius: 5px;
      cursor: pointer;
    }

    .alert-content .ok-btn:hover {
      background: linear-gradient(to right, #0052cc, #009ee3);
    }

    /* Fix checkbox alignment */
    .terms-checkbox {
      display: flex;
      align-items: center;
      margin-top: .5rem;
      font-size: 14px;
      color: #fff;
      gap: 6px;
      /* space between checkbox and text */
    }

    .terms-checkbox input[type="checkbox"] {
      margin: 0;
      width: 16px;
      height: 16px;
      vertical-align: middle;
      accent-color: #00c6ff;
      /* modern browsers: custom color */
      cursor: pointer;
    }

    .terms-checkbox label {
      margin: 0;
      line-height: 1;
      /* keeps text centered with checkbox */
    }

    .terms-checkbox a {
      color: #00c6ff;
      text-decoration: none;
    }

    .terms-checkbox a:hover {
      text-decoration: underline;
    }

    .normal-swal {
      font-size: 14px;
      border-radius: 10px;
    }

    .swal-title-blue {
      color: #0072ff !important;
      /* bright blue */
      font-weight: bold;
    }


    @media (max-width:48rem) {
      .login-container {
        flex-direction: column;
      }

      .welcome-panel,
      .form-panel {
        width: 100%;
      }

      .welcome-panel {
        padding: 1.875rem 1.25rem;
      }

      .welcome-panel h1 {
        font-size: 1.75rem;
      }

      .form-panel {
        padding: 2.5rem 1.25rem;
      }

      .form-box h2 {
        font-size: 1.5rem;
      }
    }

    @media (max-width:30rem) {
      .main-container {
        padding: 1rem;
      }

      .welcome-panel h1 {
        font-size: 1.5rem;
      }

      .form-box h2 {
        font-size: 1.5rem;
      }
    }
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
          <img src="rem.png" alt="SLATE Logo">
          <h2 id="formTitle">SLATE Login</h2>

          <!-- Login Form -->
          <form id="loginForm" class="login-form" method="POST" action="login.php" onsubmit="return checkTerms()">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit" name="login" id="loginBtn"
              <?php if ($_SESSION['lockout_time'] > time()) echo "disabled style='cursor:not-allowed;opacity:0.6;'"; ?>>
              Log In
            </button>

            <!-- ✅ Terms Agreement -->
            <div class="terms-checkbox">
              <input type="checkbox" id="agreeLogin" name="terms">
              <label for="agreeLogin">
                I agree to the
                <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">
                  Terms & Conditions
                </a>
              </label>
            </div>

            <?php if (!empty($error) && isset($_POST['login'])): ?>
              <div class="inline-message error"><?= $error ?></div>
            <?php endif; ?>
          </form>


          <!-- Terms & Conditions Modal -->
          <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
              <div class="modal-content" style="background:#222; color:#fff; border-radius:10px;">
                <div class="modal-header">
                  <h5 class="modal-title" id="termsModalLabel">Terms & Conditions</h5>
                </div>
                <div class="modal-body" style="max-height:400px; overflow-y:auto; font-size:14px;">
                  <p><strong>1. Acceptance of Terms</strong></p>
                  <p>By creating an account, you agree to follow our system rules and policies.</p>

                  <p><strong>2. User Responsibilities</strong></p>
                  <p>You must provide accurate information when registering and respect the system’s usage rules.</p>

                  <p><strong>3. Data Privacy</strong></p>
                  <p>We handle your data securely and will not share it without consent, except as required by law.</p>

                  <p><strong>4. Restrictions</strong></p>
                  <p>Do not misuse the system, attempt unauthorized access, or disrupt service operations.</p>

                  <p><strong>5. Changes to Terms</strong></p>
                  <p>We may update these Terms & Conditions, and continued use means you accept those changes.</p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>



          <!-- Register Form -->
          <form id="registerForm" class="register-form" method="POST" action="login.php" style="display:none;">
            <input type="text" name="username" placeholder="Username" required oninput="checkAvailability('username', this.value)">
            <div id="usernameMessage" class="inline-message error"></div>

            <input type="email" name="email" placeholder="Email" required oninput="checkAvailability('email', this.value)">
            <div id="emailMessage" class="inline-message error"></div>

            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <div id="matchMessage" class="inline-message"></div>

            <button type="submit" name="register">Register</button>

            <?php if (!empty($error) && isset($_POST['register'])): ?>
              <div class="inline-message error"><?= htmlentities($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($success) && isset($_POST['register'])): ?>
              <div class="inline-message success"><?= htmlentities($success) ?></div>
            <?php endif; ?>
          </form>

          <div class="switch-link" id="switchToRegister">Don’t have an account? <a onclick="showRegister()">Register</a></div>
          <div class="switch-link" id="switchToLogin" style="display:none;">Already have an account? <a onclick="showLogin()">Login</a></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Custom Alert -->
  <div id="customAlert">
    <div class="alert-content">
      <h3 id="alertTitle">Notification</h3>
      <p id="alertMessage">This is a message</p>
      <button class="ok-btn" onclick="closeAlert()">OK</button>
    </div>
  </div>

  <footer>&copy; <span id="currentYear"></span> SLATE Freight Management System. All rights reserved.</footer>

  <script>
    document.getElementById('currentYear').textContent = new Date().getFullYear();

    function showRegister() {
      document.getElementById('loginForm').style.display = 'none';
      document.getElementById('registerForm').style.display = 'block';
      document.getElementById('formTitle').textContent = 'SLATE Register';
      document.getElementById('switchToRegister').style.display = 'none';
      document.getElementById('switchToLogin').style.display = 'block';
    }

    function showLogin() {
      document.getElementById('loginForm').style.display = 'block';
      document.getElementById('registerForm').style.display = 'none';
      document.getElementById('formTitle').textContent = 'SLATE Login';
      document.getElementById('switchToRegister').style.display = 'block';
      document.getElementById('switchToLogin').style.display = 'none';
    }

    // Password match check
    const passwordInput = document.querySelector('#registerForm input[name="password"]');
    const confirmInput = document.querySelector('#registerForm input[name="confirm_password"]');
    if (confirmInput) {
      confirmInput.addEventListener('input', () => {
        let messageBox = document.getElementById('matchMessage');
        if (confirmInput.value === "") {
          messageBox.textContent = "";
          return;
        }
        if (passwordInput.value !== confirmInput.value) {
          messageBox.textContent = "Passwords do not match!";
          messageBox.style.color = "#ff6b6b";
        } else {
          messageBox.textContent = "Passwords match!";
          messageBox.style.color = "#4caf50";
        }
      });
    }

    // Live check for username/email
    function checkAvailability(field, value) {
      if (value === "") {
        document.getElementById(field + 'Message').textContent = "";
        return;
      }
      fetch(`login.php?check=${field}&value=${encodeURIComponent(value)}`)
        .then(res => res.text())
        .then(data => {
          document.getElementById(field + 'Message').textContent = data;
        });
    }

    // Custom alert functions
    function showAlert(title, message) {
      document.getElementById("alertTitle").textContent = title;
      document.getElementById("alertMessage").textContent = message;
      document.getElementById("customAlert").style.display = "flex";
    }

    function closeAlert() {
      document.getElementById("customAlert").style.display = "none";
    }

    // Stay on register form if php indicated an error during registration
    <?php if ($showRegister): ?>
      showRegister();
    <?php endif; ?>

    // 🔥 Lockout countdown for button only
    const countdownEl = document.getElementById('countdown');
    const loginBtn = document.getElementById('loginBtn');
    if (countdownEl && loginBtn) {
      loginBtn.disabled = true;
      loginBtn.style.cursor = "not-allowed";
      loginBtn.style.opacity = "0.6";

      let timeLeft = parseInt(countdownEl.textContent);
      const timer = setInterval(() => {
        timeLeft--;
        countdownEl.textContent = timeLeft;
        if (timeLeft <= 0) {
          clearInterval(timer);
          loginBtn.disabled = false;
          loginBtn.style.cursor = "pointer";
          loginBtn.style.opacity = "1";
          location.reload(); // refresh to clear message
        }
      }, 1000);
    }

    // ✅ Ensure terms are checked before login
    function checkTerms() {
      const terms = document.getElementById("agreeLogin");
      if (!terms.checked) {
        alert("You must agree to the Terms & Conditions before logging in.");
        return false; // stop form submission
      }
      return true;
    }

    // ✅ Ensure terms are checked before login with SweetAlert2
    function checkTerms() {
      const terms = document.getElementById("agreeLogin");
      if (!terms.checked) {
        Swal.fire({
          title: 'Terms & Conditions',
          text: 'You must agree to the Terms & Conditions before logging in.',
          confirmButtonColor: '#0072ff',
          customClass: {
            popup: 'normal-swal',
            title: 'swal-title-blue' // 👈 add custom class for title
          }
        });
        return false; // stop form submission
      }
      return true;
    }
  </script>

  <!-- Trigger alert (if any) AFTER the showAlert function is defined -->
  <?php if (!empty($alertMessage)): ?>
    <script>
      const __alertTitle = "<?php echo addslashes($alertTitle); ?>";
      const __alertMessage = "<?php echo addslashes($alertMessage); ?>";
      const __alertRedirect = "<?php echo addslashes($alertRedirect); ?>";

      showAlert(__alertTitle, __alertMessage);
      setTimeout(() => {
        if (__alertRedirect && __alertRedirect.length > 0) {
          window.location.href = __alertRedirect;
        }
      }, 2000);
    </script>
  <?php endif; ?>
</body>

</html>