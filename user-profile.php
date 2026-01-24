<?php
include('session.php');
include('connection.php');
include("darkmode.php");
requireRole('user');

$msg = "";
$msgType = "";

// 1. FETCH CURRENT USER INFO
$current_user_email = $_SESSION['email'];
$q = mysqli_query($conn, "SELECT * FROM accounts WHERE email='$current_user_email'");
$user = mysqli_fetch_assoc($q);
$user_id = $user['id'];

// 2. HANDLE PROFILE PICTURE UPLOAD
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_pic'])) {
    $targetDir = "upload/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    
    $fileName = time() . "_" . basename($_FILES['profile_pic']['name']);
    $targetFile = $targetDir . $fileName;
    $allowedTypes = ['jpg', 'jpeg', 'png'];
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFile)) {
            $sql = "UPDATE accounts SET profile_image = '$targetFile' WHERE id = '$user_id'";
            if (mysqli_query($conn, $sql)) {
                $msg = "Profile picture updated successfully!";
                $msgType = "success";
                $user['profile_image'] = $targetFile; 
            }
        }
    } else {
        $msg = "Invalid file type. Please upload JPG or PNG.";
        $msgType = "danger";
    }
}

// 3. HANDLE INFO UPDATE
if (isset($_POST['update_info'])) {
    $new_username = mysqli_real_escape_string($conn, $_POST['username']);
    $new_phone = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $new_gender = mysqli_real_escape_string($conn, $_POST['gender']);

    $sql = "UPDATE accounts SET username='$new_username', phone_number='$new_phone', gender='$new_gender' WHERE id='$user_id'";
    
    if (mysqli_query($conn, $sql)) {
        $msg = "Account details updated.";
        $msgType = "success";
        $_SESSION['username'] = $new_username;
        // Refresh User Data
        $user['username'] = $new_username;
        $user['phone_number'] = $new_phone;
        $user['gender'] = $new_gender;
    } else {
        $msg = "Error updating details: " . mysqli_error($conn);
        $msgType = "danger";
    }
}

// 4. HANDLE PASSWORD UPDATE
if (isset($_POST['update_pass'])) {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if ($new_pass === $confirm_pass) {
        $sql = "UPDATE accounts SET password='$new_pass' WHERE id='$user_id'";
        if (mysqli_query($conn, $sql)) {
            $msg = "Password changed successfully.";
            $msgType = "success";
        }
    } else {
        $msg = "Passwords do not match.";
        $msgType = "danger";
    }
}

$profileImage = !empty($user['profile_image']) ? $user['profile_image'] : 'user.png';

