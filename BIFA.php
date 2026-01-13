<?php
include("connection.php");
include("darkmode.php");
include('session.php');
requireRole('admin');
include('loading.html');

// ==========================================================
// 1. DATA FETCHING
// ==========================================================

// A. KPI TOTALS
$sqlTotals = "SELECT 
                COALESCE(SUM(price),0) as total_revenue, 
                COUNT(*) as total_shipments,
                COALESCE(AVG(NULLIF(rating,0)), 0) as avg_rating 
              FROM shipments 
              WHERE status != 'Cancelled'";
$totals = $conn->query($sqlTotals)->fetch_assoc();
$totalVolume = $totals['total_shipments']; // Need this for percentage calc

// B. SLA PERFORMANCE
$sqlSLA = "SELECT 
            SUM(CASE WHEN sla_status = 'Met' THEN 1 ELSE 0 END) as met,
            SUM(CASE WHEN sla_status = 'Breached' THEN 1 ELSE 0 END) as breached
           FROM shipments WHERE status = 'Delivered'";
$sla = $conn->query($sqlSLA)->fetch_assoc();
$slaMet = $sla['met'] ?? 0;
$slaBreached = $sla['breached'] ?? 0;
$totalDelivered = $slaMet + $slaBreached;
$onTimeRate = ($totalDelivered > 0) ? round(($slaMet / $totalDelivered) * 100, 1) : 0;

// C. REVENUE & VOLUME TREND
$months = []; $revData = []; $volData = [];
for ($i = 5; $i >= 0; $i--) {
    $monthStart = date('Y-m-01', strtotime("-$i months"));
    $monthEnd   = date('Y-m-t', strtotime("-$i months"));
    $months[]   = date('M', strtotime("-$i months")); 
    
    $q = "SELECT SUM(price) as rev, COUNT(*) as vol 
          FROM shipments 
          WHERE created_at BETWEEN '$monthStart 00:00:00' AND '$monthEnd 23:59:59' 
          AND status != 'Cancelled'";
    $res = $conn->query($q)->fetch_assoc();
    $revData[] = $res['rev'] ?? 0;
    $volData[] = $res['vol'] ?? 0;
}

// D. TOP DESTINATIONS (Enhanced Logic)
$locLabels = [];
$locCounts = [];
$locColors = [];
$topRegion = "None";
$topPercent = 0;

$locSql = "SELECT destination_island, COUNT(*) as c 
           FROM shipments 
           WHERE destination_island IS NOT NULL AND destination_island != ''
           GROUP BY destination_island 
           ORDER BY c DESC";
$locQ = $conn->query($locSql);

$islandStats = []; // Array for the list view

while($r = $locQ->fetch_assoc()) {
    $label = $r['destination_island'];
    $count = $r['c'];
    
    // Calculate Percentage
    $percent = ($totalVolume > 0) ? round(($count / $totalVolume) * 100, 1) : 0;
    
    // Assign Colors
    $color = '#858796'; // Grey default
    if($label == 'Luzon') $color = '#4e73df'; // Blue
    if($label == 'Visayas') $color = '#f6c23e'; // Yellow
    if($label == 'Mindanao') $color = '#e74a3b'; // Red

    // Determine Top Region
    if($count > 0 && empty($locLabels)) {
        $topRegion = $label;
        $topPercent = $percent;
    }

    $locLabels[] = $label;
    $locCounts[] = $count;
    $locColors[] = $color;

    // Save for List View
    $islandStats[] = [
        'name' => $label,
        'count' => $count,
        'percent' => $percent,
        'color' => $color
    ];
}

