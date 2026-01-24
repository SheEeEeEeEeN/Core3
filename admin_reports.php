<?php
// admin_reports.php
include("connection.php");
// Note: Assumed na nandito yung function na initDarkMode() base sa BIFA code mo
include("darkmode.php"); 
include('session.php');
requireRole('admin');
include('loading.html');

// 1. DEFAULT DATES (First day of month to Now)
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'All';

// 2. BUILD QUERY
$sql = "SELECT * FROM shipments WHERE DATE(created_at) BETWEEN '$startDate' AND '$endDate'";

if($statusFilter != 'All') {
    $sql .= " AND status = '$statusFilter'";
}

$sql .= " ORDER BY created_at DESC";
$result = $conn->query($sql);

// 3. COMPUTE TOTALS
$totalSales = 0;
$count = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Generate Reports</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  
  <style>
    /* VARIABLES */
    :root {
      --primary: #4e73df; --secondary: #f8f9fc; --dark-bg: #1e1e2f; --dark-card: #2b2b40;
      --light-text: #f8f9fa; --radius: 1rem; --shadow: 0 0.35rem 1.2rem rgba(0, 0, 0, 0.15);
    }
    body { font-family: 'Segoe UI', system-ui, sans-serif; background-color: var(--secondary); transition: all 0.3s ease; }
    
    /* DARK MODE STYLES - GENERAL */
    body.dark-mode { background-color: var(--dark-bg); color: var(--light-text); }
    body.dark-mode .header, body.dark-mode .card, body.dark-mode .list-group-item { background: var(--dark-card); color: var(--light-text); border-color: #444; }
    body.dark-mode .form-control, body.dark-mode .form-select { background-color: #3a3b45; color: white; border: 1px solid #555; }
    
    /* DARK MODE STYLES - TABLE SPECIFIC FIX */
    body.dark-mode .table { 
        color: var(--light-text); 
        border-color: #444;
    }
    body.dark-mode .table thead th {
        background-color: #1a1a2e; /* Mas madilim na header para distinct */
        color: white;
        border-bottom: 2px solid #555;
    }
    body.dark-mode .table tbody td {
        background-color: var(--dark-card);
        color: var(--light-text);
        border-color: #444;
    }
    body.dark-mode .table tfoot td {
        background-color: var(--dark-card);
        color: var(--light-text);
        border-top: 2px solid #555;
    }
    /* Hover Effect sa Dark Mode */
    body.dark-mode .table-hover tbody tr:hover td { 
        background-color: #3a3b45; 
        color: white; 
    }
    
    /* SIDEBAR STYLES */
    .sidebar { width: 250px; height: 100vh; background: #2c3e50; color: white; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; transition: 0.3s; z-index: 1000; overflow-y: auto; }
    .sidebar.collapsed { transform: translateX(-100%); }
    .content { margin-left: 250px; padding: 2rem; transition: 0.3s; }
    .content.expanded { margin-left: 0; }
    
    .sidebar a { display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.2rem; text-decoration: none; color: rgba(255,255,255,0.9); }
    .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.2); border-left: 4px solid #fff; color: white; }
    
    /* HEADER & CARD */
    .header { background: white; border-radius: var(--radius); box-shadow: var(--shadow); padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
    .card { border: none; border-radius: var(--radius); padding: 1.5rem; background: white; box-shadow: var(--shadow); margin-bottom: 1.5rem; }
    
    /* TOGGLE SWITCH */
    .theme-switch { width: 50px; height: 25px; position: relative; display: inline-block; }
    .theme-switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; inset: 0; background-color: #ccc; border-radius: 34px; transition: .4s; }
    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 3px; background-color: white; border-radius: 50%; transition: .4s; }
    input:checked+.slider { background-color: var(--primary); }
    input:checked+.slider:before { transform: translateX(24px); }
  </style>
</head>

<body>

  <div class="sidebar" id="sidebar">
    <div>
      <div class="text-center p-3 border-bottom border-secondary">
        <img src="Remorig.png" alt="Logo" style="width: 100px;">
        <h6 class="mt-2 mb-0 text-light">CORE ADMIN</h6>
      </div>
      <nav class="mt-3">
        <a href="admin.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
        
        <a href="#crmSubmenu" data-bs-toggle="collapse" class="d-flex justify-content-between">
            <span><i class="bi bi-people"></i> CRM</span><i class="bi bi-chevron-down small"></i>
        </a>
        <div class="collapse" id="crmSubmenu" style="background: rgba(0,0,0,0.2);">
            <a href="CRM.php" class="ps-4"><i class="bi bi-dot"></i> CRM Dashboard</a>
            <a href="customer_feedback.php" class="ps-4"><i class="bi bi-dot"></i> Customer Feedback</a>
        </div>

        <a href="#csmSubmenu" data-bs-toggle="collapse" class="d-flex justify-content-between">
            <span><i class="bi bi-file-text"></i> Contract & SLA</span><i class="bi bi-chevron-down small"></i>
        </a>
        <div class="collapse" id="csmSubmenu" style="background: rgba(0,0,0,0.2);">
            <a href="Admin_contracts.php" class="ps-4"><i class="bi bi-dot"></i> Manage Contracts</a>
            <a href="Admin_shipments.php" class="ps-4"><i class="bi bi-dot"></i> SLA Monitoring</a>
        </div>

        <a href="E-Doc.php"><i class="bi bi-folder2-open"></i> E-Docs</a>
        <a href="BIFA.php"><i class="bi bi-graph-up"></i> BI & Analytics</a>
        
        <a href="admin_reports.php" class="active">
           <i class="bi bi-file-earmark-bar-graph"></i> Reports Generation
        </a>
        
        <a href="activity-log.php"><i class="bi bi-clock-history"></i> Activity Log</a>
        <a href="Archive.php"><i class="bi bi-archive"></i> Archives</a>
        <a href="logout.php" class="border-top mt-3"><i class="bi bi-box-arrow-right"></i> Logout</a>
      </nav>
    </div>
  </div>

  <div class="content" id="mainContent">
    
    <div class="header">
      <div class="d-flex align-items-center gap-3">
        <i class="bi bi-list fs-4" id="hamburger" style="cursor: pointer;"></i>
        <h4 class="fw-bold mb-0">Generate Reports</h4>
      </div>
      <div class="d-flex align-items-center gap-2">
        <div class="dropdown me-3">
    <a href="#" class="text-dark position-relative" id="notifDropdown" data-bs-toggle="dropdown" onclick="markRead()">
        <i class="bi bi-bell fs-4"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge" style="display: none;">
            0
        </span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm p-0" style="width: 300px; max-height: 400px; overflow-y: auto;">
        <li class="p-2 border-bottom fw-bold bg-light">Notifications</li>
        <div id="notifList">
            <li class="text-center p-3 text-muted small">Checking...</li>
        </div>
        <li><a class="dropdown-item text-center small text-primary p-2 border-top" href="feedback.php">View All</a></li>
    </ul>
</div>
        <small>Dark Mode</small>
        <label class="theme-switch">
          <input type="checkbox" id="adminThemeToggle"><span class="slider"></span>
        </label>
      </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold">From Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo $startDate; ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">To Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo $endDate; ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-select">
                        <option value="All" <?php if($statusFilter=='All') echo 'selected'; ?>>All Status</option>
                        <option value="Delivered" <?php if($statusFilter=='Delivered') echo 'selected'; ?>>Delivered (Sales)</option>
                        <option value="Pending" <?php if($statusFilter=='Pending') echo 'selected'; ?>>Pending</option>
                        <option value="Cancelled" <?php if($statusFilter=='Cancelled') echo 'selected'; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter"></i> Filter Data</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="m-0 fw-bold text-primary">Report Preview</h6>
            <a href="print_report.php?start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>&status=<?php echo $statusFilter; ?>" 
               target="_blank" 
               class="btn btn-success fw-bold">
               <i class="bi bi-printer-fill"></i> Print / Save PDF
            </a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tracking #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Destination</th>
                        <th>Status</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result->num_rows > 0):
                        while($row = $result->fetch_assoc()): 
                            $totalSales += $row['price'];
                            $count++;
                    ?>
                    <tr>
                        <td><span class="badge bg-secondary"><?php echo $row['id']; ?></span></td>
                        <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                        <td><?php echo $row['sender_name']; ?></td>
                        <td><?php echo $row['destination_island']; ?></td>
                        <td>
                            <?php 
                                $s = $row['status'];
                                $badge = ($s=='Delivered')?'success':(($s=='Cancelled')?'danger':'warning');
                                echo "<span class='badge bg-$badge'>$s</span>";
                            ?>
                        </td>
                        <td class="text-end fw-bold">₱<?php echo number_format($row['price'], 2); ?></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="6" class="text-center text-muted">No records found for this date range.</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="table-active">
                        <td colspan="5" class="text-end fw-bold fs-5">TOTAL SALES:</td>
                        <td class="text-end fw-bold fs-5 text-success">₱<?php echo number_format($totalSales, 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // JS Logic para sa Dark Mode at Sidebar Toggle
    if (typeof initDarkMode === 'function') {
        initDarkMode("adminThemeToggle", "adminDarkMode");
    } else {
        const toggle = document.getElementById('adminThemeToggle');
        if(localStorage.getItem('adminDarkMode') === 'enabled'){
            document.body.classList.add('dark-mode');
            toggle.checked = true;
        }
        toggle.addEventListener('change', () => {
            if(toggle.checked){
                document.body.classList.add('dark-mode');
                localStorage.setItem('adminDarkMode', 'enabled');
            } else {
                document.body.classList.remove('dark-mode');
                localStorage.setItem('adminDarkMode', 'disabled');
            }
        });
    }

    document.getElementById('hamburger').addEventListener('click', () => {
      document.getElementById('sidebar').classList.toggle('collapsed');
      document.getElementById('mainContent').classList.toggle('expanded');
    });
  </script>
  <script>
    // --- GLOBAL NOTIFICATION SCRIPT ---
    function fetchNotifications() {
        // Siguraduhing tama ang path ng API mo relative sa file location
        // Kung nasa root folder ka (gaya ng user.php), gamitin ang 'api/get_notifications.php'
        fetch('api/get_notifications.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('notifBadge');
            const list = document.getElementById('notifList');

            // 1. Update Badge Count
            if (data.count > 0) {
                badge.innerText = data.count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }

            // 2. Update Dropdown List
            let html = '';
            if (data.data.length > 0) {
                data.data.forEach(notif => {
                    let bgClass = notif.is_read == 0 ? 'bg-light' : '';
                    let icon = notif.is_read == 0 ? 'bi-circle-fill text-primary' : 'bi-check-circle text-muted';
                    
                    // Adjust link if needed based on user role
                    let link = notif.link ? notif.link : '#';

                    html += `
                    <li>
                        <a class="dropdown-item ${bgClass} p-2 border-bottom" href="${link}">
                            <div class="d-flex align-items-start">
                                <i class="bi ${icon} me-2 mt-1" style="font-size: 10px;"></i>
                                <div>
                                    <small class="fw-bold d-block">${notif.title}</small>
                                    <small class="text-muted text-wrap">${notif.message}</small>
                                    <br>
                                    <small class="text-secondary" style="font-size: 0.7rem;">${new Date(notif.created_at).toLocaleString()}</small>
                                </div>
                            </div>
                        </a>
                    </li>`;
                });
            } else {
                html = '<li class="text-center p-3 text-muted small">No new notifications</li>';
            }
            list.innerHTML = html;
        })
        .catch(err => console.error("Notif Error:", err));
    }

    function markRead() {
        fetch('api/get_notifications.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=read_all'
        }).then(() => {
            document.getElementById('notifBadge').style.display = 'none';
        });
    }

    // Run immediately and every 5 seconds
    fetchNotifications();
    setInterval(fetchNotifications, 5000);
</script>

</body>
</html>