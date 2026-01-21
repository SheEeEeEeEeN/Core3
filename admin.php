<?php
// C:\xampp\htdocs\last\admin.php
include("connection.php");
include("darkmode.php");
include('session.php');
requireRole('admin');

// HELPER FUNCTION: Time Elapsed (e.g., "2 hours ago")
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'yr', 'm' => 'mon', 'w' => 'wk',
        'd' => 'day', 'h' => 'hr', 'i' => 'min', 's' => 'sec',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

// =========================================================
// 1. DATA FETCHING (DATABASE CONNECTIONS)
// =========================================================

// CARD 1: Total Revenue (Paid Shipments)
$sql = "SELECT SUM(price) as total FROM shipments WHERE payment_status = 'Paid'";
$revData = $conn->query($sql)->fetch_assoc();
$totalRevenue = $revData['total'] ? $revData['total'] : 0;

// CARD 2: Active Shipments
$sql = "SELECT COUNT(*) as total FROM shipments WHERE status IN ('Pending', 'In Transit', 'Out for Delivery', 'Processing')";
$activeShipments = $conn->query($sql)->fetch_assoc()['total'];

// CARD 3: Pending Request
$sql = "SELECT COUNT(*) as total FROM shipments WHERE status = 'Pending'";
$pendingCount = $conn->query($sql)->fetch_assoc()['total'];

// CARD 4: SLA Performance
$sqlMet = "SELECT COUNT(*) as c FROM shipments WHERE sla_status='Met'";
$sqlBreach = "SELECT COUNT(*) as c FROM shipments WHERE sla_status='Breached'";
$met = $conn->query($sqlMet)->fetch_assoc()['c'];
$breached = $conn->query($sqlBreach)->fetch_assoc()['c'];
$totalSLA = $met + $breached;
$slaRate = ($totalSLA > 0) ? round(($met / $totalSLA) * 100, 1) : 100;

// CHART 1: Revenue & Volume Trend (Last 7 Days)
$chartLabels = [];
$chartRevenue = [];
$chartVolume = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chartLabels[] = date('M d', strtotime($date));
    
    // Daily Revenue
    $qRev = "SELECT SUM(price) as t FROM shipments WHERE DATE(created_at) = '$date' AND payment_status = 'Paid'";
    $rRev = $conn->query($qRev)->fetch_assoc();
    $chartRevenue[] = $rRev['t'] ? $rRev['t'] : 0;

    // Daily Volume
    $qVol = "SELECT COUNT(*) as c FROM shipments WHERE DATE(created_at) = '$date'";
    $rVol = $conn->query($qVol)->fetch_assoc();
    $chartVolume[] = $rVol['c'] ? $rVol['c'] : 0;
}

// CHART 2: Shipment Status Distribution
$statusCounts = [0, 0, 0, 0]; // Pending, In Transit, Delivered, Cancelled
$qStat = "SELECT status, COUNT(*) as c FROM shipments GROUP BY status";
$rStat = $conn->query($qStat);
while($row = $rStat->fetch_assoc()){
    if($row['status'] == 'Pending') $statusCounts[0] = $row['c'];
    if($row['status'] == 'In Transit' || $row['status'] == 'Out for Delivery') $statusCounts[1] += $row['c'];
    if($row['status'] == 'Delivered') $statusCounts[2] = $row['c'];
    if($row['status'] == 'Cancelled') $statusCounts[3] = $row['c'];
}