// E. SHIPMENT STATUS
$statPending = $conn->query("SELECT COUNT(*) FROM shipments WHERE status='Pending'")->fetch_row()[0];
$statTransit = $conn->query("SELECT COUNT(*) FROM shipments WHERE status='In Transit'")->fetch_row()[0];
$statDelivered = $conn->query("SELECT COUNT(*) FROM shipments WHERE status='Delivered'")->fetch_row()[0];
$statCancelled = $conn->query("SELECT COUNT(*) FROM shipments WHERE status='Cancelled'")->fetch_row()[0];
$statusData = [$statPending, $statTransit, $statDelivered, $statCancelled];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Freight Analytics</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>

  <style>
    :root {
      --primary: #4e73df; --secondary: #f8f9fc; --dark-bg: #1e1e2f; --dark-card: #2b2b40;
      --light-text: #f8f9fa; --radius: 1rem; --shadow: 0 0.35rem 1.2rem rgba(0, 0, 0, 0.15);
    }
    body { font-family: 'Segoe UI', system-ui, sans-serif; background-color: var(--secondary); transition: all 0.3s ease; }
    body.dark-mode { background-color: var(--dark-bg); color: var(--light-text); }
    
    .sidebar { width: 250px; height: 100vh; background: #2c3e50; color: white; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; transition: 0.3s; z-index: 1000; }
    .sidebar.collapsed { transform: translateX(-100%); }
    .content { margin-left: 250px; padding: 2rem; transition: 0.3s; }
    .content.expanded { margin-left: 0; }
    .sidebar a { display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.2rem; text-decoration: none; color: rgba(255,255,255,0.9); }
    .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.2); border-left: 4px solid #fff; color: white; }
    
    .header { background: white; border-radius: var(--radius); box-shadow: var(--shadow); padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
    .card { border: none; border-radius: var(--radius); padding: 1.5rem; background: white; box-shadow: var(--shadow); margin-bottom: 1.5rem; }
    body.dark-mode .header, body.dark-mode .card, body.dark-mode .list-group-item { background: var(--dark-card); color: var(--light-text); border-color: #444; }
    
    .stat-value { font-size: 2rem; font-weight: 800; }
    .progress-thin { height: 6px; border-radius: 3px; }
    
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
        <a href="BIFA.php" class="active"><i class="bi bi-graph-up"></i> BI & Analytics</a>
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
        <h4 class="fw-bold mb-0">BI & Freight Analytics</h4>
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
        <div class="card h-100 border-start border-5 border-success py-2">
          <h6 class="text-secondary text-uppercase fw-bold small">Total Revenue</h6>
          <div class="stat-value text-success">₱<?php echo number_format($totals['total_revenue']); ?></div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card h-100 border-start border-5 border-primary py-2">
          <h6 class="text-secondary text-uppercase fw-bold small">Total Volume</h6>
          <div class="stat-value text-primary"><?php echo number_format($totals['total_shipments']); ?></div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card h-100 border-start border-5 border-info py-2">
          <h6 class="text-secondary text-uppercase fw-bold small">On-Time (SLA)</h6>
          <div class="stat-value text-info"><?php echo $onTimeRate; ?>%</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card h-100 border-start border-5 border-warning py-2">
          <h6 class="text-secondary text-uppercase fw-bold small">Avg Rating</h6>
          <div class="stat-value text-warning"><?php echo number_format($totals['avg_rating'], 1); ?> ⭐</div>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-lg-8">
        <div class="card h-100">
          <h5 class="fw-bold mb-3"><i class="bi bi-graph-up-arrow"></i> Revenue & Volume (Last 6 Months)</h5>
          <div style="height: 300px;">
            <canvas id="trendChart"></canvas>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card h-100">
          <h5 class="fw-bold mb-3"><i class="bi bi-pie-chart-fill"></i> Shipment Status</h5>
          <div style="height: 300px;">
            <canvas id="statusChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3">
      
      <div class="col-lg-7">
        <div class="card h-100">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-geo-alt-fill text-danger"></i> Top Destinations</h5>
          </div>

          <div class="row">
            <div class="col-md-7">
               <div style="height: 250px;">
                 <canvas id="locChart"></canvas>
               </div>
            </div>
            
            <div class="col-md-5 d-flex flex-column justify-content-center">
                <?php if($topRegion != 'None'): ?>
                <div class="alert alert-light border-start border-4 border-primary shadow-sm p-2 mb-3">
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Dominant Region</small>
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="mb-0 fw-bold text-dark"><?php echo $topRegion; ?></h5>
                        <span class="badge bg-primary rounded-pill"><?php echo $topPercent; ?>% share</span>
                    </div>
                </div>
                <?php endif; ?>

                <ul class="list-group list-group-flush small">
                    <?php if(!empty($islandStats)): foreach($islandStats as $stat): ?>
                    <li class="list-group-item d-flex align-items-center justify-content-between px-0 border-0 pb-1">
                        <div class="d-flex align-items-center gap-2" style="width: 50%;">
                            <span class="rounded-circle" style="width:10px; height:10px; background-color: <?php echo $stat['color']; ?>;"></span>
                            <span class="fw-semibold"><?php echo $stat['name']; ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="text-muted"><?php echo $stat['count']; ?> ship</span>
                            <span class="fw-bold"><?php echo $stat['percent']; ?>%</span>
                        </div>
                    </li>
                    <div class="progress progress-thin mb-2 bg-light">
                        <div class="progress-bar" style="width: <?php echo $stat['percent']; ?>%; background-color: <?php echo $stat['color']; ?>;"></div>
                    </div>
                    <?php endforeach; else: ?>
                    <div class="text-center text-muted">No data available</div>
                    <?php endif; ?>
                </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card h-100">
          <h5 class="fw-bold mb-3"><i class="bi bi-clock-history"></i> On-Time Performance</h5>
          <div style="height: 200px;">
            <canvas id="slaChart"></canvas>
          </div>
          <div class="mt-4 text-center">
             <div class="d-flex justify-content-center gap-4">
                 <div class="text-center">
                     <h4 class="mb-0 fw-bold text-success"><?php echo $slaMet; ?></h4>
                     <small class="text-muted">On Time</small>
                 </div>
                 <div class="border-end"></div>
                 <div class="text-center">
                     <h4 class="mb-0 fw-bold text-secondary"><?php echo $slaBreached; ?></h4>
                     <small class="text-muted">Late</small>
                 </div>
             </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    initDarkMode("adminThemeToggle", "adminDarkMode");
    document.getElementById('hamburger').addEventListener('click', () => {
      document.getElementById('sidebar').classList.toggle('collapsed');
      document.getElementById('mainContent').classList.toggle('expanded');
    });

    // DATA INJECTION
    const months = <?php echo json_encode($months); ?>;
    const revData = <?php echo json_encode($revData); ?>;
    const volData = <?php echo json_encode($volData); ?>;
    const locLabels = <?php echo json_encode($locLabels); ?>;
    const locData = <?php echo json_encode($locCounts); ?>;
    const locColors = <?php echo json_encode($locColors); ?>;
    const statData = <?php echo json_encode($statusData); ?>;
    const slaData = [<?php echo $slaMet; ?>, <?php echo $slaBreached; ?>];

    // 1. TREND CHART
    new Chart("trendChart", {
        type: 'bar',
        data: {
            labels: months,
            datasets: [
                { label: 'Revenue (₱)', data: revData, type: 'line', borderColor: '#1cc88a', backgroundColor: 'rgba(28,200,138,0.1)', yAxisID: 'y-axis-1', order: 1 },
                { label: 'Volume', data: volData, backgroundColor: '#4e73df', yAxisID: 'y-axis-2', order: 2 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: {
                yAxes: [
                    { id: 'y-axis-1', type: 'linear', position: 'left', gridLines: {display:false}, ticks: {beginAtZero:true} },
                    { id: 'y-axis-2', type: 'linear', position: 'right', gridLines: {display:false}, ticks: {beginAtZero:true} }
                ]
            }
        }
    });

    // 2. STATUS CHART
    new Chart("statusChart", {
        type: 'doughnut',
        data: {
            labels: ["Pending", "In Transit", "Delivered", "Cancelled"],
            datasets: [{ backgroundColor: ["#f6c23e", "#36b9cc", "#1cc88a", "#e74a3b"], data: statData }]
        },
        options: { maintainAspectRatio: false, legend: { position: 'bottom' } }
    });

    // 3. TOP DESTINATIONS CHART (Visual Upgrade)
    new Chart("locChart", {
        type: 'horizontalBar',
        data: {
            labels: locLabels,
            datasets: [{
                label: 'Shipments',
                backgroundColor: locColors,
                borderColor: '#fff',
                borderWidth: 2,
                data: locData
            }]
        },
        options: {
            maintainAspectRatio: false,
            legend: { display: false },
            scales: { 
                xAxes: [{ ticks: { beginAtZero: true, stepSize: 1 }, gridLines: { borderDash: [4] } }],
                yAxes: [{ gridLines: { display: false } }]
            },
            tooltips: {
                backgroundColor: "rgba(255,255,255,0.9)",
                bodyFontColor: "#000",
                borderColor: "#ddd",
                borderWidth: 1,
                displayColors: true,
                callbacks: { label: function(item) { return " " + item.xLabel + " Shipments"; } }
            }
        }
    });

    // 4. SLA CHART
    new Chart("slaChart", {
        type: 'pie',
        data: {
            labels: ["On Time", "Late"],
            datasets: [{ backgroundColor: ["#1cc88a", "#858796"], data: slaData }]
        },
        options: { maintainAspectRatio: false, legend: { position: 'right' } }
    });
  </script>
</body>
</html>