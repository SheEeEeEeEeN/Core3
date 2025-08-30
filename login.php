<?php
session_start();
include("connection.php");

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT id, username, password, role FROM accounts WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $db_username, $db_password, $role);
            $stmt->fetch();

            if (password_verify($password, $db_password)) {
                // Set session
                session_regenerate_id(true);
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $db_username;
                $_SESSION['role'] = $role;

                // Redirect based on role
                if ($role === 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: user.php");
                }
                exit();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "No account found with that username.";
        }

        $stmt->close();

    } elseif (isset($_POST['register'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm = $_POST['confirm_password'];

        if ($password !== $confirm) {
            $error = "Passwords do not match!";
        } else {
            $check = $conn->prepare("SELECT id FROM accounts WHERE username = ? OR email = ?");
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
                    $success = "Account created successfully! You can now login.";
                } else {
                    $error = "Error: " . $stmt->error;
                }

                $stmt->close();
            }

            $check->close();
        }
    }
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SLATE System</title>
  <style>
    /* Base Styles */
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

    /* Layout */
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
      border-radius: 0.75rem;
      overflow: hidden;
      box-shadow: 0 0.625rem 1.875rem rgba(0, 0, 0, 0.3);
    }

    /* Welcome Panel */
    .welcome-panel {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2.5rem;
      background: linear-gradient(135deg, rgba(0, 114, 255, 0.2), rgba(0, 198, 255, 0.2));
    }

    .welcome-panel h1 {
      font-size: 2.25rem;
      font-weight: 700;
      color: #ffffff;
      text-shadow: 0.125rem 0.125rem 0.5rem rgba(0, 0, 0, 0.6);
      text-align: center;
    }

    /* Form Panel */
    .form-panel {
      width: 25rem;
      padding: 3.75rem 2.5rem;
      background: rgba(22, 33, 49, 0.95);
    }

    .form-box {
      width: 100%;
      text-align: center;
    }

    .form-box img {
      width: 6.25rem;
      height: auto;
      margin-bottom: 1.25rem;
    }

    .form-box h2 {
      margin-bottom: 1.5625rem;
      color: #ffffff;
      font-size: 1.75rem;
    }

    /* Form Elements */
    .form-box form {
      display: flex;
      flex-direction: column;
      gap: 1.25rem;
    }

    .form-box input {
      width: 100%;
      padding: 0.75rem;
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 0.375rem;
      color: white;
      font-size: 1rem;
      transition: all 0.3s ease;
    }

    .form-box input:focus {
      outline: none;
      border-color: #00c6ff;
      box-shadow: 0 0 0 0.125rem rgba(0, 198, 255, 0.2);
    }

    .form-box input::placeholder {
      color: rgba(160, 160, 160, 0.8);
    }

    .form-box button {
      padding: 0.75rem;
      background: linear-gradient(to right, #0072ff, #00c6ff);
      border: none;
      border-radius: 0.375rem;
      font-weight: 600;
      font-size: 1rem;
      color: white;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .form-box button:hover {
      background: linear-gradient(to right, #0052cc, #009ee3);
      transform: translateY(-0.125rem);
      box-shadow: 0 0.3125rem 0.9375rem rgba(0, 0, 0, 0.2);
    }

    /* Error Message */
    .error {
      margin-top: 1rem;
      color: #ff6b6b;
      font-size: 0.9rem;
    }

    /* Switch Link */
    .switch-link {
      margin-top: 1rem;
      font-size: 0.9rem;
    }

    .switch-link a {
      color: #00c6ff;
      cursor: pointer;
      text-decoration: none;
    }

    .switch-link a:hover {
      text-decoration: underline;
    }

    /* Footer */
    footer {
      text-align: center;
      padding: 1.25rem;
      background: rgba(0, 0, 0, 0.2);
      color: rgba(255, 255, 255, 0.7);
      font-size: 0.875rem;
    }

    /* Responsive */
    @media (max-width: 48rem) {
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
    }

    @media (max-width: 30rem) {
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
<form id="loginForm" action="login.php" method="POST">
  <input type="text" name="username" placeholder="Username" required>
  <input type="password" name="password" placeholder="Password" required>
  <button type="submit" name="login">Log In</button>
</form>

<!-- Register Form -->
<form id="registerForm" action="login.php" method="POST" style="display:none;">
  <input type="text" name="username" placeholder="Username" required>
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="password" placeholder="Password" required>
  <input type="password" name="confirm_password" placeholder="Confirm Password" required>
  <button type="submit" name="register">Register</button>
</form>

          <!-- Error Message -->
          <?php if (!empty($error)): ?>
            <div class="error"><?= $error; ?></div>
          <?php endif; ?>

          <!-- Switch Links -->
          <div class="switch-link" id="switchToRegister">
            Don’t have an account? <a onclick="showRegister()">Register</a>
          </div>
          <div class="switch-link" id="switchToLogin" style="display:none;">
            Already have an account? <a onclick="showLogin()">Login</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <footer>
    &copy; <span id="currentYear"></span> SLATE Freight Management System. All rights reserved.
  </footer>

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
  </script>
</body>
</html>