// GET SHIPMENT STATS
$totalShip = 0; $activeShip = 0;
if($statQ = mysqli_query($conn, "SELECT COUNT(*) as total, SUM(CASE WHEN status IN ('Pending', 'In Transit', 'Processing') THEN 1 ELSE 0 END) as active FROM shipments WHERE user_id='$user_id'")) {
    $stats = mysqli_fetch_assoc($statQ);
    $totalShip = $stats['total'] ?? 0;
    $activeShip = $stats['active'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Freight System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        /* --- ORIGINAL DASHBOARD CSS --- */
        :root { --sidebar-width: 250px; --primary-color: #4e73df; --dark-bg: #1a1a2e; }
        body { font-family: 'Segoe UI', sans-serif; background-color: #f8f9fc; color: #212529; overflow-x: hidden; }
        
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; left: 0; top: 0; background: #2c3e50; color: white; z-index: 1040; transition: all 0.3s ease; }
        .content { margin-left: var(--sidebar-width); padding: 20px; transition: all 0.3s ease; min-height: 100vh; }
        .sidebar.collapsed { left: -250px; }
        .content.expanded { margin-left: 0; }
        
        @media (max-width: 768px) {
            .sidebar { left: -250px; }
            .content { margin-left: 0 !important; }
            .sidebar.mobile-open { left: 0; }
        }
        
        .sidebar a { color: rgba(255, 255, 255, 0.8); text-decoration: none; padding: .75rem 1.5rem; display: block; border-left: 3px solid transparent; }
        .sidebar a:hover, .sidebar a.active { background-color: rgba(255, 255, 255, 0.1); color: white; border-left: 3px solid white; }
        
        .sidebar-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0, 0, 0, 0.5); z-index: 1030; display: none; }
        .sidebar-overlay.show { display: block; }

        /* Dark Mode */
        body.dark-mode { background-color: var(--dark-bg); color: #f8f9fa; }
        body.dark-mode .sidebar { box-shadow: 2px 0 10px rgba(0,0,0,0.2); }
        body.dark-mode .header, body.dark-mode .card, body.dark-mode .modal-content { background-color: #16213e !important; color: #f8f9fa !important; border: 1px solid #2a3a5a !important; }
        body.dark-mode .form-control, body.dark-mode .form-select { background-color: #2a3a5a; border-color: #444; color: white; }
        body.dark-mode .nav-tabs .nav-link { color: #f8f9fa; }
        body.dark-mode .nav-tabs .nav-link:hover { color: white; }

        /* --- PROFILE SPECIFIC STYLES --- */
        .profile-cover { height: 150px; background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); border-radius: 10px 10px 0 0; }
        .profile-avatar-container { position: relative; margin-top: -75px; text-align: center; }
        .profile-avatar { width: 150px; height: 150px; border-radius: 50%; border: 5px solid white; object-fit: cover; background: #fff; }
        .dark-mode .profile-avatar { border-color: #16213e; }
        
        .upload-icon {
            position: absolute; bottom: 5px; right: 35%; 
            background: var(--primary-color); color: white; 
            width: 35px; height: 35px; border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; 
            cursor: pointer; border: 2px solid white; transition: 0.2s;
        }
        .upload-icon:hover { transform: scale(1.1); }

        .stat-box { background: rgba(78, 115, 223, 0.1); padding: 15px; border-radius: 10px; text-align: center; }
        .dark-mode .stat-box { background: rgba(255,255,255,0.05); }
        .stat-num { font-size: 1.5rem; font-weight: bold; color: var(--primary-color); display: block; }
    </style>
</head>

<body>
    <?php include 'loading.html'; ?>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="sidebar d-flex flex-column flex-shrink-0 p-3" id="sidebar">
        <div class="text-center mb-4 mt-2">
            <img src="Remorig.png" alt="Logo" class="img-fluid mb-2" style="max-width: 140px;">
            <h6 class="fw-semibold text-uppercase text-light-50 mb-0" style="font-size: 0.85rem;">Core Transaction 3</h6>
        </div>
        <hr class="text-light opacity-25">
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item mb-2"><a href="user.php" class="nav-link"><i class="bi bi-house-door-fill fs-5 me-2"></i>Dashboard</a></li>
            <li class="nav-item mb-2"><a href="bookshipment.php" class="nav-link"><i class="bi bi-truck fs-5 me-2"></i>Book Shipment</a></li>
            <li class="nav-item mb-2"><a href="My_shipment.php" class="nav-link"><i class="bi bi-truck fs-5 me-2"></i>My Shipments</a></li>
            <li class="nav-item mb-2"><a href="shiphistory.php" class="nav-link"><i class="bi bi-clock-history fs-5 me-2"></i>History</a></li>
            <li class="nav-item mb-2"><a href="feedback.php" class="nav-link"><i class="bi bi-chat-dots fs-5 me-2"></i>Feedback</a></li>
            <li class="nav-item mb-2"><a href="user-profile.php" class="nav-link active"><i class="bi bi-person-circle fs-5 me-2"></i>My Profile</a></li>
        </ul>
    </div>

    <div class="content" id="mainContent">
        
        <header class="header d-flex align-items-center justify-content-between px-3 py-3 mb-4 bg-white shadow-sm rounded-3 sticky-top">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light border-0 p-2" id="hamburger"><i class="bi bi-list fs-4"></i></button>
                <div><h5 class="fw-semibold mb-0">Account Settings</h5></div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="dropdown me-3">
                    <a href="#" class="text-dark position-relative" id="notifDropdown" data-bs-toggle="dropdown" onclick="markRead()">
                        <i class="bi bi-bell fs-4"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge" style="display: none;">0</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm p-0" style="width: 300px; max-height: 400px; overflow-y: auto;">
                        <li class="p-2 border-bottom fw-bold bg-light">Notifications</li>
                        <div id="notifList"><li class="text-center p-3 text-muted small">No new notifications</li></div>
                    </ul>
                </div>
                <div class="form-check form-switch mb-0 ms-2">
                    <label class="form-check-label d-none d-sm-inline" for="userThemeToggle">🌙</label>
                    <input class="form-check-input" type="checkbox" role="switch" id="userThemeToggle">
                </div>
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none text-dark dropdown-toggle" data-bs-toggle="dropdown">
                        <img src="<?php echo $profileImage; ?>" alt="Profile" class="rounded-circle" width="40" height="40" style="object-fit:cover;">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item" href="user-profile.php">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <?php if ($msg): ?>
            <div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show shadow-sm" role="alert">
                <?php echo $msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="profile-cover"></div>
                    <div class="card-body pt-0">
                        <div class="profile-avatar-container">
                            <img src="<?php echo $profileImage; ?>" class="profile-avatar shadow" alt="User">
                            <label for="fileUpload" class="upload-icon shadow" title="Change Photo">
                                <i class="bi bi-camera-fill"></i>
                            </label>
                            <form id="avatarForm" method="POST" enctype="multipart/form-data" class="d-none">
                                <input type="file" id="fileUpload" name="profile_pic" accept="image/*" onchange="document.getElementById('avatarForm').submit()">
                            </form>
                        </div>
                        
                        <div class="text-center mt-3">
                            <h4 class="fw-bold mb-0"><?php echo htmlspecialchars($user['username']); ?></h4>
                            <p class="text-muted small mb-3"><?php echo htmlspecialchars($user['email']); ?></p>
                            
                            <div class="d-flex justify-content-center gap-2">
                                <span class="badge bg-primary px-3 py-2 rounded-pill"><?php echo strtoupper($user['role']); ?></span>
                                <?php if(!empty($user['gender'])): ?>
                                    <span class="badge bg-secondary px-3 py-2 rounded-pill"><?php echo ucfirst($user['gender']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row mt-4 g-2">
                            <div class="col-6">
                                <div class="stat-box">
                                    <span class="stat-num"><?php echo $totalShip; ?></span>
                                    <small class="text-muted">Total Shipments</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-box">
                                    <span class="stat-num text-success"><?php echo $activeShip; ?></span>
                                    <small class="text-muted">Active</small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <small class="text-muted d-block mb-2 fw-bold text-uppercase">Member Info</small>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-calendar3 text-primary"></i>
                                <span>Joined: <?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-telephone text-primary"></i>
                                <span><?php echo !empty($user['phone_number']) ? $user['phone_number'] : 'No phone linked'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header bg-transparent border-bottom-0">
                        <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#edit-profile">
                                    <i class="bi bi-person-gear me-2"></i>Edit Profile
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#security">
                                    <i class="bi bi-shield-lock me-2"></i>Security
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="edit-profile">
                                <form method="POST">
                                    <h6 class="fw-bold text-primary mb-3">Basic Information</h6>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label small text-muted">Username</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small text-muted">Gender</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-gender-ambiguous"></i></span>
                                                <select name="gender" class="form-select">
                                                    <option value="" disabled>Select Gender</option>
                                                    <option value="Male" <?php echo ($user['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                                    <option value="Female" <?php echo ($user['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                                    <option value="Other" <?php echo ($user['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <h6 class="fw-bold text-primary mb-3 mt-4">Contact Details</h6>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label small text-muted">Email Address (Read-only)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bi bi-envelope-at"></i></span>
                                                <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small text-muted">Phone Number</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                                <input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($user['phone_number']); ?>" placeholder="09xxxxxxxxx">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-end mt-4">
                                        <button type="submit" name="update_info" class="btn btn-primary px-4">
                                            <i class="bi bi-save me-2"></i>Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="security">
                                <form method="POST">
                                    <div class="alert alert-info border-0 d-flex align-items-center gap-3">
                                        <i class="bi bi-info-circle-fill fs-4"></i>
                                        <div>
                                            <strong>Password Security:</strong>
                                            <ul class="mb-0 small ps-3">
                                                <li>Ensure your password is strong.</li>
                                                <li>Don't share your password with anyone.</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="mb-3 mt-4">
                                        <label class="form-label small text-muted">New Password</label>
                                        <input type="password" name="new_password" class="form-control" required placeholder="Enter new password">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small text-muted">Confirm New Password</label>
                                        <input type="password" name="confirm_password" class="form-control" required placeholder="Repeat new password">
                                    </div>

                                    <div class="text-end mt-4">
                                        <button type="submit" name="update_pass" class="btn btn-danger px-4">
                                            <i class="bi bi-key me-2"></i>Update Password
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Init Dark Mode
        if(typeof initDarkMode === 'function') initDarkMode("userThemeToggle", "userDarkMode");

        // Sidebar Toggle
        document.getElementById('hamburger').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (window.innerWidth > 768) {
                sidebar.classList.toggle('collapsed');
                document.getElementById('mainContent').classList.toggle('expanded');
            } else {
                sidebar.classList.toggle('mobile-open');
                overlay.classList.toggle('show');
            }
        });

        document.getElementById('sidebarOverlay').addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('mobile-open');
            this.classList.remove('show');
        });

        // Notifications
        function fetchNotifications() {
            fetch('api/get_notifications.php').then(r => r.json()).then(d => {
                const b = document.getElementById('notifBadge');
                if(d.count > 0) { b.innerText = d.count; b.style.display = 'inline-block'; } 
                else { b.style.display = 'none'; }
                let h = '';
                if(d.data.length > 0) d.data.forEach(n => {
                    let bg = n.is_read == 0 ? 'bg-light' : '';
                    h += `<li><a class="dropdown-item ${bg} p-2 border-bottom" href="${n.link}"><small class="fw-bold d-block">${n.title}</small><small class="text-muted">${n.message}</small></a></li>`;
                });
                else h = '<li class="text-center p-3 text-muted small">No new notifications</li>';
                document.getElementById('notifList').innerHTML = h;
            });
        }
        function markRead() {
            fetch('api/get_notifications.php', { method: 'POST', body: 'action=read_all', headers: {'Content-Type': 'application/x-www-form-urlencoded'} })
            .then(() => { document.getElementById('notifBadge').style.display = 'none'; });
        }
        setInterval(fetchNotifications, 5000); fetchNotifications();
    </script>
</body>
</html>