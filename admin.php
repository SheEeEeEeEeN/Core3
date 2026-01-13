<?php
include("connection.php");
include("darkmode.php");
include('session.php');
requireRole('admin');
include('loading.html');

// --- DATABASE METRICS ---
$sql = "SELECT COUNT(*) AS total_users FROM accounts WHERE role='user'";
$users = $conn->query($sql)->fetch_assoc()['total_users'];

$sql = "SELECT COUNT(*) as total_active FROM csm WHERE status = 'Active'";
$contracts = $conn->query($sql)->fetch_assoc()['total_active'];

$sql = "SELECT COUNT(*) as total_pending FROM shipments WHERE status = 'Pending'";
$pending = $conn->query($sql)->fetch_assoc()['total_pending'];

$sql = "SELECT AVG(rating) as avg_rate, COUNT(*) as total_reviews FROM shipments WHERE rating > 0";
$rateData = $conn->query($sql)->fetch_assoc();
$avgRating = number_format($rateData['avg_rate'], 1);
$totalReviews = $rateData['total_reviews'];

$met = $conn->query("SELECT COUNT(*) as c FROM shipments WHERE sla_status='Met'")->fetch_assoc()['c'];
$breached = $conn->query("SELECT COUNT(*) as c FROM shipments WHERE sla_status='Breached'")->fetch_assoc()['c'];

// Recent Feedback List
$feedbacks = $conn->query("SELECT s.rating, s.feedback_text, s.created_at, a.username 
                           FROM shipments s 
                           JOIN accounts a ON s.user_id = a.id 
                           WHERE s.rating > 0 
                           ORDER BY s.created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
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
    
    .stat-value { font-size: 1.8rem; font-weight: 700; }
    
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
            <a href="Admin_shipments.php" class="ps-4"><i class="bi bi-dot"></i> SLA Monitoring</a>
        </div>

        <a href="E-Doc.php"><i class="bi bi-folder2-open"></i> E-Docs</a>
        <a href="BIFA.php"><i class="bi bi-graph-up"></i> BI & Analytics</a>
        <a href="admin_reports.php">
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
        <h4 class="fw-bold mb-0">Executive Dashboard</h4>
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
        <div class="card p-3 text-center border-top border-5 border-info h-100">
          <h5 class="text-secondary">Users</h5>
          <div class="stat-value text-dark"><?php echo $users; ?></div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card p-3 text-center border-top border-5 border-success h-100">
          <h5 class="text-secondary">Active Contracts</h5>
          <div class="stat-value text-dark"><?php echo $contracts; ?></div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card p-3 text-center border-top border-5 border-warning h-100">
          <h5 class="text-secondary">Pending</h5>
          <div class="stat-value text-dark"><?php echo $pending; ?></div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card p-3 text-center border-top border-5 border-danger h-100">
          <h5 class="text-secondary">Avg Rating</h5>
          <div class="stat-value text-danger">
            <?php echo $avgRating; ?> <small class="fs-6 text-muted">/ 5</small>
          </div>
          <small class="text-muted"><?php echo $totalReviews; ?> Reviews</small>
        </div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-lg-7">
        <div class="card h-100">
          <div class="d-flex justify-content-between mb-3">
            <h5 class="fw-bold"><i class="bi bi-chat-quote text-primary"></i> Latest Feedback</h5>
            <a href="customer_feedback.php" class="btn btn-sm btn-outline-primary">View All Reviews</a>
          </div>
          <div class="list-group list-group-flush">
            <?php if($feedbacks->num_rows > 0): while($f = $feedbacks->fetch_assoc()): ?>
            <div class="list-group-item px-0">
              <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($f['username']); ?></h6>
                <small class="text-muted"><?php echo date('M d', strtotime($f['created_at'])); ?></small>
              </div>
              <div class="text-warning small mb-1">
                <?php for($i=0; $i<$f['rating']; $i++) echo '★'; ?>
              </div>
              <small class="text-muted fst-italic">"<?php echo $f['feedback_text'] ?: 'No comment'; ?>"</small>
            </div>
            <?php endwhile; else: ?>
              <p class="text-center text-muted py-4">No feedback yet.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card h-100">
          <h5 class="fw-bold mb-3"><i class="bi bi-pie-chart text-success"></i> SLA Performance</h5>
          <canvas id="slaChart" style="width:100%; max-height:250px;"></canvas>
          <div class="mt-3 text-center small text-muted">
            On-Time Deliveries vs Delayed
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

    new Chart("slaChart", {
      type: "doughnut",
      data: {
        labels: ["On Time (Met)", "Delayed (Breached)"],
        datasets: [{
          backgroundColor: ["#1cc88a", "#e74a3b"],
          data: [<?php echo $met; ?>, <?php echo $breached; ?>]
        }]
      },
      options: { legend: { position: 'bottom' }, cutoutPercentage: 70 }
    });
  </script>
</body>
</html>