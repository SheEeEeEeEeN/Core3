<?php
include("connection.php");

$username = $password = "";
$usernameErr = $passwordErr = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["username"])) {
        $usernameErr = "Username is required";
    } else {
        $username = $_POST["username"];
    }
    if (empty($_POST["password"])) {
        $passwordErr = "Password is required";
    } else {
        $password = $_POST["password"];
    }

    if ($password && $username) {
        include("connection.php");
        $check_username_query = "SELECT * FROM accounts WHERE username = '$username'";
        $check_username_result = mysqli_query($conn, $check_username_query);

        if ($check_username_result) {
            $check_username_row = mysqli_num_rows($check_username_result);
            if ($check_username_row > 0) {
                while ($row = mysqli_fetch_assoc($check_username_result)) {
                    $db_pass = $row["password"];
                    $db_role = $row["role"];
                    if ($db_pass == $password) {
                        if ($db_role == "admin") {
                            header("Location: admin.php");
                            exit();
                        } else {
                            header("Location: user.php");
                            exit();
                        }
                    } else {
                        $passwordErr = "Password incorrect";
                    }
                }
            } else {
                $usernameErr = "Username not found";
            }
        } else {
            echo "Error: " . mysqli_error($connection);
        }
    } else {
        $usernameErr = "Your username is unregistered";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - SLATE System</title>
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

    /* Layout Components */
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

    /* Login Panel */
    .login-panel {
      width: 25rem;
      padding: 3.75rem 2.5rem;
      background: rgba(22, 33, 49, 0.95);
    }

    .login-box {
      width: 100%;
      text-align: center;
    }

    .login-box img {
      width: 6.25rem;
      height: auto;
      margin-bottom: 1.25rem;
    }

    .login-box h2 {
      margin-bottom: 1.5625rem;
      color: #ffffff;
      font-size: 1.75rem;
    }

    /* Form Elements */
    .login-box form {
      display: flex;
      flex-direction: column;
      gap: 1.25rem;
    }

    .login-box input {
      width: 100%;
      padding: 0.75rem;
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 0.375rem;
      color: white;
      font-size: 1rem;
      transition: all 0.3s ease;
    }

    .login-box input:focus {
      outline: none;
      border-color: #00c6ff;
      box-shadow: 0 0 0 0.125rem rgba(0, 198, 255, 0.2);
    }

    .login-box input::placeholder {
      color: rgba(160, 160, 160, 0.8);
    }

    .login-box button {
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

    .login-box button:hover {
      background: linear-gradient(to right, #0052cc, #009ee3);
      transform: translateY(-0.125rem);
      box-shadow: 0 0.3125rem 0.9375rem rgba(0, 0, 0, 0.2);
    }

    /* Footer */
    footer {
      text-align: center;
      padding: 1.25rem;
      background: rgba(0, 0, 0, 0.2);
      color: rgba(255, 255, 255, 0.7);
      font-size: 0.875rem;
    }

    /* Responsive Design */
    @media (max-width: 48rem) {
      .login-container {
        flex-direction: column;
      }

      .welcome-panel, 
      .login-panel {
        width: 100%;
      }

      .welcome-panel {
        padding: 1.875rem 1.25rem;
      }

      .welcome-panel h1 {
        font-size: 1.75rem;
      }

      .login-panel {
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

      .login-box h2 {
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

      <div class="login-panel">
        <div class="login-box">
          <img src="rem.png" alt="SLATE Logo">
          <h2>SLATE Login</h2>
          <form action="login.php" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Log In</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <footer>
    &copy; <span id="currentYear"></span> SLATE Freight Management System. All rights reserved.
  </footer>

  <script>
    // Add current year to footer
    document.getElementById('currentYear').textContent = new Date().getFullYear();
  </script>
</body>
</html>