// TABLE: Recent Shipments (Limit 5)
$recShip = $conn->query("SELECT id, user_id, destination_address, status, price, created_at 
                          FROM shipments ORDER BY created_at DESC LIMIT 5");

// FEEDBACK: Latest Reviews
$feedbacks = $conn->query("SELECT s.rating, s.feedback_text, s.created_at, a.username 
                           FROM shipments s 
                           JOIN accounts a ON s.user_id = a.id 
                           WHERE s.rating > 0 
                           ORDER BY s.created_at DESC LIMIT 4");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | Logistics Core</title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
  
  <style>
    :root {
      --primary: #4e73df; --secondary: #f8f9fc; --dark-bg: #1e1e2f; --dark-card: #2b2b40;
      --light-text: #f8f9fa; --radius: 0.8rem; --shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    body { font-family: 'Segoe UI', system-ui, sans-serif; background-color: var(--secondary); transition: 0.3s; }
    body.dark-mode { background-color: var(--dark-bg); color: var(--light-text); }
    
    /* Sidebar & Layout */
    .sidebar { width: 250px; height: 100vh; background: #2c3e50; color: white; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; transition: 0.3s; z-index: 1000; }
    .sidebar.collapsed { transform: translateX(-100%); }
    .content { margin-left: 250px; padding: 2rem; transition: 0.3s; }
    .content.expanded { margin-left: 0; }
    
    .sidebar a { display: flex; align-items: center; gap: 10px; padding: 12px 20px; text-decoration: none; color: rgba(255,255,255,0.8); border-left: 4px solid transparent; transition: 0.2s; }
    .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); border-left-color: #fff; color: white; }
    
    /* Cards & Panels */
    .header { background: white; border-radius: var(--radius); box-shadow: var(--shadow); padding: 1rem 1.5rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; }
    .card { border: none; border-radius: var(--radius); background: white; box-shadow: var(--shadow); transition: 0.3s; }
    .card:hover { transform: translateY(-3px); }
    
    /* Table Styling Overrides */
    .table-responsive { overflow-x: auto; }
    .text-xxs { font-size: 0.75rem !important; }
    .opacity-7 { opacity: 0.7; }
    .font-weight-bolder { font-weight: 700 !important; }
    
    /* Dark Mode Overrides */
    body.dark-mode .header, body.dark-mode .card, body.dark-mode .list-group-item { background: var(--dark-card); color: var(--light-text); border-color: #444; }
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
        <a href="admin.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
        
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
        <i class="bi bi-list fs-4" id="hamburger" style="cursor: pointer;"></i>
        <h5 class="fw-bold mb-0">Executive Dashboard</h5>
      </div>
      <div class="d-flex align-items-center gap-3">
        <div class="dropdown">
            <a href="#" class="text-dark position-relative" data-bs-toggle="dropdown" onclick="markRead()">
                <i class="bi bi-bell-fill fs-5"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge" style="display:none;">0</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow p-0 border-0" style="width: 300px;">
                <li class="p-2 border-bottom fw-bold bg-light rounded-top">Notifications</li>
                <div id="notifList" style="max-height: 300px; overflow-y: auto;">
                    <li class="text-center p-3 text-muted small">Checking...</li>
                </div>
            </ul>
        </div>
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-moon-fill small"></i>
            <label class="theme-switch">
              <input type="checkbox" id="adminThemeToggle"><span class="slider"></span>
            </label>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-xl-3 col-md-6">
        <div class="card h-100 py-2 border-start border-4 border-primary">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Earnings (Paid)</div>
                <div class="h5 mb-0 fw-bold text-gray-800">₱<?php echo number_format($totalRevenue); ?></div>
              </div>
              <div class="col-auto"><i class="bi bi-currency-dollar fs-2 text-primary opacity-50"></i></div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card h-100 py-2 border-start border-4 border-success">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs fw-bold text-success text-uppercase mb-1">Active Shipments</div>
                <div class="h5 mb-0 fw-bold text-gray-800"><?php echo $activeShipments; ?></div>
              </div>
              <div class="col-auto"><i class="bi bi-truck fs-2 text-success opacity-50"></i></div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card h-100 py-2 border-start border-4 border-warning">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs fw-bold text-warning text-uppercase mb-1">Pending Requests</div>
                <div class="h5 mb-0 fw-bold text-gray-800"><?php echo $pendingCount; ?></div>
              </div>
              <div class="col-auto"><i class="bi bi-clipboard-data fs-2 text-warning opacity-50"></i></div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card h-100 py-2 border-start border-4 border-info">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs fw-bold text-info text-uppercase mb-1">SLA Compliance</div>
                <div class="row no-gutters align-items-center">
                  <div class="col-auto">
                    <div class="h5 mb-0 mr-3 fw-bold text-gray-800"><?php echo $slaRate; ?>%</div>
                  </div>
                  <div class="col ps-2">
                    <div class="progress progress-sm mr-2" style="height: 5px;">
                      <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $slaRate; ?>%"></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-auto"><i class="bi bi-speedometer2 fs-2 text-info opacity-50"></i></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between bg-transparent border-bottom align-items-center">
                    <h6 class="m-0 fw-bold text-primary">Revenue Overview (7 Days)</h6>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 bg-transparent border-bottom">
                    <h6 class="m-0 fw-bold text-primary">Shipment Status</h6>
                </div>
                <div class="card-body">
                      <div style="position: relative; height: 250px; width: 100%;">
                        <canvas id="statusChart"></canvas>
                    </div>
                    <div class="mt-3 text-center small">
                        <span class="me-2"><i class="bi bi-circle-fill text-success"></i> Delivered</span>
                        <span class="me-2"><i class="bi bi-circle-fill text-warning"></i> Pending</span>
                        <span><i class="bi bi-circle-fill text-info"></i> Transit</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card shadow h-100">
                <div class="card-header py-3 bg-transparent border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-primary">Recent Transactions</h6>
                    <a href="shiphistory.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4">Tracking ID</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Destination</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Amount</th>
                                <th class="text-secondary opacity-7"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($recShip->num_rows > 0): while($row = $recShip->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light d-flex justify-content-center align-items-center me-3 text-primary" style="width: 35px; height: 35px;">
                                            <i class="bi bi-box-seam"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark d-block text-sm">
                                                <?php echo 'SHIP-'.str_pad($row['id'], 5, "0", STR_PAD_LEFT); ?>
                                            </span>
                                            <small class="text-muted" style="font-size: 11px;">
                                                <?php echo $row['user_id']; ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center text-secondary">
                                        <i class="bi bi-geo-alt me-1 small"></i>
                                        <span class="text-sm fw-bold"><?php echo substr($row['destination_address'], 0, 18) . '...'; ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                        $st = $row['status'];
                                        // Soft Badge Logic (BG Opacity)
                                        $badgeClass = 'bg-secondary text-secondary bg-opacity-10'; 
                                        if($st=='Delivered') $badgeClass = 'bg-success text-success bg-opacity-10';
                                        else if($st=='Pending') $badgeClass = 'bg-warning text-warning bg-opacity-10';
                                        else if($st=='In Transit' || $st=='Out for Delivery') $badgeClass = 'bg-info text-info bg-opacity-10';
                                        else if($st=='Cancelled') $badgeClass = 'bg-danger text-danger bg-opacity-10';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?> px-3 py-2 rounded-pill border border-0 fw-bold">
                                        <?php echo $st; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark">₱<?php echo number_format($row['price']); ?></span>
                                        <small class="text-muted" style="font-size: 10px;">
                                            <?php echo date('M d', strtotime($row['created_at'])); ?>
                                        </small>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <small class="text-muted d-block mb-1" style="font-size: 10px;">
                                        <?php echo time_elapsed_string($row['created_at']); ?>
                                    </small>
                                    <a href="view_shipment.php?id=<?php echo $row['id']; ?>" class="text-secondary text-hover-primary">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">No recent transactions.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 bg-transparent border-bottom">
                    <h6 class="m-0 fw-bold text-primary">Customer Voices</h6>
                </div>
                <div class="list-group list-group-flush">
                    <?php if($feedbacks->num_rows > 0): while($f = $feedbacks->fetch_assoc()): ?>
                    <div class="list-group-item border-bottom-0 pb-3 pt-3">
                        <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                            <h6 class="mb-0 fw-bold text-dark small"><?php echo htmlspecialchars($f['username']); ?></h6>
                            <small class="text-muted" style="font-size: 10px;"><?php echo time_elapsed_string($f['created_at']); ?></small>
                        </div>
                        <div class="text-warning small mb-2" style="font-size: 10px;">
                            <?php for($i=0; $i<$f['rating']; $i++) echo '<i class="bi bi-star-fill"></i> '; ?>
                        </div>
                        <p class="mb-0 small text-secondary bg-light p-2 rounded fst-italic">"<?php echo $f['feedback_text'] ?: 'No comment'; ?>"</p>
                    </div>
                    <?php endwhile; else: ?>
                        <div class="text-center p-4 text-muted small">No feedback received yet.</div>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-center bg-transparent border-top-0 pb-3">
                    <a href="customer_feedback.php" class="small text-decoration-none fw-bold">View All Reviews</a>
                </div>
            </div>
        </div>
    </div>

  </div> 
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // 1. UI INIT
    initDarkMode("adminThemeToggle", "adminDarkMode");
    document.getElementById('hamburger').addEventListener('click', () => {
      document.getElementById('sidebar').classList.toggle('collapsed');
      document.getElementById('mainContent').classList.toggle('expanded');
    });

    // 2. REVENUE CHART
    const ctxRev = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctxRev, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($chartLabels); ?>,
            datasets: [
                {
                    label: 'Revenue (₱)',
                    data: <?php echo json_encode($chartRevenue); ?>,
                    type: 'line',
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    pointRadius: 4,
                    pointBackgroundColor: '#4e73df',
                    yAxisID: 'y1'
                },
                {
                    label: 'Volume',
                    data: <?php echo json_encode($chartVolume); ?>,
                    backgroundColor: '#1cc88a',
                    yAxisID: 'y2',
                    barThickness: 20
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            tooltips: { mode: 'index', intersect: false },
            scales: {
                yAxes: [
                    { id: 'y1', position: 'left', ticks: { beginAtZero: true, callback: v => '₱' + v } },
                    { id: 'y2', position: 'right', gridLines: { display: false }, ticks: { beginAtZero: true } }
                ],
                xAxes: [{ gridLines: { display: false } }]
            }
        }
    });

    // 3. STATUS CHART
    const ctxStat = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxStat, {
        type: 'doughnut',
        data: {
            labels: ["Pending", "Transit/Out", "Delivered", "Cancelled"],
            datasets: [{
                data: <?php echo json_encode($statusCounts); ?>,
                backgroundColor: ['#f6c23e', '#36b9cc', '#1cc88a', '#e74a3b'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: { display: false },
            cutoutPercentage: 75,
        }
    });

    // 4. NOTIFICATIONS
    function fetchNotifications() {
        fetch('api/get_notifications.php')
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
                    html += `<li class="border-bottom ${bg}"><a class="dropdown-item p-2 text-wrap" href="#">
                        <small class="fw-bold d-block">${n.title}</small>
                        <small class="text-muted">${n.message}</small>
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