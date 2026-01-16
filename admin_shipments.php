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
$total_shipments = 0;
$total_delayed = 0;
$total_ontime = 0;
$total_penalty = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SLA Monitoring | Control Tower</title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" rel="stylesheet">

  <style>
    /* --- ADMIN TEMPLATE STYLES (From admin.php) --- */
    :root {
      --primary: #4e73df; --secondary: #f8f9fc; --dark-bg: #1e1e2f; --dark-card: #2b2b40;
      --light-text: #f8f9fa; --radius: 1rem; --shadow: 0 0.35rem 1.2rem rgba(0, 0, 0, 0.15);
      
      /* SLA SPECIFIC COLORS */
      --status-ok: #10b981;
      --status-warn: #f59e0b;
      --status-bad: #ef4444;
    }
    
    body { font-family: 'Segoe UI', system-ui, sans-serif; background-color: var(--secondary); transition: all 0.3s ease; }
    body.dark-mode { background-color: var(--dark-bg); color: var(--light-text); }
    
    /* SIDEBAR STYLES */
    .sidebar { width: 250px; height: 100vh; background: #2c3e50; color: white; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; transition: 0.3s; z-index: 1000; }
    .sidebar.collapsed { transform: translateX(-100%); }
    .content { margin-left: 250px; padding: 2rem; transition: 0.3s; }
    .content.expanded { margin-left: 0; }
    
    .sidebar a { display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.2rem; text-decoration: none; color: rgba(255,255,255,0.9); }
    .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.2); border-left: 4px solid #fff; color: white; }
    
    /* HEADER & CARDS */
    .header { background: white; border-radius: var(--radius); box-shadow: var(--shadow); padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
    .card { border: none; border-radius: var(--radius); padding: 1.5rem; background: white; box-shadow: var(--shadow); margin-bottom: 1.5rem; }
    
    /* DARK MODE OVERRIDES */
    body.dark-mode .header, body.dark-mode .card, body.dark-mode .kpi-card { background: var(--dark-card); color: var(--light-text); border-color: #444; }
    body.dark-mode .table { color: var(--light-text); }
    body.dark-mode thead th { background-color: #1e1e2f; color: #ccc; }
    
    /* TOGGLE SWITCH */
    .theme-switch { width: 50px; height: 25px; position: relative; display: inline-block; }
    .theme-switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; inset: 0; background-color: #ccc; border-radius: 34px; transition: .4s; }
    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 3px; background-color: white; border-radius: 50%; transition: .4s; }
    input:checked+.slider { background-color: var(--primary); }
    input:checked+.slider:before { transform: translateX(24px); }

    /* --- SLA MONITORING SPECIFIC STYLES --- */
    .mono { font-family: 'Roboto Mono', monospace; font-size: 0.9em; }
    .table-responsive { max-height: 600px; overflow-y: auto; }
    thead th { position: sticky; top: 0; background: #f8f9fc; z-index: 1; }

    /* KPI Cards Custom */
    .kpi-card { background: white; border-radius: var(--radius); padding: 1.5rem; box-shadow: var(--shadow); height: 100%; display: flex; align-items: center; justify-content: space-between; border-left: 5px solid transparent; }
    .kpi-icon { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }

    /* Status Badges */
    .badge-sla { padding: 5px 10px; border-radius: 4px; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; display: inline-flex; align-items: center; gap: 5px; }
    .sla-ok { background: #d1fae5; color: #065f46; } 
    .sla-warn { background: #fef3c7; color: #92400e; }
    .sla-bad { background: #fee2e2; color: #991b1b; }
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
        <a href="BIFA.php"><i class="bi bi-graph-up"></i> BI & Analytics</a>
        <a href="admin_reports.php"><i class="bi bi-file-earmark-bar-graph"></i> Reports Generation</a>
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
        <h4 class="fw-bold mb-0">SLA Control Tower</h4>
      </div>
      <div class="d-flex align-items-center gap-2">
        <small>Dark Mode</small>
        <label class="theme-switch">
          <input type="checkbox" id="adminThemeToggle"><span class="slider"></span>
        </label>
      </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="kpi-card" style="border-left-color: #4e73df;">
                <div>
                    <small class="text-muted fw-bold">TOTAL SHIPMENTS</small>
                    <h2 class="fw-bold m-0" id="kpi-total">0</h2>
                </div>
                <div class="kpi-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-box-seam"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card" style="border-left-color: #1cc88a;">
                <div>
                    <small class="text-muted fw-bold">ON TIME</small>
                    <h2 class="fw-bold m-0 text-success" id="kpi-ontime">0</h2>
                </div>
                <div class="kpi-icon bg-success bg-opacity-10 text-success"><i class="bi bi-check-lg"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card" style="border-left-color: #e74a3b;">
                <div>
                    <small class="text-muted fw-bold">DELAYED (BREACH)</small>
                    <h2 class="fw-bold m-0 text-danger" id="kpi-delayed">0</h2>
                </div>
                <div class="kpi-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-exclamation-triangle"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card" style="border-left-color: #f6c23e;">
                <div>
                    <small class="text-muted fw-bold">PENALTIES</small>
                    <h2 class="fw-bold m-0 text-warning" id="kpi-penalty">₱0</h2>
                </div>
                <div class="kpi-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-cash-coin"></i></div>
            </div>
        </div>
    </div>

    <div class="card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Tracking ID</th>
                        <th>Route</th>
                        <th>Booking Date</th>
                        <th>Target Delivery</th>
                        <th>Lead Time</th>
                        <th>Status</th>
                        <th>SLA Health</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): 
                            $total_shipments++;
                            
                            // CALCULATE SLA
                            $origin = $row['origin_island'] ?? 'Metro Manila';
                            $dest = $row['destination_island'] ?? 'Visayas';
                            $sla_days = $rulesArr[$origin][$dest] ?? 7; 
                            
                            $book_date = strtotime($row['created_at']);
                            $target_date = strtotime("+$sla_days days", $book_date);
                            $now = time();
                            
                            $is_delivered = ($row['status'] == 'Delivered');
                            $actual_end = $is_delivered ? strtotime($row['updated_at'] ?? $row['created_at']) : $now;
                            
                            $days_diff = ceil(($target_date - $actual_end) / 60 / 60 / 24);
                            
                            $sla_status = "";
                            $penalty = 0;

                            if ($actual_end > $target_date) {
                                // DELAYED
                                $sla_status = '<span class="badge-sla sla-bad"><i class="bi bi-exclamation-circle-fill"></i> BREACHED</span>';
                                $total_delayed++;
                                $penalty = ($row['price'] ?? 0) * 0.10; 
                                $total_penalty += $penalty;
                            } elseif ($days_diff <= 1 && !$is_delivered) {
                                // AT RISK
                                $sla_status = '<span class="badge-sla sla-warn"><i class="bi bi-clock-history"></i> AT RISK</span>';
                                $total_ontime++; 
                            } else {
                                // ON TRACK
                                $sla_status = '<span class="badge-sla sla-ok"><i class="bi bi-check-circle-fill"></i> ON TRACK</span>';
                                $total_ontime++;
                            }
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-primary mono">TRK-<?php echo $row['id']; ?></div>
                                <div class="small text-muted"><?php echo $row['sender_name']; ?></div>
                            </td>
                            <td>
                                <div class="d-flex flex-column small">
                                    <span><i class="bi bi-geo-alt text-success"></i> <?php echo $origin; ?></span>
                                    <span><i class="bi bi-arrow-down-short text-muted"></i></span>
                                    <span><i class="bi bi-geo-alt-fill text-danger"></i> <?php echo $dest; ?></span>
                                </div>
                            </td>
                            <td class="mono small"><?php echo date('M d, Y', $book_date); ?></td>
                            <td class="mono small fw-bold">
                                <?php echo date('M d, Y', $target_date); ?>
                            </td>
                            <td><span class="badge bg-secondary"><?php echo $sla_days; ?> Days</span></td>
                            <td>
                                <?php 
                                    $st = $row['status'];
                                    $badge = ($st == 'Delivered') ? 'bg-success' : (($st == 'Cancelled') ? 'bg-danger' : 'bg-primary');
                                    echo "<span class='badge $badge'>$st</span>";
                                ?>
                            </td>
                            <td><?php echo $sla_status; ?></td>
                            <td class="text-end pe-4">
                                <?php if($penalty > 0): ?>
                                    <button class="btn btn-sm btn-outline-danger" title="Issue Refund">
                                        <i class="bi bi-cash"></i> -₱<?php echo number_format($penalty, 2); ?>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-light text-muted" disabled>No Penalty</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center py-5 text-muted">No shipments found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    if (typeof initDarkMode === 'function') initDarkMode("adminThemeToggle", "adminDarkMode");
    
    // Sidebar Toggle Logic
    document.getElementById('hamburger').addEventListener('click', () => {
      document.getElementById('sidebar').classList.toggle('collapsed');
      document.getElementById('mainContent').classList.toggle('expanded');
    });

    // UPDATE KPI COUNTERS FROM PHP VALUES
    document.getElementById('kpi-total').innerText = "<?php echo $total_shipments; ?>";
    document.getElementById('kpi-ontime').innerText = "<?php echo $total_ontime; ?>";
    document.getElementById('kpi-delayed').innerText = "<?php echo $total_delayed; ?>";
    document.getElementById('kpi-penalty').innerText = "₱<?php echo number_format($total_penalty, 2); ?>";
  </script>
</body>
</html>