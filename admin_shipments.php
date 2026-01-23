<?php
// admin_shipments.php - SLA CONTROL TOWER
include("connection.php");
include("darkmode.php");
include('session.php');
requireRole('admin');

// 1. FETCH SHIPMENTS
$query = "SELECT * FROM shipments ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// 2. FETCH SLA RULES
$rulesArr = [];
$rQ = mysqli_query($conn, "SELECT * FROM sla_policies WHERE contract_id = 0"); 
while($r = mysqli_fetch_assoc($rQ)) {
    $rulesArr[$r['origin_group']][$r['destination_group']] = $r['max_days'];
}

// 3. KPI VARIABLES
$total_shipments = 0; $total_delayed = 0; $total_ontime = 0; $total_penalty = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SLA Control Tower | Admin</title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" rel="stylesheet">

  <style>
    /* --- SHARED STYLES FROM admin.php --- */
    :root {
      --primary: #4e73df; --secondary: #f8f9fc; --dark-bg: #1e1e2f; --dark-card: #2b2b40;
      --light-text: #f8f9fa; --radius: 0.8rem; --shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    body { font-family: 'Inter', system-ui, sans-serif; background-color: var(--secondary); transition: 0.3s; color: #5a5c69; }
    body.dark-mode { background-color: var(--dark-bg); color: var(--light-text); }
    
    /* Sidebar */
    .sidebar { width: 250px; height: 100vh; background: #2c3e50; color: white; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; transition: 0.3s; z-index: 1000; }
    .sidebar.collapsed { transform: translateX(-100%); }
    .content { margin-left: 250px; padding: 2rem; transition: 0.3s; }
    .content.expanded { margin-left: 0; }
    .sidebar a { display: flex; align-items: center; gap: 10px; padding: 12px 20px; text-decoration: none; color: rgba(255,255,255,0.8); border-left: 4px solid transparent; transition: 0.2s; }
    .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); border-left-color: #fff; color: white; }
    
    /* Header & Cards */
    .header { background: white; border-radius: var(--radius); box-shadow: var(--shadow); padding: 1rem 1.5rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; }
    .card { border: none; border-radius: var(--radius); background: white; box-shadow: var(--shadow); transition: 0.3s; }
    
    /* Dark Mode Overrides */
    body.dark-mode .header, body.dark-mode .card { background: var(--dark-card); color: var(--light-text); border-color: #444; }
    body.dark-mode .table { color: var(--light-text); }
    body.dark-mode .table thead th { background: #3a3b45; color: white; border: none; }
    body.dark-mode .table td { border-color: #444; }
    body.dark-mode .bg-light { background-color: #3a3b45 !important; }

    /* Toggle Switch */
    .theme-switch { position: relative; display: inline-block; width: 40px; height: 20px; }
    .theme-switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; border-radius: 34px; transition: .4s; }
    .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 2px; bottom: 2px; background-color: white; border-radius: 50%; transition: .4s; }
    input:checked+.slider { background-color: var(--primary); }
    input:checked+.slider:before { transform: translateX(20px); }

    /* --- SLA SPECIFIC STYLES --- */
    .font-mono { font-family: 'Roboto Mono', monospace; letter-spacing: -0.5px; }
    .text-xxs { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; }
    .table td { vertical-align: middle; padding: 1rem 0.75rem; }
    
    /* KPI Cards */
    .kpi-card-body { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; }
    .kpi-icon { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; opacity: 0.8; }

    /* Soft Badges */
    .badge-soft { padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 0.75rem; display: inline-block; }
    .bg-soft-success { background-color: rgba(28, 200, 138, 0.15); color: #1cc88a; }
    .bg-soft-warning { background-color: rgba(246, 194, 62, 0.15); color: #f6c23e; }
    .bg-soft-danger { background-color: rgba(231, 74, 59, 0.15); color: #e74a3b; }
    .bg-soft-primary { background-color: rgba(78, 115, 223, 0.15); color: #4e73df; }
    .bg-soft-info { background-color: rgba(54, 185, 204, 0.15); color: #36b9cc; }
    .bg-soft-secondary { background-color: rgba(133, 135, 150, 0.15); color: #858796; }

    /* Route Visualization */
    .route-visual { position: relative; padding-left: 15px; border-left: 2px solid #e3e6f0; }
    .route-visual::before { content: ''; position: absolute; left: -5px; top: 0; width: 8px; height: 8px; border-radius: 50%; background: #1cc88a; }
    .route-visual::after { content: ''; position: absolute; left: -5px; bottom: 0; width: 8px; height: 8px; border-radius: 50%; background: #e74a3b; }
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

        <a href="#csmSubmenu" data-bs-toggle="collapse" class="d-flex justify-content-between active" aria-expanded="true">
            <span><i class="bi bi-file-text"></i> Contract & SLA</span><i class="bi bi-chevron-down small"></i>
        </a>
        <div class="collapse show" id="csmSubmenu" style="background: rgba(0,0,0,0.2);">
            <a href="admin_contracts.php" class="ps-4"><i class="bi bi-dot"></i> Manage Contracts</a>
            <a href="Admin_shipments.php" class="ps-4 active"><i class="bi bi-dot"></i> SLA Monitoring</a>
        </div>

        <a href="E-Doc.php"><i class="bi bi-folder2-open"></i> E-Docs</a>
        <a href="admin_completed.php"><i class="bi bi-check-circle-fill"></i> Completed Trans.</a>
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
        <i class="bi bi-list fs-4 text-secondary" id="hamburger" style="cursor: pointer;"></i>
        <div>
            <h5 class="fw-bold mb-0">SLA Control Tower</h5>
            <small class="text-muted">Real-time shipment tracking & SLA performance</small>
        </div>
      </div>
      <div class="d-flex align-items-center gap-3">
        <div class="dropdown">
            <a href="#" class="text-secondary position-relative" data-bs-toggle="dropdown" onclick="markRead()">
                <i class="bi bi-bell-fill fs-5"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge" style="display:none;">0</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow p-0 border-0" style="width: 320px;">
                <li class="p-3 border-bottom fw-bold bg-light rounded-top d-flex justify-content-between">
                    <span>Notifications</span>
                    <a href="feedback.php" class="text-decoration-none small">View All</a>
                </li>
                <div id="notifList" style="max-height: 300px; overflow-y: auto;">
                    <li class="text-center p-3 text-muted small">Checking...</li>
                </div>
            </ul>
        </div>
        
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-moon-fill small text-secondary"></i>
            <label class="theme-switch">
                <input type="checkbox" id="adminThemeToggle"><span class="slider"></span>
            </label>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-start border-4 border-primary">
                <div class="kpi-card-body">
                    <div>
                        <div class="text-xxs fw-bold text-primary mb-1">TOTAL SHIPMENTS</div>
                        <div class="h3 mb-0 fw-bold text-gray-800" id="kpi-total">0</div>
                    </div>
                    <div class="kpi-icon bg-soft-primary"><i class="bi bi-box-seam text-primary"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-start border-4 border-success">
                <div class="kpi-card-body">
                    <div>
                        <div class="text-xxs fw-bold text-success mb-1">ON TIME DELIVERY</div>
                        <div class="h3 mb-0 fw-bold text-gray-800" id="kpi-ontime">0</div>
                    </div>
                    <div class="kpi-icon bg-soft-success"><i class="bi bi-check-lg text-success"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-start border-4 border-danger">
                <div class="kpi-card-body">
                    <div>
                        <div class="text-xxs fw-bold text-danger mb-1">SLA BREACHED</div>
                        <div class="h3 mb-0 fw-bold text-gray-800" id="kpi-delayed">0</div>
                    </div>
                    <div class="kpi-icon bg-soft-danger"><i class="bi bi-exclamation-triangle text-danger"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-start border-4 border-warning">
                <div class="kpi-card-body">
                    <div>
                        <div class="text-xxs fw-bold text-warning mb-1">TOTAL PENALTIES</div>
                        <div class="h3 mb-0 fw-bold text-gray-800" id="kpi-penalty">₱0</div>
                    </div>
                    <div class="kpi-icon bg-soft-warning"><i class="bi bi-cash-stack text-warning"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-transparent border-bottom d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary"><i class="bi bi-table me-2"></i>Live Shipment Status</h6>
            <button class="btn btn-sm btn-light border" onclick="location.reload()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
        </div>
        <div class="table-responsive p-0">
            <table class="table table-hover align-middle mb-0 w-100">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 text-xxs text-secondary opacity-7">Tracking ID / Sender</th>
                        <th class="text-xxs text-secondary opacity-7">Route (Origin → Dest)</th>
                        <th class="text-xxs text-secondary opacity-7">Timeline</th>
                        <th class="text-xxs text-secondary opacity-7">Status</th>
                        <th class="text-xxs text-secondary opacity-7">SLA Health</th>
                        <th class="text-end pe-4 text-xxs text-secondary opacity-7">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): 
                            $total_shipments++;
                            $current_status = strtoupper($row['status']); 
                            
                            // Calculate SLA
                            $origin = $row['origin_island'] ?? 'Luzon';
                            $dest = $row['destination_island'] ?? 'Visayas';
                            $sla_days = $rulesArr[$origin][$dest] ?? 7; 
                            $book_date = strtotime($row['created_at']);
                            $target_date = strtotime("+$sla_days days", $book_date);
                            $is_delivered = ($current_status == 'DELIVERED');
                            $actual_end = $is_delivered ? strtotime($row['updated_at'] ?? $row['created_at']) : time();
                            
                            $sla_status = "";
                            $penalty = 0;

                            if ($actual_end > $target_date) {
                                $sla_status = '<span class="badge-soft bg-soft-danger">BREACHED</span>';
                                $total_delayed++;
                                $penalty = ($row['price'] ?? 0) * 0.10; 
                                $total_penalty += $penalty;
                            } elseif (!$is_delivered && (ceil(($target_date - $actual_end)/86400) <= 1)) {
                                $sla_status = '<span class="badge-soft bg-soft-warning">AT RISK</span>';
                                $total_ontime++; 
                            } else {
                                $sla_status = '<span class="badge-soft bg-soft-success">ON TRACK</span>';
                                $total_ontime++;
                            }
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-light d-flex justify-content-center align-items-center me-3 text-secondary" style="width: 35px; height: 35px;">
                                        <i class="bi bi-box"></i>
                                    </div>
                                    <div>
                                        <span class="d-block fw-bold text-primary font-mono"><?php echo $row['contract_number']; ?></span>
                                        <small class="text-muted"><?php echo $row['sender_name']; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="route-visual">
                                    <div class="text-xs fw-bold text-dark"><?php echo $row['origin_address']; ?></div>
                                    <div class="text-xs text-muted mt-1"><?php echo $row['destination_address']; ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <small class="text-muted" style="font-size: 11px;">Booked: <?php echo date('M d', $book_date); ?></small>
                                    <small class="fw-bold text-dark" style="font-size: 11px;">Target: <?php echo date('M d', $target_date); ?></small>
                                    <small class="text-muted fst-italic" style="font-size: 10px;"><?php echo $sla_days; ?> Days SLA</small>
                                </div>
                            </td>
                            <td>
                                <?php 
                                    $badgeClass = 'bg-soft-secondary';
                                    if ($current_status == 'DELIVERED') $badgeClass = 'bg-soft-success';
                                    elseif ($current_status == 'CONSOLIDATED') $badgeClass = 'bg-soft-info';
                                    elseif ($current_status == 'IN_TRANSIT' || $current_status == 'DISPATCH') $badgeClass = 'bg-soft-warning';
                                    elseif ($current_status == 'CANCELLED') $badgeClass = 'bg-soft-danger';
                                    
                                    echo "<span class='badge-soft $badgeClass'>$current_status</span>";
                                ?>
                            </td>
                            <td><?php echo $sla_status; ?></td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <button onclick="syncStatus('<?php echo $row['contract_number']; ?>', this)" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="Sync Status from Core 1">
                                        <i class="bi bi-arrow-repeat me-1"></i> Sync
                                    </button>
                                    
                                    <?php if($penalty > 0): ?>
                                        <button class="btn btn-sm btn-outline-danger ms-1 rounded-pill" 
                                                title="Issue Penalty" 
                                                onclick="issuePenalty(<?php echo $penalty; ?>, '<?php echo $row['contract_number']; ?>')">
                                            <i class="bi bi-cash"></i> -₱<?php echo number_format($penalty, 0); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">No shipments found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    // 1. UI Initialization
    if (typeof initDarkMode === 'function') initDarkMode("adminThemeToggle", "adminDarkMode");
    
    document.getElementById('hamburger').addEventListener('click', () => {
      document.getElementById('sidebar').classList.toggle('collapsed');
      document.getElementById('mainContent').classList.toggle('expanded');
    });

    // 2. Update KPI UI with Values Calculated in PHP
    document.getElementById('kpi-total').innerText = "<?php echo $total_shipments; ?>";
    document.getElementById('kpi-ontime').innerText = "<?php echo $total_ontime; ?>";
    document.getElementById('kpi-delayed').innerText = "<?php echo $total_delayed; ?>";
    document.getElementById('kpi-penalty').innerText = "₱<?php echo number_format($total_penalty, 2); ?>";

    // 3. Sync Status Logic (UPDATED WITH BETTER ALERTS)
    function syncStatus(trackingCode, btn) {
        // Show Loading Modal
        Swal.fire({
            title: 'Syncing Status',
            text: 'Retrieving latest tracking data...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData();
        formData.append('action', 'sync_core1');
        formData.append('tracking_code', trackingCode);

        fetch('update_shipment_api.php', { 
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                // Success Alert with Timer
                Swal.fire({
                    icon: 'success',
                    title: 'Synced!',
                    text: 'Shipment status updated successfully.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload(); 
                });
            } else {
                // Error Alert
                Swal.fire({
                    icon: 'error',
                    title: 'Sync Failed',
                    text: data.message
                });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Connection Error',
                text: 'Could not connect to the server. Check console.'
            });
        });
    }

    // 4. Issue Penalty Logic (NEW ADDITION)
    function issuePenalty(amount, ref) {
        Swal.fire({
            title: 'Issue Penalty?',
            text: `You are about to issue a ₱${amount} penalty for shipment ${ref} due to SLA breach.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#858796',
            confirmButtonText: 'Yes, Issue Penalty'
        }).then((result) => {
            if (result.isConfirmed) {
                // Simulate penalty issuance (Connect to backend here if needed)
                Swal.fire(
                    'Issued!',
                    'Penalty has been recorded in the accounting ledger.',
                    'success'
                );
            }
        });
    }

    // 5. Notification System
    function fetchNotifications() {
        fetch('api/get_notifications.php') // Adjust path if needed
        .then(res => res.json())
        .then(data => {
            const badge = document.getElementById('notifBadge');
            const list = document.getElementById('notifList');
            
            if (data.count > 0) {
                badge.innerText = data.count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
            
            let html = '';
            if (data.data.length > 0) {
                data.data.forEach(n => {
                    let bg = n.is_read == 0 ? 'bg-light' : '';
                    html += `<li class="border-bottom ${bg}"><a class="dropdown-item p-3 text-wrap" href="#">
                        <small class="fw-bold d-block text-dark">${n.title}</small>
                        <small class="text-muted">${n.message}</small>
                        <br><small class="text-secondary opacity-50" style="font-size:10px">${new Date(n.created_at).toLocaleString()}</small>
                    </a></li>`;
                });
            } else {
                html = '<li class="text-center p-3 text-muted small">No new notifications</li>';
            }
            list.innerHTML = html;
        }).catch(e => console.error(e));
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