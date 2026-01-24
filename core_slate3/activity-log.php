<?php
include('connection.php');
include("darkmode.php");
include('session.php');
include('loading.html');
requireRole('admin');

$result = $conn->query("SELECT * FROM activity_log ORDER BY login_time DESC");
$activityResult = $conn->query("SELECT * FROM admin_activity ORDER BY date DESC LIMIT 100");

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Activity Log</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
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
      font-family: 'Segoe UI', system-ui, sans-serif;
      overflow-x: hidden;
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
.dropdown-container .dropdown-toggle {
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
    
    /* Table Section */
        .table-section{
       border-radius: var(--border-radius);
       padding: 1.5rem;
       text-align: center;
       background-color: white;
       box-shadow: var(--shadow);
      }

        .table-section1 {
       margin-bottom: 0.5rem; /* space below */
      }

        .dark-mode .table-section{
       background-color: var(--dark-card);
       color: var(--text-light);
      }

      .UAL {
          margin-bottom: 1.5rem;
      }

      .RA{
          margin: 1.5rem 0 1.5rem 0;
      }  
      
        table {
            width: 100%;
            margin-top: 1rem;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .dark-mode th,
        .dark-mode td {
            border-bottom-color: #3a4b6e;
        }

        thead {
            background-color: var(--primary-color);
            color: white;
        }
        
        .table-scroll-container {
  max-height: 410px;
  overflow-y: auto;   /* ✅ scroll on wrapper, not tbody */
  border-radius: var(--border-radius);
  box-shadow: var(--shadow);
}

.table-scroll {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
  margin-top: 0;   /* ✅ wala nang gap sa taas */
}

.table-scroll th,
.table-scroll td {
  padding: 0.75rem;
  text-align: left;
  border-bottom: 1px solid #ddd;
  word-wrap: break-word;
}

.table-scroll thead th {
  position: sticky;
  top: 0;
  background-color: var(--primary-color);
  color: white;
  z-index: 2;
  margin: 0;        /* ✅ tanggalin extra spacing */
  padding-top: 0.5rem; /* optional: para hindi dikit masyado */
}

/* Custom scrollbar on wrapper */
.table-scroll-container::-webkit-scrollbar {
  width: 10px;
}
.table-scroll-container::-webkit-scrollbar-thumb {
  background: var(--primary-color);
  height: 42.5px;
}
.table-scroll-container::-webkit-scrollbar-track {
  background: #f1f1f1;
}
.dark-mode .table-scroll-container::-webkit-scrollbar-thumb {
  background: #4e73df;
}
.dark-mode .table-scroll-container::-webkit-scrollbar-track {
  background: #1a1a2e;
}
    


    /* Theme Toggle */
    .theme-toggle-container {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      margin-right: 0.5rem;
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
        <a href="activity-log.php" class="active"><i class="bi bi-clock-history"></i> Activity Log</a>
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
        <h4 class="fw-bold mb-0">Activity Log</h4>
      </div>
      <div class="theme-toggle-container">
        <small>Dark Mode</small>
        <label class="theme-switch">
          <input type="checkbox" id="adminThemeToggle">
          <span class="slider"></span>
        </label>
      </div>
    </div>


    
    <div class="table-section">
            <h3 class="fw-bold UAL">User Activity Log</h3>
             <div class="table-scroll-container">
                <table class="table-scroll">
                    <colgroup>
    <col style="width: 10%;">
    <col style="width: 20%;">
    <col style="width: 20%;">
    <col style="width: 30%;">
    <col style="width: 20%;">
  </colgroup>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>IP Address</th>
                        <th>User Agent</th>
                        <th>Login Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['ip_address']) ?></td>
                <td><?= htmlspecialchars($row['user_agent']) ?></td>
                <td><?= htmlspecialchars($row['login_time']) ?></td>
              </tr>
            <?php endwhile; ?>
                </tbody>
            </table>
            </div>
        
            <h3 class="fw-bold RA">Recent Activity</h3>
             <div class="table-scroll-container">
                <table class="table-scroll">
                    <colgroup>
    <col style="width: 20%;">
    <col style="width: 20%;">
    <col style="width: 40%;">
    <col style="width: 20%;">
  </colgroup>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Module</th>
                        <th>Activity</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($activityResult && $activityResult->num_rows > 0): ?>
              <?php while ($row = $activityResult->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['date']); ?></td>
                  <td><?= htmlspecialchars($row['module']); ?></td>
                  <td><?= htmlspecialchars($row['activity']); ?></td>
                  <td>
                    <?php if ($row['status'] === 'Success'): ?>
                      <span class="badge bg-success"><?= htmlspecialchars($row['status']); ?></span>
                    <?php elseif ($row['status'] === 'Failed'): ?>
                      <span class="badge bg-danger"><?= htmlspecialchars($row['status']); ?></span>
                    <?php else: ?>
                      <span class="badge bg-secondary"><?= htmlspecialchars($row['status']); ?></span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="4" class="text-center text-muted py-3">No recent activity found</td>
              </tr>
            <?php endif; ?>
                </tbody>
            </table>
        </div>
  </div>
  </div>





  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    initDarkMode("adminThemeToggle", "adminDarkMode");

    document.getElementById('hamburger').addEventListener('click', function () {
      document.getElementById('sidebar').classList.toggle('collapsed');
      document.getElementById('mainContent').classList.toggle('expanded');
    });
    
     // Sidebar accordion behavior
const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

dropdownToggles.forEach(toggle => {
  toggle.addEventListener('click', (e) => {
    e.preventDefault();
    const currentMenu = toggle.nextElementSibling;

    // Close all others
    document.querySelectorAll('.dropdown-content').forEach(menu => {
      if (menu !== currentMenu) menu.classList.remove('show');
    });

    // Toggle current
    currentMenu.classList.toggle('show');
  });
});

  </script>
</body>

</html>