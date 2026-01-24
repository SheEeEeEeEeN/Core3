<?php
include('session.php');
include('connection.php');
include("darkmode.php");
requireRole('user');

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_pic'])) {
    $targetDir = "upload/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $fileName = time() . "_" . basename($_FILES['profile_pic']['name']);
    $targetFile = $targetDir . $fileName;

    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFile)) {
            $username = $_SESSION['username'];
            $sql = "UPDATE accounts SET profile_image = '$targetFile' WHERE username = '$username'";
            if (mysqli_query($conn, $sql)) {
                $_SESSION['profile_image'] = $targetFile;
                header("Location: user-profile.php?success=1");
                exit();
            }
        }
    }
}

// Fetch current user info
$username = $_SESSION['username'];
$query = mysqli_query($conn, "SELECT * FROM accounts WHERE username = '$username'");
$user = mysqli_fetch_assoc($query);

// Profile image fallback
$profileImage = !empty($user['profile_image']) ? $user['profile_image'] : 'user.png';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Profile - Freight System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --dark-bg: #1a1a2e;
            --dark-card: #16213e;
            --text-light: #f8f9fa;
            --text-dark: #212529;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --border-radius: 0.35rem;
            --shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background-color: var(--secondary-color);
            color: var(--text-dark);
        }

        body.dark-mode {
            --secondary-color: var(--dark-bg);
            background-color: var(--secondary-color);
            color: var(--text-light);
        }

        /* Sidebar */
    .sidebar {
      width: var(--sidebar-width);
      height: 100vh;
      position: fixed;
      left: 0;
      top: 0;
      background: #2c3e50;
      color: white;
      padding: 0;
      transition: all 0.3s ease;
      z-index: 1000;
      transform: translateX(0);
    }

    .sidebar.collapsed {
      transform: translateX(-100%);
    }

    .sidebar .logo {
      padding: 1.5rem;
      text-align: center;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar .logo img {
      max-width: 100%;
      height: auto;
    }

    .system-name {
      padding: 0.5rem 1.5rem;
      font-size: 0.9rem;
      color: rgba(255, 255, 255, 0.8);
      text-align: center;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      margin-bottom: 1rem;
    }

    .sidebar a {
      display: block;
      color: rgba(255, 255, 255, 0.8);
      padding: 0.75rem 1.5rem;
      text-decoration: none;
      border-left: 3px solid transparent;
      transition: all 0.3s;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background-color: rgba(255, 255, 255, 0.1);
      color: white;
      border-left: 3px solid white;
    }

    .admin-feature {
      background-color: rgba(0, 0, 0, 0.1);
    }

    /* Main Content */
    .content {
      margin-left: var(--sidebar-width);
      padding: 20px;
      transition: all 0.3s ease;
    }

    .content.expanded {
      margin-left: 0;
    }


        /* Header */
        .header {
            background-color: white;
            padding: 1rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .dark-mode .header {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        .hamburger {
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
        }

        .system-title {
            color: var(--primary-color);
            font-size: 1rem;
        }

        /* User Icon */
        .user_icon {
            cursor: pointer;
            padding: 0.5rem;
            display: flex;
            align-items: center;
            position: relative;
        }

        .user_icon img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .user_dropdown {
            display: none;
            position: absolute;
            top: 60px;
            right: 0;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            min-width: 150px;
            z-index: 2000;
        }

        .user_dropdown a {
            display: block;
            padding: 10px 15px;
            text-decoration: none;
            color: #333;
            font-size: 0.9rem;
        }

        .user_dropdown a:hover {
            background-color: #f0f0f0;
        }

        .dark-mode .user_dropdown {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        .dark-mode .user_dropdown a {
            color: var(--text-light);
        }

        .dark-mode .user_dropdown a:hover {
            background-color: #2a3a5a;
        }

        .profile-card {
            max-width: 700px;
            margin: auto;
            padding: 30px;
            border-radius: 15px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .dark-mode .profile-card {
            background: var(--dark-card);
        }

        .profile-card img {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            border: 4px solid #0d6efd;
            object-fit: cover;
            margin-bottom: 15px;
        }

        .profile-info {
            margin: 15px 0;
            text-align: left;
        }

        .profile-info p {
            margin: 6px 0;
        }

        /* Theme Toggle */
        .theme-toggle-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .theme-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .theme-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: var(--primary-color);
        }

        input:checked+.slider:before {
            transform: translateX(26px);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>
    <?php 
    include 'loading.html';
?>
    <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="logo">
      <img src="Remorig.png" alt="SLATE Logo">
    </div>
    <div class="system-name">CORE TRANSACTION 3</div>
        <a href="user.php">🏠 Dashboard</a>
       
        <a href="bookshipment.php">📝 Book Shipment</a>
        <a href="shiphistory.php">📜 Shipment History</a>
        <a href="feedback.php">💬 Feedback & Notification Hub</a>
  </div>

    <div class="content" id="mainContent">
        <div class="header">
            <div class="hamburger" id="hamburger">☰</div>
            <div>
                <h1 class="fw-bold">User Profile</h1>
            </div>
            <div class="theme-toggle-container">
                <div class="user_icon" id="userIcon">
                    <img src="<?php echo $profileImage; ?>" alt="User" id="profileImg">
                    <div class="user_dropdown" id="userDropdown">
                        <a href="user-profile.php">Profile</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
                <span class="theme-label">Dark Mode</span>
                <label class="theme-switch">
                    <input type="checkbox" id="userThemeToggle">
                    <span class="slider"></span>
                </label>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="profile-card">
                <img src="<?php echo $profileImage; ?>" alt="Profile Picture">
                <p class="text-muted">👤 <?php echo $user['role'] ?? 'User'; ?></p>
                <div class="profile-info">
                    <p><strong>Email:</strong> <?php echo $user['email'] ?? 'Not Set'; ?></p>
                    <p><strong>Joined:</strong> <?php echo $user['created_at'] ?? ''; ?></p>
                </div>

                <!-- Upload Form -->
                <form method="POST" enctype="multipart/form-data" class="mt-3">
                    <div class="input-group mb-3">
                        <input type="file" name="profile_pic" accept="image/*" class="form-control" required>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success mt-3">✅ Profile picture updated successfully!</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Initialize dark mode with global storage key
        initDarkMode("userThemeToggle", "userDarkMode");

        // Sidebar toggle
        document.getElementById('hamburger').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('mainContent').classList.toggle('expanded');
        });

        // Toggle dropdown
        const userIcon = document.getElementById("userIcon");
        const userDropdown = document.getElementById("userDropdown");
        userIcon.addEventListener("click", () => {
            userDropdown.style.display = userDropdown.style.display === "block" ? "none" : "block";
        });

        document.addEventListener("click", (e) => {
            if (!userIcon.contains(e.target)) {
                userDropdown.style.display = "none";
            }
        });
    </script>
</body>
</html>
