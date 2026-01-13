<?php
include('connection.php');
include("darkmode.php");
include('session.php');
include('loading.html');
requireRole('admin');

// Handle restore
if (isset($_GET['restore'])) {
    $id = intval($_GET['restore']);
    $res = $conn->query("SELECT * FROM archive_crm WHERE id = $id");

    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();

        $stmt = $conn->prepare("INSERT INTO accounts (username, email, phone_number, gender, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssss", $row['username'], $row['email'], $row['phone_number'], $row['gender'], $row['role']);
        $stmt->execute();

        $conn->query("DELETE FROM archive_crm WHERE id = $id");

        $_SESSION['alert'] = ['title' => 'Restored!', 'text' => 'Customer restored successfully.', 'icon' => 'success'];
       header("Location: Archive_CRM.php");
exit;

        
    }
}

$result = $conn->query("SELECT * FROM archive_crm ORDER BY archived_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Archived CRM Customers</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
      --border-radius: 1rem;
      --shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

body {
    background-color: #f8f9fa;
    transition: background-color 0.3s, color 0.3s;
}

body.dark-mode {
    background-color: #1a1a2e;
    color: #f8f9fa;
}

/* Sidebar */
    .sidebar {
      width: 250px;
      height: 100vh;
     background: #2c3e50;
      color: white;
      position: fixed;
      top: 0;
      left: 0;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: all 0.3s ease;
      z-index: 1000;
    }

    .sidebar.collapsed {
      transform: translateX(-100%);
    }

    .sidebar .logo {
      text-align: center;
      padding: 1.2rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .sidebar .logo img {
      width: 120px;
    }

    .sidebar a {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.85rem 1.2rem;
      text-decoration: none;
      color: rgba(255, 255, 255, 0.9);
      transition: 0.2s;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background: rgba(255, 255, 255, 0.2);
      color: #fff;
      border-left: 4px solid #fff;
    }

/* Dropdown */
.dropdown-container .dropdown-toggle2 {
  cursor: pointer;
}

.dropdown-content {
  display: none;
  flex-direction: column;
  margin-left: 15px;
  border-left: 2px solid #444;
  margin-top: 5px;
  padding-left: 10px;
}

.dropdown-content a {
  font-size: 0.9rem;
  padding: 8px 10px;
  color: #aaa;
}

.dropdown-content a:hover {
  color: #fff;
}

.dropdown-content.show {
  display: flex;
  animation: slideDown 0.3s ease;
}

@keyframes slideDown {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Main Content */
        .content {
          margin-left: var(--sidebar-width);
          padding: 2rem;
          transition: margin-left .3s;
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
            margin-left: 0.5rem;
        }

        .system-title {
            color: var(--primary-color);
            font-size: 1rem;
        }

/* Table */
.table {
  overflow: hidden;
  background-color: #ffffff;
  color: #212529;
}
.table thead {
    background-color: #4e73df;
    color: white;
}
.dark-mode .table thead {
    background-color: #0f3460;
}
.dark-mode .table {
    color: #f8f9fa;
}
.table th {
    background-color: var(--primary-color);
            color: var(--text-light);
}

/* Hamburger */
.hamburger {
    font-size: 1.5rem;
    cursor: pointer;
}

/* 🌙 DARK MODE TABLE STYLING */
body.dark-mode .card {
  background-color: var(--dark-card);
  color: var(--text-light);
  border: 1px solid rgba(255,255,255,0.1);
  box-shadow: 0 0 10px rgba(0,0,0,0.3);
}

body.dark-mode .table {
  background-color: #1e2a47;
  color: #f8f9fa;
  border-collapse: separate;
  border-spacing: 0;
  overflow: hidden;
}

body.dark-mode .table thead {
  background-color: #283b6a;
  color: #ffffff;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  border-bottom: 2px solid #3b55d9;
}

body.dark-mode .table tbody tr:nth-child(even) {
  background-color: rgba(255,255,255,0.03);
}

body.dark-mode .table-hover tbody tr:hover {
  background-color: rgba(255, 255, 255, 0.08);
  transition: background-color 0.2s ease-in-out;
}

body.dark-mode th,
body.dark-mode td {
    background-color: var(--primary-color);
            color: var(--text-light);
}

body.dark-mode td {
  background-color: transparent;
}

body.dark-mode .table td.text-center {
  color: #cfd4e3;
}

/* ✅ Buttons inside dark-mode tables */
body.dark-mode .btn-success {
  background-color: #1cc88a;
  border-color: #1cc88a;
  color: white;
}

body.dark-mode .btn-success:hover {
  background-color: #17b17a;
}

body.dark-mode .btn-primary {
  background-color: #3b55d9;
  border-color: #3b55d9;
  color: white;
}

body.dark-mode .btn-primary:hover {
  background-color: #324ac0;
}

/* ✅ Scrollbar styling for table container */
.card-body {
  overflow-x: auto;
}

.card-body::-webkit-scrollbar {
  height: 8px;
}

.card-body::-webkit-scrollbar-thumb {
  background: var(--primary-color);
  border-radius: 10px;
}

.dark-mode .card-body::-webkit-scrollbar-thumb {
  background: #4e73df;
}

.dark-mode .card-body::-webkit-scrollbar-track {
  background: #1a1a2e;
}


/* Theme Toggle */
    .theme-toggle-container {
      display: flex;
      align-items: center;
      gap: 0.6rem;
    }

    .theme-switch {
      width: 50px;
      height: 25px;
      position: relative;
      display: inline-block;
    }

    .theme-switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .slider {
      position: absolute;
      cursor: pointer;
      inset: 0;
      background-color: #ccc;
      border-radius: 34px;
      transition: .4s;
    }

    .slider:before {
      position: absolute;
      content: "";
      height: 18px;
      width: 18px;
      left: 4px;
      bottom: 3px;
      background-color: white;
      border-radius: 50%;
      transition: .4s;
    }

    input:checked+.slider {
      background-color: var(--primary-color);
    }

    input:checked+.slider:before {
      transform: translateX(24px);
    }

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
        <div class="sidebar" id="sidebar">
    <div>
      <div class="logo">
        <img src="Remorig.png" alt="Logo">
        <h6 class="mt-2 mb-0 text-light fw-normal">CORE TRANSACTION 3</h6>
      </div>
      <nav class="mt-3">
        <a href="admin.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <div class="dropdown-container">
        <a href="#" class="dropdown-toggle"><i class="bi bi-people-fill me-2"></i> CRM </a>
        <div class="dropdown-content">
          <a href="CRM.php"><i class="bi bi-circle"></i> Overview</a>
          <a href="customer_feedback.php"><i class="bi bi-chat-dots"></i> Customer Feedback</a>
        </div>
      </div>
        <a href="CSM.php"><i class="bi bi-file-text"></i> Contract & SLA</a>
        <a href="E-Doc.php"><i class="bi bi-folder2-open"></i> E-Docs</a>
        <a href="BIFA.php"><i class="bi bi-graph-up"></i> BI &amp; Freight Analytics</a>
        <a href="activity-log.php"><i class="bi bi-clock-history"></i> Activity Log</a>
        <div class="dropdown-container">
        <a href="#" class="dropdown-toggle"><i class="bi bi-archive-fill"></i> Archived</a>
        <div class="dropdown-content">
          <a href="Archive.php"><i class="bi bi-archive"></i> Documents</a>
          <a href="Archive_CRM.php"><i class="bi bi-people"></i> Customers</a>
        </div>
      </div>
        <a href="logout.php" class="border-top"><i class="bi bi-box-arrow-right"></i> Logout</a>
      </nav>
    </div>
  </div>

    <div class="content" id="mainContent">
    <div class="header">
      <div class="d-flex align-items-center gap-3">
        <div class="hamburger" id="hamburger">☰</div>
        <h4 class="fw-bold mb-0">Archived<span class="text-primary"> | Customers</span></h4>
      </div>
      <div class="theme-toggle-container">
        <small>Dark Mode</small>
        <label class="theme-switch">
          <input type="checkbox" id="adminThemeToggle">
          <span class="slider"></span>
        </label>
      </div>
    </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>USERNAME</th>
                            <th>EMAIL</th>
                            <th>PHONE</th>
                            <th>GENDER</th>
                            <th>ROLE</th>
                            <th>ARCHIVED ON</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                    <td>" . htmlspecialchars($row['username']) . "</td>
                                    <td>" . htmlspecialchars($row['email']) . "</td>
                                    <td>" . htmlspecialchars($row['phone_number']) . "</td>
                                    <td>" . htmlspecialchars($row['gender']) . "</td>
                                    <td>" . htmlspecialchars($row['role']) . "</td>
                                    <td>" . htmlspecialchars($row['archived_at']) . "</td>
                                    <td>
                                       <a href='Archive_CRM.php?restore=" . $row['id'] . "' class='btn btn-success btn-sm restore'>
                                            <i class='bi bi-arrow-clockwise'></i> Restore
                                        </a>

                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center'>No archived customers found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<script>
initDarkMode("adminThemeToggle", "adminDarkMode");
// Sidebar toggle
document.getElementById("hamburger").addEventListener("click", function () {
    document.getElementById("sidebar").classList.toggle("collapsed");
    document.getElementById("mainContent").classList.toggle("expanded");
});

// Sidebar accordion behavior
const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

dropdownToggles.forEach(toggle => {
  toggle.addEventListener('click', (e) => {
    e.preventDefault();
    const currentMenu = toggle.nextElementSibling;

    // Close others
    document.querySelectorAll('.dropdown-content').forEach(menu => {
      if (menu !== currentMenu) menu.classList.remove('show');
    });

    // Toggle current dropdown
    currentMenu.classList.toggle('show');
  });
});

// keep dropdown open on current subpage
const path = window.location.pathname.split("/").pop();

// CRM pages
if (path === "CRM.php" || path === "customer_feedback.php") {
  const crmMenu = document.querySelectorAll('.dropdown-content')[0];
  const crmToggle = document.querySelectorAll('.dropdown-toggle')[0];
  crmMenu.classList.add('show');
  crmToggle.classList.add('active');
  crmMenu.querySelectorAll('a').forEach(link => {
    if (link.getAttribute('href') === path) link.classList.add('active');
  });
}

// Archived pages
if (path === "Archive.php" || path === "Archive_CRM.php") {
  const arcMenu = document.querySelectorAll('.dropdown-content')[1];
  const arcToggle = document.querySelectorAll('.dropdown-toggle')[1];
  arcMenu.classList.add('show');
  arcToggle.classList.add('active');
  arcMenu.querySelectorAll('a').forEach(link => {
    if (link.getAttribute('href') === path) link.classList.add('active');
  });
}

// Highlight main nav link (non-dropdown)
document.querySelectorAll('.sidebar > div nav > a').forEach(link => {
  if (link.getAttribute('href') === path) link.classList.add('active');
});


// SweetAlert restore confirmation
document.querySelectorAll(".restore").forEach(btn => {
    btn.addEventListener("click", function(e) {
        e.preventDefault();
        const url = this.href;
        Swal.fire({
            title: 'Restore Customer?',
            text: "This will move the customer back to the CRM table.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, restore it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });
});

// Session alert
<?php if (isset($_SESSION['alert'])):
    $alert = $_SESSION['alert'];
    unset($_SESSION['alert']); ?>
    Swal.fire({
        title: '<?= $alert['title'] ?>',
        text: '<?= $alert['text'] ?>',
        icon: '<?= $alert['icon'] ?>',
        confirmButtonColor: '#4e73df'
    });
<?php endif; ?>
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
