<?php
// admin_completed.php - SECURE VAULT EDITION 🔒
include("connection.php");
include("darkmode.php"); 
include('session.php'); 

if (function_exists('requireRole')) {
    requireRole('admin');
}

// =========================================================
// 🔐 SECURITY CONFIGURATION
// =========================================================
$VAULT_PASSWORD = "core3"; // <--- PALITAN MO ITO

// Handle Unlock
$vault_error = "";
if (isset($_POST['btn_unlock'])) {
    $input_pass = $_POST['vault_pass'];
    if ($input_pass === $VAULT_PASSWORD) {
        $_SESSION['completed_unlocked'] = true;
        header("Location: admin_completed.php");
        exit;
    } else {
        $vault_error = "Incorrect password. Access denied.";
    }
}

// Handle Lock
if (isset($_GET['action']) && $_GET['action'] == 'lock') {
    unset($_SESSION['completed_unlocked']);
    header("Location: admin_completed.php");
    exit;
}

$is_unlocked = isset($_SESSION['completed_unlocked']) && $_SESSION['completed_unlocked'] === true;

// ==========================================================
// 1. DATA FETCHING (EXECUTE ONLY IF UNLOCKED)
// ==========================================================
$result = null;
if ($is_unlocked) {
    $sql = "SELECT 
                s.id AS shipment_id, 
                p.invoice_number,
                p.amount,
                p.payment_date,
                p.method,
                s.contract_number,
                s.sender_name,
                s.status as delivery_status,
                a.username
            FROM payments p
            JOIN shipments s ON p.shipment_id = s.id
            LEFT JOIN accounts a ON p.user_id = a.id
            WHERE s.status = 'Delivered' 
            ORDER BY p.payment_date DESC";
    $result = mysqli_query($conn, $sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Completed Transactions | Secured</title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  
  <style>
    /* --- CSS FROM DASHBOARD --- */
    :root {
      --primary: #4e73df; --secondary: #f8f9fc; --dark-bg: #1e1e2f; --dark-card: #2b2b40;
      --light-text: #f8f9fa; --radius: 1rem; --shadow: 0 0.35rem 1.2rem rgba(0, 0, 0, 0.15);
    }
    body { font-family: 'Segoe UI', system-ui, sans-serif; background-color: var(--secondary); transition: all 0.3s ease; }
    body.dark-mode { background-color: var(--dark-bg); color: var(--light-text); }
    
    .sidebar { width: 250px; height: 100vh; background: #2c3e50; color: white; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; transition: 0.3s; z-index: 1000; }
    .sidebar.collapsed { transform: translateX(-100%); }
    .sidebar a { display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.2rem; text-decoration: none; color: rgba(255,255,255,0.9); }
    .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.2); border-left: 4px solid #fff; color: white; }
    
    .content { margin-left: 250px; padding: 2rem; transition: 0.3s; }
    .content.expanded { margin-left: 0; }
    
    .header { background: white; border-radius: var(--radius); box-shadow: var(--shadow); padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
    .card { border: none; border-radius: var(--radius); padding: 1.5rem; background: white; box-shadow: var(--shadow); margin-bottom: 1.5rem; }
    
    body.dark-mode .header, body.dark-mode .card, body.dark-mode .list-group-item, body.dark-mode .modal-content { background: var(--dark-card); color: var(--light-text); border-color: #444; }
    body.dark-mode .table { color: var(--light-text); }
    body.dark-mode .table-striped > tbody > tr:nth-of-type(odd) > * { color: var(--light-text); background-color: rgba(255,255,255,0.05); }
    body.dark-mode .table-hover > tbody > tr:hover > * { color: var(--light-text); background-color: rgba(255,255,255,0.1); }
    
    .theme-switch { width: 50px; height: 25px; position: relative; display: inline-block; }
    .theme-switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; inset: 0; background-color: #ccc; border-radius: 34px; transition: .4s; }
    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 3px; background-color: white; border-radius: 50%; transition: .4s; }
    input:checked+.slider { background-color: var(--primary); }
    input:checked+.slider:before { transform: translateX(24px); }

    /* LOCK SCREEN STYLES */
    .lock-screen {
        max-width: 400px;
        margin: 5rem auto;
        text-align: center;
    }
    .lock-icon {
        font-size: 4rem;
        color: var(--primary);
        margin-bottom: 1rem;
    }
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
            <a href="admin_contracts.php" class="ps-4"><i class="bi bi-dot"></i> Manage Contracts</a>
            <a href="admin_shipments.php" class="ps-4"><i class="bi bi-dot"></i> SLA Monitoring</a>
        </div>
        <a href="E-Doc.php"><i class="bi bi-folder2-open"></i> E-Docs</a>
        <a href="admin_completed.php" class="active"><i class="bi bi-check-circle-fill"></i> Completed Trans.</a>
        <a href="BIFA.php"><i class="bi bi-graph-up"></i> BI & Analytics</a>
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
        <h4 class="fw-bold mb-0">✅ Completed Deliveries</h4>
      </div>
      <div class="d-flex align-items-center gap-2">
        <?php if($is_unlocked): ?>
            <a href="?action=lock" class="btn btn-outline-danger btn-sm rounded-pill px-3 me-3">
                <i class="bi bi-lock-fill me-1"></i> Lock Vault
            </a>
        <?php endif; ?>

        <div class="dropdown me-3">
            <a href="#" class="text-dark position-relative" id="notifDropdown" data-bs-toggle="dropdown" onclick="markRead()">
                <i class="bi bi-bell fs-4"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge" style="display: none;">0</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm p-0" style="width: 300px; max-height: 400px; overflow-y: auto;">
                <li class="p-2 border-bottom fw-bold bg-light">Notifications</li>
                <div id="notifList"><li class="text-center p-3 text-muted small">Checking...</li></div>
                <li><a class="dropdown-item text-center small text-primary p-2 border-top" href="feedback.php">View All</a></li>
            </ul>
        </div>
        <small>Dark Mode</small>
        <label class="theme-switch">
          <input type="checkbox" id="adminThemeToggle"><span class="slider"></span>
        </label>
      </div>
    </div>

    <?php if (!$is_unlocked): ?>
        <div class="lock-screen fade-in">
            <div class="card shadow-lg border-0 p-4">
                <div class="card-body">
                    <i class="bi bi-shield-lock-fill lock-icon"></i>
                    <h4 class="fw-bold mb-1">Financial Records Locked</h4>
                    <p class="text-muted small mb-4">Authorized personnel only. Enter password to view.</p>
                    
                    <?php if($vault_error): ?>
                        <div class="alert alert-danger py-2 small"><?php echo $vault_error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                            <input type="password" name="vault_pass" class="form-control" placeholder="Enter Password" required autofocus>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="btn_unlock" class="btn btn-primary fw-bold py-2">
                                Unlock Records <i class="bi bi-arrow-right-short"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php else: ?>

    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>OR Number</th>
                        <th>Date Paid</th>
                        <th>Tracking No.</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td class="fw-bold text-danger"><?php echo $row['invoice_number']; ?></td>
                            <td><?php echo date('M d, Y h:i A', strtotime($row['payment_date'])); ?></td>
                            <td>
                                <small class="text-muted">ID: <?php echo $row['shipment_id']; ?></small><br>
                                <?php echo $row['contract_number']; ?>
                            </td>
                            <td>
                                <strong><?php echo $row['sender_name']; ?></strong><br>
                                <small class="text-muted">User: <?php echo $row['username']; ?></small>
                            </td>
                            <td class="fw-bold text-success">₱<?php echo number_format($row['amount'], 2); ?></td>
                            <td>
                                <span class="badge bg-success">DELIVERED</span>
                                <span class="badge bg-primary">PAID</span>
                            </td>
                            <td class="text-center">
                                <button onclick="openReceipt(<?php echo $row['shipment_id']; ?>)" 
                                        class="btn btn-primary btn-sm rounded-pill px-3"
                                        title="View Official Receipt">
                                    <i class="bi bi-eye-fill"></i> View OR
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No completed transactions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
      </div>
    </div>

    <div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" style="max-width: 900px;">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
            <h5 class="modal-title"><i class="bi bi-receipt"></i> Official Receipt Preview</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="background: #555;">
            <iframe id="receiptFrame" src="" style="width: 100%; height: 80vh; border: none; display: block;"></iframe>
            </div>
            <div class="modal-footer bg-light">
                <small class="text-muted me-auto">Note: Use the print button inside the receipt to save as PDF.</small>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
        </div>
    </div>

    <?php endif; ?> </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    <?php if ($is_unlocked): ?>
    // 1. RECEIPT MODAL FUNCTION (Only needed if unlocked)
    function openReceipt(id) {
        const frame = document.getElementById('receiptFrame');
        frame.src = "waybill.php?id=" + id;
        const myModal = new bootstrap.Modal(document.getElementById('receiptModal'));
        myModal.show();
    }
    <?php endif; ?>

    // 2. DARK MODE INIT
    const toggle = document.getElementById('adminThemeToggle');
    const body = document.body;
    if(localStorage.getItem('theme') === 'dark'){
        body.classList.add('dark-mode');
        toggle.checked = true;
    }
    toggle.addEventListener('change', () => {
        if(toggle.checked){
            body.classList.add('dark-mode');
            localStorage.setItem('theme', 'dark');
        } else {
            body.classList.remove('dark-mode');
            localStorage.setItem('theme', 'light');
        }
    });

    // 3. SIDEBAR TOGGLE
    document.getElementById('hamburger').addEventListener('click', () => {
      document.getElementById('sidebar').classList.toggle('collapsed');
      document.getElementById('mainContent').classList.toggle('expanded');
    });

    // 4. NOTIFICATION SYSTEM
    function fetchNotifications() {
        fetch('api/get_notifications.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('notifBadge');
            const list = document.getElementById('notifList');
            if (data.count > 0) {
                badge.innerText = data.count;
                badge.style.display = 'inline-block';
            } else { badge.style.display = 'none'; }

            let html = '';
            if (data.data.length > 0) {
                data.data.forEach(notif => {
                    let bgClass = notif.is_read == 0 ? 'bg-light' : '';
                    let icon = notif.is_read == 0 ? 'bi-circle-fill text-primary' : 'bi-check-circle text-muted';
                    html += `<li><a class="dropdown-item ${bgClass} p-2 border-bottom" href="#">
                        <div class="d-flex align-items-start"><i class="bi ${icon} me-2 mt-1" style="font-size: 10px;"></i>
                        <div><small class="fw-bold d-block">${notif.title}</small><small class="text-muted text-wrap">${notif.message}</small></div>
                        </div></a></li>`;
                });
            } else { html = '<li class="text-center p-3 text-muted small">No new notifications</li>'; }
            list.innerHTML = html;
        });
    }
    function markRead() {
        fetch('api/get_notifications.php', { method: 'POST', body: 'action=read_all', headers: {'Content-Type': 'application/x-www-form-urlencoded'} })
        .then(() => { document.getElementById('notifBadge').style.display = 'none'; });
    }
    fetchNotifications();
    setInterval(fetchNotifications, 5000);
  </script>
</body>
</html>