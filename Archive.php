<?php
include('connection.php');
include("darkmode.php");
include('session.php');
include('loading.html');
requireRole('admin');

// Handle restore
if (isset($_GET['restore'])) {
    $id = intval($_GET['restore']);

    $res = $conn->query("SELECT * FROM archive_doc WHERE id = $id");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();

        $stmt = $conn->prepare("INSERT INTO e_doc (title, doc_type, filename, status) VALUES (?, ?, ?, 'Pending Review')");
        $stmt->bind_param("sss", $row['title'], $row['doc_type'], $row['filename']);
        $stmt->execute();

        $archiveFile = "uploads/archive/" . $row['filename'];
        $uploadsFile = "uploads/" . $row['filename'];
        if (file_exists($archiveFile)) {
            rename($archiveFile, $uploadsFile);
        }

        $conn->query("DELETE FROM archive_doc WHERE id = $id");

        $module = "E-Documentation";
        $activity = "Restored document: " . $row['title'];
        $status = "Restored";
        $conn->query("INSERT INTO admin_activity (`module`, `activity`, `status`, `date`) 
                    VALUES ('$module', '$activity', '$status', NOW())");

        $_SESSION['alert'] = ['title' => 'Restored!', 'text' => 'Document restored successfully.', 'icon' => 'success'];
        header("Location: Archive.php");
        exit;
    }
}

$result = $conn->query("SELECT * FROM archive_doc ORDER BY archived_on DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Archived Documents</title>
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


    .theme-toggle-container {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .theme-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 26px;
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
        border-radius: 26px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked + .slider {
        background-color: #4e73df;
    }

    input:checked + .slider:before {
        transform: translateX(24px);
    }

    .table {
  overflow: hidden;
  background-color: #ffffff;
  color: #212529;
}

.table thead {
  background-color: #4e73df;
  color: #ffffff;
}
.table th {
    background-color: var(--primary-color);
            color: var(--text-light);
}

.table-hover tbody tr:hover {
  background-color: #f1f1f1;
}


/* Buttons */
.btn {
  border-radius: 0.3rem;
}
body.dark-mode .btn-primary {
  background-color: #3b55d9;
  border-color: #3b55d9;
}
body.dark-mode .btn-success {
  background-color: #1cc88a;
  border-color: #1cc88a;
}
body.dark-mode .btn:hover {
  opacity: 0.9;
}

/* 🌙 DARK MODE TABLE STYLES */
body.dark-mode .card {
  background-color: #16213e;
  color: #f8f9fa;
  border: 1px solid rgba(255,255,255,0.1);
  box-shadow: 0 0 10px rgba(0,0,0,0.3);
}

body.dark-mode .table {
  background-color: #1e2a47;
  color: #f8f9fa;
  border-collapse: separate;
  border-spacing: 0;
}

body.dark-mode .table thead {
  background-color: #4e73df;
  color: #ffffff;  
  letter-spacing: 0.03em;
}

body.dark-mode .table-hover tbody tr:hover {
  background-color: rgba(255,255,255,0.1);
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


/* Buttons inside the table */
body.dark-mode .btn {
  color: #fff !important;
  border: none;
}

body.dark-mode .btn-primary {
  background-color: #3b55d9;
}

body.dark-mode .btn-success {
  background-color: #1cc88a;
}

body.dark-mode .btn-primary:hover {
  background-color: #324ac0;
}

body.dark-mode .btn-success:hover {
  background-color: #17b17a;
}

/* Empty table message */
body.dark-mode .table td.text-center {
  color: #cfd4e3;
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
        <h4 class="fw-bold mb-0">Archived<span class="text-primary"> | Documents</span></h4>
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
                            <th>TITLE</th>
                            <th>TYPE</th>
                            <th>FILENAME</th>
                            <th>ARCHIVED ON</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $id = intval($row['id']);
                                $title = htmlspecialchars($row['title'], ENT_QUOTES);
                                $type = htmlspecialchars($row['doc_type'], ENT_QUOTES);
                                $filename = htmlspecialchars($row['filename'], ENT_QUOTES);
                                $archived_on = htmlspecialchars($row['archived_on'], ENT_QUOTES);

                                echo "
                                <tr>
                                    <td>$title</td>
                                    <td>$type</td>
                                    <td>$filename</td>
                                    <td>$archived_on</td>
                                    <td>
                                        <a class='btn btn-primary btn-sm' href='uploads/archive/$filename' download>
                                            <i class='bi bi-download'></i> Download
                                        </a>
                                        <a class='btn btn-success btn-sm restore' href='Archive.php?restore=$id'>
                                            <i class='bi bi-arrow-counterclockwise'></i> Restore
                                        </a>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center'>No archived documents found</td></tr>";
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

// ✅ Keep dropdown open on current subpage
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


    // SweetAlert confirmation
    document.querySelectorAll(".restore").forEach(btn => {
        btn.addEventListener("click", function(e) {
            e.preventDefault();
            const url = this.href;
            Swal.fire({
                title: 'Restore Document?',
                text: "This will move the document back to main records.",
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

